<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Mise à jour de statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id']) && !empty($_POST['status'])) {
    if (csrf_check($_POST['csrf_token'] ?? '')) {
        $allowed = ['nouveau', 'en_cours', 'traite', 'refuse'];
        if (in_array($_POST['status'], $allowed, true)) {
            $stmt = $pdo->prepare("UPDATE quote_requests SET status = :status, admin_note = :note WHERE id = :id");
            $stmt->execute([
                'status' => $_POST['status'],
                'note'   => trim($_POST['admin_note'] ?? ''),
                'id'     => (int) $_POST['id'],
            ]);
        }
    }
    header('Location: quotes.php');
    exit;
}

$statusFilter = $_GET['status'] ?? '';
$allowedStatus = ['nouveau', 'en_cours', 'traite', 'refuse'];
if ($statusFilter !== '' && in_array($statusFilter, $allowedStatus, true)) {
    $stmt = $pdo->prepare("SELECT * FROM quote_requests WHERE status = :s ORDER BY created_at DESC");
    $stmt->execute(['s' => $statusFilter]);
} else {
    $stmt = $pdo->query("SELECT * FROM quote_requests ORDER BY created_at DESC");
}
$quotes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Devis — Administration Kalinza Group</title>
<meta name="robots" content="noindex, nofollow">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
  body { background: var(--kg-bg-alt); }
  .admin-nav { background: var(--kg-ink); padding: 16px 24px; display:flex; justify-content:space-between; align-items:center; }
  .admin-nav a { color: rgba(255,255,255,0.8); margin-right:18px; font-family:var(--font-display); font-size:0.9rem; }
  .admin-nav a:hover { color: var(--kg-orange-light); }
  table { width:100%; border-collapse:collapse; background:var(--kg-white); border-radius:var(--radius); overflow:hidden; margin-top:20px; }
  th, td { text-align:left; padding:10px 14px; border-bottom:1px solid rgba(0,0,0,0.06); font-size:0.85rem; vertical-align:top; }
  th { font-family:var(--font-display); background:var(--kg-bg-alt); }
  select, textarea.note { font-size:0.85rem; padding:6px 8px; }
  .filters a { margin-right:10px; font-size:0.85rem; font-family:var(--font-display); }
  .filters a.is-active { color: var(--kg-orange); }
</style>
</head>
<body>

<nav class="admin-nav">
    <div><a href="index.php">Tableau de bord</a><a href="quotes.php">Devis</a><a href="bookings.php">Réservations</a><a href="availability.php">Disponibilités</a><a href="services.php">Services</a><a href="portfolio.php">Réalisations</a></div>
    <div><a href="logout.php">Déconnexion</a></div>
</nav>

<div class="container" style="padding-top:20px;">
    <h1 style="font-size:1.6rem;">Demandes de devis</h1>

    <div class="filters">
        <a href="quotes.php" class="<?= $statusFilter === '' ? 'is-active' : '' ?>">Tous</a>
        <?php foreach ($allowedStatus as $s): ?>
            <a href="quotes.php?status=<?= e($s) ?>" class="<?= $statusFilter === $s ? 'is-active' : '' ?>"><?= e(ucfirst(str_replace('_', ' ', $s))) ?></a>
        <?php endforeach; ?>
    </div>

    <table>
        <thead>
        <tr><th>Client</th><th>Division</th><th>Besoin</th><th>Contact</th><th>Statut</th><th>Note interne</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($quotes as $q): ?>
            <tr>
                <td><?= e($q['full_name']) ?><?= $q['company'] ? '<br><small>' . e($q['company']) . '</small>' : '' ?></td>
                <td><?= e($q['division']) ?><?= $q['service_type'] ? '<br><small>' . e($q['service_type']) . '</small>' : '' ?></td>
                <td style="max-width:220px;"><?= nl2br(e($q['description'])) ?>
                    <?php if ($q['attachment_path']): ?><br><a href="../<?= e($q['attachment_path']) ?>" target="_blank">Voir fichier joint</a><?php endif; ?>
                </td>
                <td><?= e($q['phone']) ?><?= $q['email'] ? '<br>' . e($q['email']) : '' ?></td>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= (int) $q['id'] ?>">
                    <td>
                        <select name="status">
                            <?php foreach ($allowedStatus as $s): ?>
                                <option value="<?= e($s) ?>" <?= $q['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $s))) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><textarea name="admin_note" class="note" rows="2" style="width:160px;"><?= e($q['admin_note']) ?></textarea></td>
                    <td><button type="submit" class="btn btn--primary" style="padding:8px 14px; font-size:0.8rem;">Mettre à jour</button></td>
                </form>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($quotes)): ?><tr><td colspan="7">Aucune demande.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
