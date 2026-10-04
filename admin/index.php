<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$counts = [
    'devis_nouveau'     => $pdo->query("SELECT COUNT(*) FROM quote_requests WHERE status='nouveau'")->fetchColumn(),
    'reservations_attente' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status='en_attente'")->fetchColumn(),
    'messages_nouveau'  => $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status='nouveau'")->fetchColumn(),
];

$recentQuotes = $pdo->query(
    "SELECT * FROM quote_requests ORDER BY created_at DESC LIMIT 8"
)->fetchAll();

$recentBookings = $pdo->query(
    "SELECT * FROM bookings ORDER BY created_at DESC LIMIT 8"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tableau de bord — Administration Kalinza Group</title>
<meta name="robots" content="noindex, nofollow">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
  body { background: var(--kg-bg-alt); }
  .admin-nav { background: var(--kg-ink); padding: 16px 24px; display:flex; justify-content:space-between; align-items:center; }
  .admin-nav a { color: rgba(255,255,255,0.8); margin-right:18px; font-family:var(--font-display); font-size:0.9rem; }
  .admin-nav a:hover { color: var(--kg-orange-light); }
  .stat-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:20px; margin:30px 0; }
  .stat-card { background:var(--kg-white); border-radius:var(--radius); padding:22px; border-left:4px solid var(--kg-orange); }
  .stat-card .value { font-family:var(--font-display); font-size:2rem; font-weight:800; }
  table { width:100%; border-collapse:collapse; background:var(--kg-white); border-radius:var(--radius); overflow:hidden; }
  th, td { text-align:left; padding:10px 14px; border-bottom:1px solid rgba(0,0,0,0.06); font-size:0.9rem; }
  th { font-family:var(--font-display); background:var(--kg-bg-alt); }
  .badge { padding:3px 10px; border-radius:999px; font-size:0.75rem; font-weight:600; background:var(--kg-orange-light); color:#5a3300; }
</style>
</head>
<body>

<nav class="admin-nav">
    <div><a href="index.php">Tableau de bord</a><a href="quotes.php">Devis</a><a href="bookings.php">Réservations</a><a href="availability.php">Disponibilités</a><a href="services.php">Services</a><a href="portfolio.php">Réalisations</a></div>
    <div>
        <span style="color:#fff; margin-right:14px;"><?= e($_SESSION['admin_name']) ?></span>
        <a href="logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container" style="padding-top:20px;">
    <h1 style="font-size:1.6rem;">Tableau de bord</h1>

    <div class="stat-grid">
        <div class="stat-card"><div class="value"><?= (int)$counts['devis_nouveau'] ?></div><div>Devis en attente</div></div>
        <div class="stat-card"><div class="value"><?= (int)$counts['reservations_attente'] ?></div><div>Réservations en attente</div></div>
        <div class="stat-card"><div class="value"><?= (int)$counts['messages_nouveau'] ?></div><div>Nouveaux messages</div></div>
    </div>

    <h2 style="font-size:1.2rem;">Derniers devis</h2>
    <table>
        <thead><tr><th>Client</th><th>Division</th><th>Téléphone</th><th>Statut</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($recentQuotes as $q): ?>
            <tr>
                <td><?= e($q['full_name']) ?></td>
                <td><?= e($q['division']) ?></td>
                <td><?= e($q['phone']) ?></td>
                <td><span class="badge"><?= e($q['status']) ?></span></td>
                <td><?= e(date('d/m/Y', strtotime($q['created_at']))) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($recentQuotes)): ?><tr><td colspan="5">Aucune demande pour le moment.</td></tr><?php endif; ?>
        </tbody>
    </table>

    <h2 style="font-size:1.2rem; margin-top:30px;">Dernières réservations</h2>
    <table>
        <thead><tr><th>Client</th><th>Type de séance</th><th>Date souhaitée</th><th>Statut</th></tr></thead>
        <tbody>
        <?php foreach ($recentBookings as $b): ?>
            <tr>
                <td><?= e($b['full_name']) ?></td>
                <td><?= e($b['session_type']) ?></td>
                <td><?= e(date('d/m/Y', strtotime($b['requested_date']))) ?></td>
                <td><span class="badge"><?= e($b['status']) ?></span></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($recentBookings)): ?><tr><td colspan="4">Aucune réservation pour le moment.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
