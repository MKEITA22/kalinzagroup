<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$errors = [];
$success = '';

// Ajout rapide de créneaux (une date + plusieurs heures)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_slots') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $errors[] = "Session expirée, merci de réessayer.";
    } else {
        $date  = $_POST['slot_date'] ?? '';
        $times = $_POST['slot_times'] ?? '';

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $errors[] = "Date invalide.";
        } elseif (trim($times) === '') {
            $errors[] = "Merci d'indiquer au moins un horaire (ex : 09:00, 11:00, 14:00).";
        } else {
            $list = array_filter(array_map('trim', explode(',', $times)));
            $inserted = 0;
            $stmt = $pdo->prepare("INSERT IGNORE INTO availability_slots (slot_date, slot_time) VALUES (:d, :t)");
            foreach ($list as $t) {
                if (preg_match('/^\d{1,2}:\d{2}$/', $t)) {
                    $stmt->execute(['d' => $date, 't' => $t . ':00']);
                    $inserted += $stmt->rowCount();
                }
            }
            $success = $inserted > 0 ? "$inserted créneau(x) ajouté(s) pour le " . date('d/m/Y', strtotime($date)) . "." : "Aucun nouveau créneau ajouté (peut-être déjà existants).";
        }
    }
}

// Basculer disponible / indisponible
if (isset($_GET['toggle'])) {
    $pdo->prepare("UPDATE availability_slots SET is_available = 1 - is_available WHERE id = :id")->execute(['id' => (int) $_GET['toggle']]);
    header('Location: availability.php' . (!empty($_GET['date']) ? '?date=' . urlencode($_GET['date']) : ''));
    exit;
}

// Supprimer un créneau
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM availability_slots WHERE id = :id")->execute(['id' => (int) $_GET['delete']]);
    header('Location: availability.php' . (!empty($_GET['date']) ? '?date=' . urlencode($_GET['date']) : ''));
    exit;
}

$today = new DateTime();
$monthParam = $_GET['month'] ?? $today->format('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $monthParam)) $monthParam = $today->format('Y-m');
[$year, $month] = array_map('intval', explode('-', $monthParam));

$selectedDate = $_GET['date'] ?? $today->format('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) $selectedDate = $today->format('Y-m-d');

$firstDay = sprintf('%04d-%02d-01', $year, $month);
$lastDay  = date('Y-m-t', strtotime($firstDay));
$stmt = $pdo->prepare(
    "SELECT slot_date, COUNT(*) AS total, SUM(is_available) AS available
     FROM availability_slots WHERE slot_date BETWEEN :start AND :end GROUP BY slot_date"
);
$stmt->execute(['start' => $firstDay, 'end' => $lastDay]);
$slotsByDate = [];
foreach ($stmt->fetchAll() as $row) {
    $slotsByDate[$row['slot_date']] = ['total' => (int) $row['total'], 'available' => (int) $row['available']];
}

$grid = build_month_grid($year, $month);
$monthLabels = ['', 'Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
$prevMonth = (new DateTime($firstDay))->modify('-1 month')->format('Y-m');
$nextMonth = (new DateTime($firstDay))->modify('+1 month')->format('Y-m');

$stmt = $pdo->prepare("SELECT * FROM availability_slots WHERE slot_date = :d ORDER BY slot_time");
$stmt->execute(['d' => $selectedDate]);
$daySlots = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Disponibilités — Administration Kalinza Group</title>
<meta name="robots" content="noindex, nofollow">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
  body { background: var(--kg-bg-alt); }
  .admin-nav { background: var(--kg-ink); padding: 16px 24px; display:flex; justify-content:space-between; align-items:center; }
  .admin-nav a { color: rgba(255,255,255,0.8); margin-right:18px; font-family:var(--font-display); font-size:0.9rem; }
  .admin-nav a:hover { color: var(--kg-orange-light); }
  .admin-form { background:var(--kg-white); padding:22px; border-radius:var(--radius); margin-top:20px; }
  .slot-mini { display:inline-flex; align-items:center; gap:8px; background:var(--kg-bg-alt); padding:6px 12px; border-radius:999px; font-size:0.85rem; margin:4px 6px 0 0; }
  .slot-mini--off { opacity:0.55; text-decoration:line-through; }
  .slot-mini a { font-size:0.75rem; color:var(--kg-orange); text-decoration:underline; }
</style>
</head>
<body>

<nav class="admin-nav">
    <div><a href="index.php">Tableau de bord</a><a href="quotes.php">Devis</a><a href="bookings.php">Réservations</a><a href="availability.php">Disponibilités</a><a href="services.php">Services</a><a href="portfolio.php">Réalisations</a></div>
    <div><a href="logout.php">Déconnexion</a></div>
</nav>

<div class="container" style="padding-top:20px;">
    <h1 style="font-size:1.6rem;">Gestion des disponibilités</h1>
    <p style="font-size:0.85rem;">Ouvrez des créneaux pour que les clients puissent réserver directement sur le calendrier public.</p>

    <?php if ($success): ?><p style="color:var(--kg-green-dark); font-weight:600;"><?= e($success) ?></p><?php endif; ?>
    <?php if (!empty($errors)): ?><p style="color:var(--kg-orange-dark);"><?= e(implode(' ', $errors)) ?></p><?php endif; ?>

    <div class="admin-form">
        <h3>Ajouter des créneaux</h3>
        <form method="post" class="form-grid">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="add_slots">
            <div>
                <label for="slot_date">Date</label>
                <input type="date" id="slot_date" name="slot_date" required value="<?= e($selectedDate) ?>" min="<?= e($today->format('Y-m-d')) ?>">
            </div>
            <div>
                <label for="slot_times">Horaires (séparés par des virgules)</label>
                <input type="text" id="slot_times" name="slot_times" placeholder="09:00, 11:00, 14:00, 16:00" required>
            </div>
            <div class="full">
                <button type="submit" class="btn btn--primary">Ajouter ces créneaux</button>
            </div>
        </form>
    </div>

    <div class="cal-nav" style="margin-top:28px;">
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
                $info = $slotsByDate[$day['date']] ?? null;
                $classes = ['cal-day'];
                if (!$day['inMonth']) $classes[] = 'cal-day--muted';
                if ($day['isPast']) $classes[] = 'cal-day--past';
                if ($day['isToday']) $classes[] = 'cal-day--today';
                if ($day['date'] === $selectedDate) $classes[] = 'cal-day--selected';
                if ($info && $info['available'] > 0) $classes[] = 'cal-day--available';
                elseif ($info && $info['available'] == 0) $classes[] = 'cal-day--full';
            ?>
            <a href="?month=<?= e($monthParam) ?>&date=<?= e($day['date']) ?>" class="<?= implode(' ', $classes) ?>">
                <span class="cal-day__num"><?= $day['day'] ?></span>
                <?php if ($info): ?><span class="cal-day__dot <?= $info['available'] == 0 ? 'cal-day__dot--full' : '' ?>"></span><?php endif; ?>
            </a>
        <?php endforeach; endforeach; ?>
    </div>

    <h3 style="margin-top:30px;">Créneaux du <?= e((new DateTime($selectedDate))->format('d/m/Y')) ?></h3>
    <div>
        <?php foreach ($daySlots as $slot): ?>
            <span class="slot-mini <?= $slot['is_available'] ? '' : 'slot-mini--off' ?>">
                <?= e(substr($slot['slot_time'], 0, 5)) ?>
                <a href="?toggle=<?= (int) $slot['id'] ?>&date=<?= e($selectedDate) ?>&month=<?= e($monthParam) ?>"><?= $slot['is_available'] ? 'Bloquer' : 'Libérer' ?></a>
                <a href="?delete=<?= (int) $slot['id'] ?>&date=<?= e($selectedDate) ?>&month=<?= e($monthParam) ?>" onclick="return confirm('Supprimer ce créneau ?');">Suppr.</a>
            </span>
        <?php endforeach; ?>
        <?php if (empty($daySlots)): ?><p>Aucun créneau pour cette date.</p><?php endif; ?>
    </div>
</div>

</body>
</html>
