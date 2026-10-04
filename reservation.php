<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Réserver une séance — Kalinza Prod';
$errors = [];
$success = false;

$today = new DateTime();
$minMonth = $today->format('Y-m');
$maxMonthDt = (clone $today)->modify('+3 months');
$maxMonth = $maxMonthDt->format('Y-m');

$monthParam = $_GET['month'] ?? $minMonth;
if (!preg_match('/^\d{4}-\d{2}$/', $monthParam) || $monthParam < $minMonth) $monthParam = $minMonth;
if ($monthParam > $maxMonth) $monthParam = $maxMonth;
[$year, $month] = array_map('intval', explode('-', $monthParam));

$selectedDate = $_GET['date'] ?? '';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate) || $selectedDate < $today->format('Y-m-d')) {
    $selectedDate = '';
}

$selectedSlotId = isset($_GET['slot']) ? (int) $_GET['slot'] : 0;

// --- Traitement de la soumission du formulaire ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['website'])) {
        $success = true; // honeypot
    } elseif (!csrf_check($_POST['csrf_token'] ?? '')) {
        $errors[] = "Votre session a expiré, merci de renvoyer le formulaire.";
    } else {
        $slotId      = (int) ($_POST['slot_id'] ?? 0);
        $sessionType = trim($_POST['session_type'] ?? '');
        $fullName    = trim($_POST['full_name'] ?? '');
        $phone       = trim($_POST['phone'] ?? '');
        $email       = trim($_POST['email'] ?? '');
        $notes       = trim($_POST['notes'] ?? '');

        if ($sessionType === '') $errors[] = "Merci de préciser le type de séance.";
        if ($fullName === '') $errors[] = "Le nom et prénom sont obligatoires.";
        if ($phone === '') $errors[] = "Le numéro de téléphone est obligatoire.";
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "L'adresse e-mail n'est pas valide.";
        if ($slotId <= 0) $errors[] = "Merci de choisir un créneau sur le calendrier.";

        if (empty($errors)) {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("SELECT * FROM availability_slots WHERE id = :id FOR UPDATE");
            $stmt->execute(['id' => $slotId]);
            $slot = $stmt->fetch();

            if (!$slot || !$slot['is_available']) {
                $pdo->rollBack();
                $errors[] = "Ce créneau vient d'être réservé par quelqu'un d'autre. Merci d'en choisir un autre.";
            } else {
                $insert = $pdo->prepare(
                    "INSERT INTO bookings (session_type, slot_id, requested_date, requested_time, full_name, phone, email, notes, status)
                     VALUES (:session_type, :slot_id, :requested_date, :requested_time, :full_name, :phone, :email, :notes, 'en_attente')"
                );
                $insert->execute([
                    'session_type'   => $sessionType,
                    'slot_id'        => $slot['id'],
                    'requested_date' => $slot['slot_date'],
                    'requested_time' => $slot['slot_time'],
                    'full_name'      => $fullName,
                    'phone'          => $phone,
                    'email'          => $email ?: null,
                    'notes'          => $notes ?: null,
                ]);
                // Le créneau est réservé dès la demande pour éviter les doublons ;
                // il n'est jamais confirmé automatiquement — l'admin valide ensuite la réservation.
                $pdo->prepare("UPDATE availability_slots SET is_available = 0 WHERE id = :id")->execute(['id' => $slot['id']]);
                $pdo->commit();
                // TODO : notifier l'administrateur par e-mail
                $success = true;
            }
        }
    }
}

// --- Données du calendrier (mois affiché) ---
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

$prevMonthDt = (new DateTime($firstDay))->modify('-1 month');
$nextMonthDt = (new DateTime($firstDay))->modify('+1 month');
$prevMonth = $prevMonthDt->format('Y-m');
$nextMonth = $nextMonthDt->format('Y-m');

// --- Créneaux du jour sélectionné ---
$daySlots = [];
if ($selectedDate !== '') {
    $stmt = $pdo->prepare("SELECT * FROM availability_slots WHERE slot_date = :d ORDER BY slot_time");
    $stmt->execute(['d' => $selectedDate]);
    $daySlots = $stmt->fetchAll();
}

$selectedSlot = null;
if ($selectedSlotId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM availability_slots WHERE id = :id AND is_available = 1");
    $stmt->execute(['id' => $selectedSlotId]);
    $selectedSlot = $stmt->fetch() ?: null;
}

$anySlotsConfigured = (bool) $pdo->query("SELECT 1 FROM availability_slots LIMIT 1")->fetchColumn();

require_once __DIR__ . '/includes/header.php';
?>

<section class="divisions">
    <div class="container" style="max-width:860px;">
        <h1>Réserver une séance</h1>
        <p>Choisissez une date et un créneau disponible sur le calendrier ci-dessous.</p>

        <?php if ($success): ?>
            <div class="service-card" style="border-left:4px solid var(--kg-green); padding:22px; margin-top:24px;">
                <h3>Merci, votre demande de réservation a bien été envoyée.</h3>
                <p>Votre créneau est provisoirement bloqué : notre équipe vous contactera pour confirmer définitivement.</p>
            </div>

        <?php elseif (!$anySlotsConfigured): ?>
            <div class="service-card" style="border-left:4px solid var(--kg-orange); padding:22px; margin-top:24px;">
                <h3>Aucun créneau n'est encore ouvert à la réservation.</h3>
                <p>Contactez-nous directement, nous conviendrons d'une date ensemble.</p>
                <a href="<?= e($whatsapp) ?>" class="btn btn--whatsapp" target="_blank" rel="noopener"><?= service_icon('whatsapp') ?> Écrire sur WhatsApp</a>
            </div>

        <?php else: ?>

            <?php if (!empty($errors)): ?>
                <div class="service-card" style="border-left:4px solid var(--kg-orange); padding:18px; margin-top:20px;">
                    <ul style="margin:0; padding-left:18px;">
                        <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Calendrier -->
            <div class="cal-nav">
                <?php if ($prevMonth >= $minMonth): ?>
                    <a href="?month=<?= e($prevMonth) ?>" class="btn btn--ghost cal-nav__btn">‹ Mois précédent</a>
                <?php else: ?>
                    <span class="btn btn--ghost cal-nav__btn" style="opacity:0.4; pointer-events:none;">‹ Mois précédent</span>
                <?php endif; ?>
                <strong><?= e($monthLabels[$month]) ?> <?= $year ?></strong>
                <?php if ($nextMonth <= $maxMonth): ?>
                    <a href="?month=<?= e($nextMonth) ?>" class="btn btn--ghost cal-nav__btn">Mois suivant ›</a>
                <?php else: ?>
                    <span class="btn btn--ghost cal-nav__btn" style="opacity:0.4; pointer-events:none;">Mois suivant ›</span>
                <?php endif; ?>
            </div>

            <div class="cal-grid">
                <?php foreach (['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'] as $wd): ?>
                    <div class="cal-weekday"><?= $wd ?></div>
                <?php endforeach; ?>

                <?php foreach ($grid as $week): foreach ($week as $day): ?>
                    <?php
                        $info = $slotsByDate[$day['date']] ?? null;
                        $hasAvailable = $info && $info['available'] > 0;
                        $isFull = $info && $info['available'] == 0;
                        $classes = ['cal-day'];
                        if (!$day['inMonth']) $classes[] = 'cal-day--muted';
                        if ($day['isPast']) $classes[] = 'cal-day--past';
                        if ($day['isToday']) $classes[] = 'cal-day--today';
                        if ($day['date'] === $selectedDate) $classes[] = 'cal-day--selected';
                        if ($hasAvailable) $classes[] = 'cal-day--available';
                        elseif ($isFull) $classes[] = 'cal-day--full';
                    ?>
                    <?php if ($hasAvailable && !$day['isPast']): ?>
                        <a href="?month=<?= e($monthParam) ?>&date=<?= e($day['date']) ?>" class="<?= implode(' ', $classes) ?>">
                            <span class="cal-day__num"><?= $day['day'] ?></span>
                            <span class="cal-day__dot"></span>
                        </a>
                    <?php else: ?>
                        <span class="<?= implode(' ', $classes) ?>">
                            <span class="cal-day__num"><?= $day['day'] ?></span>
                            <?php if ($isFull): ?><span class="cal-day__dot cal-day__dot--full"></span><?php endif; ?>
                        </span>
                    <?php endif; ?>
                <?php endforeach; endforeach; ?>
            </div>
            <p class="cal-legend"><span class="cal-legend__dot cal-legend__dot--available"></span> Disponible &nbsp; <span class="cal-legend__dot cal-legend__dot--full"></span> Complet</p>

            <!-- Créneaux du jour sélectionné -->
            <?php if ($selectedDate !== '' && !empty($daySlots)): ?>
                <div style="margin-top:30px;">
                    <h3>Créneaux du <?= e((new DateTime($selectedDate))->format('d/m/Y')) ?></h3>
                    <div class="slot-list">
                        <?php foreach ($daySlots as $slot): ?>
                            <?php if ($slot['is_available']): ?>
                                <a href="?month=<?= e($monthParam) ?>&date=<?= e($selectedDate) ?>&slot=<?= (int) $slot['id'] ?>#booking-form"
                                   class="slot-btn <?= $selectedSlot && (int) $selectedSlot['id'] === (int) $slot['id'] ? 'is-selected' : '' ?>">
                                    <?= e(substr($slot['slot_time'], 0, 5)) ?>
                                </a>
                            <?php else: ?>
                                <span class="slot-btn slot-btn--taken"><?= e(substr($slot['slot_time'], 0, 5)) ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php elseif ($selectedDate !== ''): ?>
                <p style="margin-top:24px;">Aucun créneau pour cette date.</p>
            <?php endif; ?>

            <!-- Formulaire de réservation -->
            <?php if ($selectedSlot): ?>
                <div id="booking-form" style="margin-top:34px;">
                    <h3>Vos coordonnées</h3>
                    <p style="font-size:0.9rem;">
                        Créneau choisi : <strong><?= e((new DateTime($selectedSlot['slot_date']))->format('d/m/Y')) ?> à <?= e(substr($selectedSlot['slot_time'], 0, 5)) ?></strong>
                    </p>
                    <form method="post" class="form-grid" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">
                        <input type="hidden" name="slot_id" value="<?= (int) $selectedSlot['id'] ?>">

                        <div class="full">
                            <label for="session_type">Type de séance *</label>
                            <select id="session_type" name="session_type" required>
                                <option value="">Choisir…</option>
                                <option value="Portrait">Portrait</option>
                                <option value="Famille / Événement">Famille / Événement</option>
                                <option value="Corporate">Corporate</option>
                                <option value="Vidéo">Production vidéo</option>
                                <option value="Autre">Autre</option>
                            </select>
                        </div>
                        <div>
                            <label for="full_name">Nom et prénom *</label>
                            <input type="text" id="full_name" name="full_name" required value="<?= e($_POST['full_name'] ?? '') ?>">
                        </div>
                        <div>
                            <label for="phone">Téléphone / WhatsApp *</label>
                            <input type="tel" id="phone" name="phone" required value="<?= e($_POST['phone'] ?? '') ?>">
                        </div>
                        <div class="full">
                            <label for="email">E-mail (facultatif)</label>
                            <input type="email" id="email" name="email" value="<?= e($_POST['email'] ?? '') ?>">
                        </div>
                        <div class="full">
                            <label for="notes">Précisions</label>
                            <textarea id="notes" name="notes"><?= e($_POST['notes'] ?? '') ?></textarea>
                        </div>
                        <div class="full">
                            <button type="submit" class="btn btn--primary">Confirmer ma demande de réservation</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
