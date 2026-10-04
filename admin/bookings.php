<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$allowedStatus = ['en_attente', 'confirmee', 'reportee', 'annulee'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id']) && !empty($_POST['status'])) {
    if (csrf_check($_POST['csrf_token'] ?? '') && in_array($_POST['status'], $allowedStatus, true)) {
        $id = (int) $_POST['id'];
        $newStatus = $_POST['status'];

        $stmt = $pdo->prepare("UPDATE bookings SET status = :status, admin_note = :note WHERE id = :id");
        $stmt->execute([
            'status' => $newStatus,
            'note'   => trim($_POST['admin_note'] ?? ''),
            'id'     => $id,
        ]);

        // Libérer ou ré-occuper le créneau associé selon le nouveau statut.
        $stmt = $pdo->prepare("SELECT slot_id FROM bookings WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $slotId = $stmt->fetchColumn();
        if ($slotId) {
            $makeAvailable = in_array($newStatus, ['annulee', 'reportee'], true) ? 1 : 0;
            $pdo->prepare("UPDATE availability_slots SET is_available = :a WHERE id = :id")
                ->execute(['a' => $makeAvailable, 'id' => $slotId]);
        }
    }
    header('Location: bookings.php' . (!empty($_GET['date']) ? '?date=' . urlencode($_GET['date']) : ''));
    exit;
}

$statusFilter = $_GET['status'] ?? '';
$dateFilter = $_GET['date'] ?? '';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFilter)) $dateFilter = '';

$where = [];
$params = [];
if ($statusFilter !== '' && in_array($statusFilter, $allowedStatus, true)) {
    $where[] = 'status = :s';
    $params['s'] = $statusFilter;
}
if ($dateFilter !== '') {
    $where[] = 'requested_date = :d';
    $params['d'] = $dateFilter;
}
$sql = "SELECT * FROM bookings" . (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '') . " ORDER BY requested_date ASC, requested_time ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

// --- Données du calendrier (mois affiché) ---
$today = new DateTime();
$monthParam = $_GET['month'] ?? $today->format('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $monthParam)) $monthParam = $today->format('Y-m');
[$year, $month] = array_map('intval', explode('-', $monthParam));

$firstDay = sprintf('%04d-%02d-01', $year, $month);
$lastDay  = date('Y-m-t', strtotime($firstDay));
$stmt = $pdo->prepare(
    "SELECT requested_date,
            SUM(status='en_attente') AS en_attente,
            SUM(status='confirmee') AS confirmee,
            COUNT(*) AS total
     FROM bookings WHERE requested_date BETWEEN :start AND :end GROUP BY requested_date"
);
$stmt->execute(['start' => $firstDay, 'end' => $lastDay]);
$bookingsByDate = [];
foreach ($stmt->fetchAll() as $row) {
    $bookingsByDate[$row['requested_date']] = $row;
}

$grid = build_month_grid($year, $month);
$monthLabels = ['', 'Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
$prevMonth = (new DateTime($firstDay))->modify('-1 month')->format('Y-m');
$nextMonth = (new DateTime($firstDay))->modify('+1 month')->format('Y-m');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Réservations — Administration Kalinza Group</title>
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
    <h1 style="font-size:1.6rem;">Réservations de séances</h1>
    <p style="font-size:0.85rem;">Rappel : un créneau n'est jamais confirmé automatiquement. Annuler ou reporter une réservation libère automatiquement son créneau.</p>

    <div class="cal-nav" style="margin-top:20px;">
        <a href="?month=<?= e($prevMonth) ?>" class="btn btn--ghost cal-nav__btn">‹ Mois précédent</a>
        <strong><?= e($monthLabels[$month]) ?> <?= $year ?></strong>
        <a href="?month=<?= e($nextMonth) ?>" class="btn btn--ghost cal-nav__btn">Mois suivant ›</a>
    </div>

    <div class="cal-grid">
        <?php foreach (['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'] as $wd): ?>
            <div class="cal-weekday"><?= $wd ?></div>
        <?php endforeach; ?>
        <?php foreach ($grid as $week): foreach ($week as $day): ?>
            <?php
                $info = $bookingsByDate[$day['date']] ?? null;
                $classes = ['cal-day'];
                if (!$day['inMonth']) $classes[] = 'cal-day--muted';
                if ($day['isToday']) $classes[] = 'cal-day--today';
                if ($day['date'] === $dateFilter) $classes[] = 'cal-day--selected';
                if ($info && $info['total'] > 0) $classes[] = $info['en_attente'] > 0 ? 'cal-day--available' : 'cal-day--full';
            ?>
            <a href="?month=<?= e($monthParam) ?>&date=<?= e($day['date']) ?>" class="<?= implode(' ', $classes) ?>">
                <span class="cal-day__num"><?= $day['day'] ?></span>
                <?php if ($info && $info['total'] > 0): ?><span class="cal-day__dot"></span><span style="font-size:0.65rem;"><?= (int) $info['total'] ?></span><?php endif; ?>
            </a>
        <?php endforeach; endforeach; ?>
    </div>
    <p class="cal-legend"><span class="cal-legend__dot cal-legend__dot--available"></span> Dont en attente &nbsp; <span class="cal-legend__dot cal-legend__dot--full"></span> Toutes traitées</p>

    <div class="filters" style="margin-top:24px;">
        <a href="bookings.php" class="<?= $statusFilter === '' && $dateFilter === '' ? 'is-active' : '' ?>">Toutes</a>
        <?php foreach ($allowedStatus as $s): ?>
            <a href="bookings.php?status=<?= e($s) ?>" class="<?= $statusFilter === $s ? 'is-active' : '' ?>"><?= e(ucfirst(str_replace('_', ' ', $s))) ?></a>
        <?php endforeach; ?>
        <?php if ($dateFilter !== ''): ?>
            <span style="margin-left:12px;">Filtré sur le <?= e((new DateTime($dateFilter))->format('d/m/Y')) ?> — <a href="bookings.php">retirer</a></span>
        <?php endif; ?>
    </div>

    <table>
        <thead>
        <tr><th>Client</th><th>Séance</th><th>Date / heure</th><th>Contact</th><th>Statut</th><th>Note interne</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($bookings as $b): ?>
            <tr>
                <td><?= e($b['full_name']) ?></td>
                <td><?= e($b['session_type']) ?><?= $b['notes'] ? '<br><small>' . nl2br(e($b['notes'])) . '</small>' : '' ?></td>
                <td><?= e(date('d/m/Y', strtotime($b['requested_date']))) ?><?= $b['requested_time'] ? ' — ' . e(date('H:i', strtotime($b['requested_time']))) : '' ?></td>
                <td><?= e($b['phone']) ?><?= $b['email'] ? '<br>' . e($b['email']) : '' ?></td>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
                    <td>
                        <select name="status">
                            <?php foreach ($allowedStatus as $s): ?>
                                <option value="<?= e($s) ?>" <?= $b['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $s))) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><textarea name="admin_note" rows="2" style="width:160px;"><?= e($b['admin_note']) ?></textarea></td>
                    <td><button type="submit" class="btn btn--primary" style="padding:8px 14px; font-size:0.8rem;">Mettre à jour</button></td>
                </form>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($bookings)): ?><tr><td colspan="7">Aucune réservation.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
