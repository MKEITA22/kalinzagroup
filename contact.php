<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Contact — Kalinza Group';
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['website'])) {
        $success = true;
    } elseif (!csrf_check($_POST['csrf_token'] ?? '')) {
        $errors[] = "Votre session a expiré, merci de renvoyer le formulaire.";
    } else {
        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $message  = trim($_POST['message'] ?? '');

        if ($fullName === '') $errors[] = "Le nom et prénom sont obligatoires.";
        if ($message === '') $errors[] = "Merci de rédiger votre message.";
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "L'adresse e-mail n'est pas valide.";

        if (empty($errors)) {
            $stmt = $pdo->prepare(
                "INSERT INTO contact_messages (full_name, email, phone, message) VALUES (:full_name, :email, :phone, :message)"
            );
            $stmt->execute([
                'full_name' => $fullName,
                'email'     => $email ?: null,
                'phone'     => $phone ?: null,
                'message'   => $message,
            ]);
            $success = true;
        }
    }
}

$phoneNumber = get_setting($pdo, 'phone_primary', 'À compléter');
$emailAddr   = get_setting($pdo, 'email_contact', 'À compléter');
$address     = get_setting($pdo, 'address', 'Adresse à compléter');

require_once __DIR__ . '/includes/header.php';
?>

<section class="divisions">
    <div class="container">
        <h1>Contactez-nous</h1>
        <div class="divisions__grid" style="align-items:start;">
            <div>
                <h3>Nos coordonnées</h3>
                <p><strong>Téléphone :</strong> <?= e($phoneNumber) ?></p>
                <p><strong>E-mail :</strong> <?= e($emailAddr) ?></p>
                <p><strong>Adresse :</strong> <?= e($address) ?></p>
                <a href="<?= e($whatsapp) ?>" class="btn btn--secondary" target="_blank" rel="noopener">Écrire sur WhatsApp</a>

                <!-- Carte Google Maps à activer dès que l'adresse est confirmée :
                <iframe src="https://www.google.com/maps/embed?..." width="100%" height="260" style="border:0; border-radius:10px; margin-top:20px;" loading="lazy"></iframe>
                -->
            </div>
            <div>
                <?php if ($success): ?>
                    <div class="service-card" style="border-left:4px solid var(--kg-green);">
                        <h3>Merci, votre message a bien été envoyé.</h3>
                        <p>Nous vous répondrons dans les meilleurs délais.</p>
                    </div>
                <?php else: ?>
                    <?php if (!empty($errors)): ?>
                        <div class="service-card" style="border-left:4px solid var(--kg-orange); margin-bottom:20px;">
                            <ul style="margin:0; padding-left:18px;">
                                <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <form method="post" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">

                        <label for="full_name">Nom et prénom *</label>
                        <input type="text" id="full_name" name="full_name" required style="margin-bottom:16px;" value="<?= e($_POST['full_name'] ?? '') ?>">

                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" style="margin-bottom:16px;" value="<?= e($_POST['email'] ?? '') ?>">

                        <label for="phone">Téléphone</label>
                        <input type="tel" id="phone" name="phone" style="margin-bottom:16px;" value="<?= e($_POST['phone'] ?? '') ?>">

                        <label for="message">Message *</label>
                        <textarea id="message" name="message" required style="margin-bottom:16px;"><?= e($_POST['message'] ?? '') ?></textarea>

                        <button type="submit" class="btn btn--primary">Envoyer</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div style="margin-top:40px;">
            <h3>Réseaux sociaux</h3>
            <p>
                <?php $fb = get_setting($pdo, 'facebook_url'); $ig = get_setting($pdo, 'instagram_url'); ?>
                <?php if ($fb): ?><a href="<?= e($fb) ?>" target="_blank" rel="noopener">Facebook</a> &nbsp;·&nbsp; <?php endif; ?>
                <?php if ($ig): ?><a href="<?= e($ig) ?>" target="_blank" rel="noopener">Instagram</a><?php endif; ?>
                <?php if (!$fb && !$ig): ?>Liens à venir.<?php endif; ?>
            </p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
