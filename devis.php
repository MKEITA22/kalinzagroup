<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Demander un devis — Kalinza Group';
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Honeypot anti-spam : ce champ doit rester vide
    if (!empty($_POST['website'])) {
        $success = true; // on fait croire à un succès sans rien enregistrer
    } elseif (!csrf_check($_POST['csrf_token'] ?? '')) {
        $errors[] = "Votre session a expiré, merci de renvoyer le formulaire.";
    } else {
        $fullName    = trim($_POST['full_name'] ?? '');
        $company     = trim($_POST['company'] ?? '');
        $phone       = trim($_POST['phone'] ?? '');
        $email       = trim($_POST['email'] ?? '');
        $division    = $_POST['division'] ?? '';
        $serviceType = trim($_POST['service_type'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $quantity    = trim($_POST['quantity'] ?? '');
        $dimensions  = trim($_POST['dimensions'] ?? '');
        $desiredDate = trim($_POST['desired_date'] ?? '');

        if ($fullName === '') $errors[] = "Le nom et prénom sont obligatoires.";
        if ($phone === '') $errors[] = "Le numéro de téléphone / WhatsApp est obligatoire.";
        if (!in_array($division, ['imprimerie', 'prod', 'digital'], true)) $errors[] = "Merci de préciser la division concernée.";
        if ($description === '') $errors[] = "Merci de décrire votre besoin.";
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "L'adresse e-mail n'est pas valide.";

        $attachmentPath = null;
        if (!empty($_FILES['attachment']['name'])) {
            $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'ai', 'psd'];
            $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed, true)) {
                $errors[] = "Format de fichier non autorisé.";
            } elseif ($_FILES['attachment']['size'] > 15 * 1024 * 1024) {
                $errors[] = "Le fichier joint dépasse 15 Mo.";
            } else {
                $uploadDir = __DIR__ . '/uploads/devis/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $filename = uniqid('devis_', true) . '.' . $ext;
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadDir . $filename)) {
                    $attachmentPath = 'uploads/devis/' . $filename;
                }
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare(
                "INSERT INTO quote_requests
                 (full_name, company, phone, email, division, service_type, description, quantity, dimensions, desired_date, attachment_path)
                 VALUES (:full_name, :company, :phone, :email, :division, :service_type, :description, :quantity, :dimensions, :desired_date, :attachment_path)"
            );
            $stmt->execute([
                'full_name'      => $fullName,
                'company'        => $company ?: null,
                'phone'          => $phone,
                'email'          => $email ?: null,
                'division'       => $division,
                'service_type'   => $serviceType ?: null,
                'description'    => $description,
                'quantity'       => $quantity ?: null,
                'dimensions'     => $dimensions ?: null,
                'desired_date'   => $desiredDate ?: null,
                'attachment_path'=> $attachmentPath,
            ]);

            // TODO : notifier l'administrateur par e-mail (mail() ou PHPMailer/SMTP Hostinger)
            $success = true;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="divisions">
    <div class="container" style="max-width:760px;">
        <h1>Demander un devis</h1>
        <p>Décrivez votre projet, nous revenons vers vous avec une proposition adaptée.</p>

        <?php if ($success): ?>
            <div class="service-card" style="border-left:4px solid var(--kg-green); margin-top:24px;">
                <h3>Merci, votre demande a bien été envoyée.</h3>
                <p>Notre équipe vous recontactera rapidement au numéro indiqué.</p>
            </div>
        <?php else: ?>

            <?php if (!empty($errors)): ?>
                <div class="service-card" style="border-left:4px solid var(--kg-orange); margin-top:24px;">
                    <ul style="margin:0; padding-left:18px; color:var(--kg-ink);">
                        <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" class="form-grid" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <!-- Honeypot anti-spam, laissé vide -->
                <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">

                <div>
                    <label for="full_name">Nom et prénom *</label>
                    <input type="text" id="full_name" name="full_name" required value="<?= e($_POST['full_name'] ?? '') ?>">
                </div>
                <div>
                    <label for="company">Entreprise (facultatif)</label>
                    <input type="text" id="company" name="company" value="<?= e($_POST['company'] ?? '') ?>">
                </div>
                <div>
                    <label for="phone">Téléphone / WhatsApp *</label>
                    <input type="tel" id="phone" name="phone" required value="<?= e($_POST['phone'] ?? '') ?>">
                </div>
                <div>
                    <label for="email">E-mail (facultatif)</label>
                    <input type="email" id="email" name="email" value="<?= e($_POST['email'] ?? '') ?>">
                </div>
                <div>
                    <label for="division">Division concernée *</label>
                    <select id="division" name="division" required>
                        <option value="">Choisir…</option>
                        <option value="imprimerie">Kalinza Imprimerie</option>
                        <option value="prod">Kalinza Prod</option>
                        <option value="digital">Kalinza Digital</option>
                    </select>
                </div>
                <div>
                    <label for="service_type">Type de prestation</label>
                    <input type="text" id="service_type" name="service_type" placeholder="Ex : flyers A5, portrait corporate..." value="<?= e($_POST['service_type'] ?? '') ?>">
                </div>
                <div>
                    <label for="quantity">Quantité</label>
                    <input type="text" id="quantity" name="quantity" value="<?= e($_POST['quantity'] ?? '') ?>">
                </div>
                <div>
                    <label for="dimensions">Dimensions / format</label>
                    <input type="text" id="dimensions" name="dimensions" value="<?= e($_POST['dimensions'] ?? '') ?>">
                </div>
                <div>
                    <label for="desired_date">Date souhaitée</label>
                    <input type="date" id="desired_date" name="desired_date" value="<?= e($_POST['desired_date'] ?? '') ?>">
                </div>
                <div>
                    <label for="attachment">Fichier joint (facultatif)</label>
                    <input type="file" id="attachment" name="attachment">
                </div>
                <div class="full">
                    <label for="description">Description du besoin *</label>
                    <textarea id="description" name="description" required><?= e($_POST['description'] ?? '') ?></textarea>
                </div>
                <div class="full">
                    <button type="submit" class="btn btn--primary">Envoyer ma demande</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
