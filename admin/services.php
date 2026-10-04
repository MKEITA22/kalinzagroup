<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$errors = [];

// Suppression
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("SELECT cover_image FROM services WHERE id = :id");
    $stmt->execute(['id' => (int) $_GET['delete']]);
    if ($row = $stmt->fetch()) {
        if ($row['cover_image'] && is_file(__DIR__ . '/../' . $row['cover_image'])) {
            @unlink(__DIR__ . '/../' . $row['cover_image']);
        }
    }
    $pdo->prepare("DELETE FROM services WHERE id = :id")->execute(['id' => (int) $_GET['delete']]);
    header('Location: services.php');
    exit;
}

// Création / édition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $errors[] = "Session expirée, merci de réessayer.";
    } else {
        $id           = (int) ($_POST['id'] ?? 0);
        $division     = $_POST['division'] ?? '';
        $category     = trim($_POST['category'] ?? '');
        $title        = trim($_POST['title'] ?? '');
        $description  = trim($_POST['description'] ?? '');
        $priceNote    = trim($_POST['price_note'] ?? '');
        $minQuantity  = trim($_POST['min_quantity'] ?? '');
        $deliveryTime = trim($_POST['delivery_time'] ?? '');
        $isVisible    = isset($_POST['is_visible']) ? 1 : 0;
        $slug         = trim(strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title)), '-');

        if (!in_array($division, ['imprimerie', 'prod', 'digital'], true)) $errors[] = "Division invalide.";
        if ($title === '') $errors[] = "Le titre est obligatoire.";

        // Image de la carte produit (facultative)
        $coverImage = trim($_POST['existing_cover_image'] ?? '') ?: null;
        if (!empty($_FILES['cover_image']['name'])) {
            $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt, true)) {
                $errors[] = "Format d'image non autorisé (jpg, png, webp).";
            } else {
                $uploadDir = __DIR__ . '/../uploads/services/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $filename = uniqid('svc_', true) . '.' . $ext;
                if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadDir . $filename)) {
                    $coverImage = 'uploads/services/' . $filename;
                }
            }
        }

        if (empty($errors)) {
            if ($id > 0) {
                $stmt = $pdo->prepare(
                    "UPDATE services SET division=:division, category=:category, title=:title, slug=:slug,
                     description=:description, price_note=:price_note, min_quantity=:min_quantity,
                     delivery_time=:delivery_time, cover_image=:cover_image, is_visible=:is_visible WHERE id=:id"
                );
                $stmt->execute([
                    'division' => $division, 'category' => $category, 'title' => $title, 'slug' => $slug,
                    'description' => $description, 'price_note' => $priceNote, 'min_quantity' => $minQuantity ?: null,
                    'delivery_time' => $deliveryTime ?: null, 'cover_image' => $coverImage, 'is_visible' => $isVisible,
                    'id' => $id,
                ]);
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO services (division, category, title, slug, description, price_note, min_quantity, delivery_time, cover_image, is_visible)
                     VALUES (:division, :category, :title, :slug, :description, :price_note, :min_quantity, :delivery_time, :cover_image, :is_visible)"
                );
                $stmt->execute([
                    'division' => $division, 'category' => $category, 'title' => $title, 'slug' => $slug,
                    'description' => $description, 'price_note' => $priceNote, 'min_quantity' => $minQuantity ?: null,
                    'delivery_time' => $deliveryTime ?: null, 'cover_image' => $coverImage, 'is_visible' => $isVisible,
                ]);
            }
            header('Location: services.php');
            exit;
        }
    }
}

$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = :id");
    $stmt->execute(['id' => (int) $_GET['edit']]);
    $editing = $stmt->fetch();
}

$services = $pdo->query("SELECT * FROM services ORDER BY division, sort_order, title")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Services — Administration Kalinza Group</title>
<meta name="robots" content="noindex, nofollow">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
  body { background: var(--kg-bg-alt); }
  .admin-nav { background: var(--kg-ink); padding: 16px 24px; display:flex; justify-content:space-between; align-items:center; }
  .admin-nav a { color: rgba(255,255,255,0.8); margin-right:18px; font-family:var(--font-display); font-size:0.9rem; }
  .admin-nav a:hover { color: var(--kg-orange-light); }
  table { width:100%; border-collapse:collapse; background:var(--kg-white); border-radius:var(--radius); overflow:hidden; margin-top:20px; }
  th, td { text-align:left; padding:10px 14px; border-bottom:1px solid rgba(0,0,0,0.06); font-size:0.85rem; vertical-align:middle; }
  th { font-family:var(--font-display); background:var(--kg-bg-alt); }
  td img { width:48px; height:48px; object-fit:cover; border-radius:8px; }
  .admin-form { background:var(--kg-white); padding:24px; border-radius:var(--radius); margin-top:20px; }
  .admin-form .form-grid { margin-top:14px; }
  .action-link { font-size:0.8rem; margin-right:10px; color:var(--kg-orange); }
  .current-image { display:flex; align-items:center; gap:10px; margin-bottom:8px; font-size:0.85rem; }
  .current-image img { width:40px; height:40px; object-fit:cover; border-radius:8px; }
</style>
</head>
<body>

<nav class="admin-nav">
    <div><a href="index.php">Tableau de bord</a><a href="quotes.php">Devis</a><a href="bookings.php">Réservations</a><a href="availability.php">Disponibilités</a><a href="services.php">Services</a><a href="portfolio.php">Réalisations</a></div>
    <div><a href="logout.php">Déconnexion</a></div>
</nav>

<div class="container" style="padding-top:20px;">
    <h1 style="font-size:1.6rem;">Services &amp; tarifs</h1>
    <p style="font-size:0.85rem;">Chaque service s'affiche comme une carte produit sur le site : image, quantité minimum, délai et bouton WhatsApp pré-rempli.</p>

    <div class="admin-form">
        <h3><?= $editing ? 'Modifier le service' : 'Ajouter un service' ?></h3>
        <?php if (!empty($errors)): ?><p style="color:var(--kg-orange-dark);"><?= e(implode(' ', $errors)) ?></p><?php endif; ?>
        <form method="post" enctype="multipart/form-data" class="form-grid">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int) $editing['id'] ?>"><?php endif; ?>

            <div>
                <label for="division">Division *</label>
                <select id="division" name="division" required>
                    <option value="imprimerie" <?= ($editing['division'] ?? '') === 'imprimerie' ? 'selected' : '' ?>>Kalinza Imprimerie</option>
                    <option value="prod" <?= ($editing['division'] ?? '') === 'prod' ? 'selected' : '' ?>>Kalinza Prod</option>
                    <option value="digital" <?= ($editing['division'] ?? '') === 'digital' ? 'selected' : '' ?>>Kalinza Digital</option>
                </select>
            </div>
            <div>
                <label for="category">Catégorie</label>
                <input type="text" id="category" name="category" value="<?= e($editing['category'] ?? '') ?>">
            </div>
            <div class="full">
                <label for="title">Titre du service *</label>
                <input type="text" id="title" name="title" required value="<?= e($editing['title'] ?? '') ?>">
            </div>
            <div class="full">
                <label for="description">Description courte</label>
                <textarea id="description" name="description"><?= e($editing['description'] ?? '') ?></textarea>
            </div>
            <div>
                <label for="price_note">Indication tarifaire</label>
                <input type="text" id="price_note" name="price_note" placeholder="Ex : sur devis" value="<?= e($editing['price_note'] ?? '') ?>">
            </div>
            <div>
                <label for="min_quantity">Quantité minimum</label>
                <input type="text" id="min_quantity" name="min_quantity" placeholder="Ex : 50 exemplaires" value="<?= e($editing['min_quantity'] ?? '') ?>">
            </div>
            <div>
                <label for="delivery_time">Délai de livraison</label>
                <input type="text" id="delivery_time" name="delivery_time" placeholder="Ex : 3 à 5 jours ouvrés" value="<?= e($editing['delivery_time'] ?? '') ?>">
            </div>
            <div>
                <label for="cover_image">Image de la carte produit</label>
                <?php if (!empty($editing['cover_image'])): ?>
                    <div class="current-image">
                        <img src="../<?= e($editing['cover_image']) ?>" alt="">
                        <span>Image actuelle (laisser vide pour la conserver)</span>
                    </div>
                    <input type="hidden" name="existing_cover_image" value="<?= e($editing['cover_image']) ?>">
                <?php endif; ?>
                <input type="file" id="cover_image" name="cover_image" accept="image/*">
            </div>
            <div style="display:flex; align-items:end;">
                <label><input type="checkbox" name="is_visible" <?= ($editing['is_visible'] ?? 1) ? 'checked' : '' ?>> Visible sur le site</label>
            </div>
            <div class="full">
                <button type="submit" class="btn btn--primary"><?= $editing ? 'Enregistrer les modifications' : 'Ajouter le service' ?></button>
                <?php if ($editing): ?><a href="services.php" class="btn btn--ghost">Annuler</a><?php endif; ?>
            </div>
        </form>
    </div>

    <table>
        <thead><tr><th>Image</th><th>Division</th><th>Titre</th><th>Qté min.</th><th>Délai</th><th>Visible</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($services as $s): ?>
            <tr>
                <td><?php if ($s['cover_image']): ?><img src="../<?= e($s['cover_image']) ?>" alt=""><?php else: ?>—<?php endif; ?></td>
                <td><?= e($s['division']) ?></td>
                <td><?= e($s['title']) ?></td>
                <td><?= e($s['min_quantity'] ?: '—') ?></td>
                <td><?= e($s['delivery_time'] ?: '—') ?></td>
                <td><?= $s['is_visible'] ? 'Oui' : 'Non' ?></td>
                <td>
                    <a href="services.php?edit=<?= (int) $s['id'] ?>" class="action-link">Modifier</a>
                    <a href="services.php?delete=<?= (int) $s['id'] ?>" class="action-link" onclick="return confirm('Supprimer ce service ?');">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($services)): ?><tr><td colspan="7">Aucun service enregistré.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
