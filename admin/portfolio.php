<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$errors = [];
$categories = ['photo', 'video', 'imprimerie', 'design'];

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("SELECT media_url FROM portfolio_items WHERE id = :id");
    $stmt->execute(['id' => $id]);
    if ($row = $stmt->fetch()) {
        $path = __DIR__ . '/../' . $row['media_url'];
        if ($row['media_url'] && is_file($path)) @unlink($path);
    }
    $pdo->prepare("DELETE FROM portfolio_items WHERE id = :id")->execute(['id' => $id]);
    header('Location: portfolio.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $errors[] = "Session expirée, merci de réessayer.";
    } else {
        $category    = $_POST['category'] ?? '';
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isFeatured  = isset($_POST['is_featured']) ? 1 : 0;

        if (!in_array($category, $categories, true)) $errors[] = "Catégorie invalide.";
        if ($title === '') $errors[] = "Le titre est obligatoire.";

        $mediaUrl = null;
        if (!empty($_FILES['media']['name'])) {
            $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'mp4'];
            $ext = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt, true)) {
                $errors[] = "Format de fichier non autorisé (jpg, png, webp, mp4 uniquement).";
            } else {
                $uploadDir = __DIR__ . '/../uploads/portfolio/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $filename = uniqid('pf_', true) . '.' . $ext;
                if (move_uploaded_file($_FILES['media']['tmp_name'], $uploadDir . $filename)) {
                    $mediaUrl = 'uploads/portfolio/' . $filename;
                }
            }
        } elseif (!empty($_POST['existing_media'])) {
            $mediaUrl = $_POST['existing_media'];
        } else {
            $errors[] = "Merci d'ajouter un fichier (image ou vidéo).";
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare(
                "INSERT INTO portfolio_items (category, title, description, media_url, thumbnail_url, is_featured)
                 VALUES (:category, :title, :description, :media_url, :media_url, :is_featured)"
            );
            $stmt->execute([
                'category' => $category, 'title' => $title, 'description' => $description,
                'media_url' => $mediaUrl, 'is_featured' => $isFeatured,
            ]);
            header('Location: portfolio.php');
            exit;
        }
    }
}

$items = $pdo->query("SELECT * FROM portfolio_items ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Réalisations — Administration Kalinza Group</title>
<meta name="robots" content="noindex, nofollow">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
  body { background: var(--kg-bg-alt); }
  .admin-nav { background: var(--kg-ink); padding: 16px 24px; display:flex; justify-content:space-between; align-items:center; }
  .admin-nav a { color: rgba(255,255,255,0.8); margin-right:18px; font-family:var(--font-display); font-size:0.9rem; }
  .admin-nav a:hover { color: var(--kg-orange-light); }
  .admin-form { background:var(--kg-white); padding:24px; border-radius:var(--radius); margin-top:20px; }
  .admin-form .form-grid { margin-top:14px; }
  .pf-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:18px; margin-top:24px; }
  .pf-card { background:var(--kg-white); border-radius:var(--radius); overflow:hidden; }
  .pf-card img { width:100%; height:140px; object-fit:cover; }
  .pf-card .pf-body { padding:12px; }
  .pf-card .action-link { font-size:0.8rem; color:var(--kg-orange); margin-right:10px; }
</style>
</head>
<body>

<nav class="admin-nav">
    <div><a href="index.php">Tableau de bord</a><a href="quotes.php">Devis</a><a href="bookings.php">Réservations</a><a href="availability.php">Disponibilités</a><a href="services.php">Services</a><a href="portfolio.php">Réalisations</a></div>
    <div><a href="logout.php">Déconnexion</a></div>
</nav>

<div class="container" style="padding-top:20px;">
    <h1 style="font-size:1.6rem;">Réalisations</h1>

    <div class="admin-form">
        <h3>Ajouter une réalisation</h3>
        <?php if (!empty($errors)): ?><p style="color:var(--kg-orange-dark);"><?= e(implode(' ', $errors)) ?></p><?php endif; ?>
        <form method="post" enctype="multipart/form-data" class="form-grid">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <div>
                <label for="category">Catégorie *</label>
                <select id="category" name="category" required>
                    <?php foreach ($categories as $c): ?><option value="<?= e($c) ?>"><?= e(ucfirst($c)) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="title">Titre *</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="full">
                <label for="description">Description</label>
                <textarea id="description" name="description"></textarea>
            </div>
            <div>
                <label for="media">Fichier (image ou vidéo) *</label>
                <input type="file" id="media" name="media" accept="image/*,video/mp4">
            </div>
            <div style="display:flex; align-items:end;">
                <label><input type="checkbox" name="is_featured"> Mettre en avant sur l'accueil</label>
            </div>
            <div class="full">
                <button type="submit" class="btn btn--primary">Ajouter</button>
            </div>
        </form>
    </div>

    <div class="pf-grid">
        <?php foreach ($items as $item): ?>
            <div class="pf-card">
                <img src="../<?= e($item['media_url']) ?>" alt="<?= e($item['title']) ?>">
                <div class="pf-body">
                    <strong><?= e($item['title']) ?></strong><br>
                    <small><?= e(ucfirst($item['category'])) ?><?= $item['is_featured'] ? ' · en avant' : '' ?></small><br>
                    <a href="portfolio.php?delete=<?= (int) $item['id'] ?>" class="action-link" onclick="return confirm('Supprimer cette réalisation ?');">Supprimer</a>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($items)): ?><p>Aucune réalisation ajoutée pour le moment.</p><?php endif; ?>
    </div>
</div>

</body>
</html>
