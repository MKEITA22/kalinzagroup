<?php
/**
 * Création du tout premier compte administrateur.
 * À UTILISER UNE SEULE FOIS, PUIS À SUPPRIMER DU SERVEUR IMMÉDIATEMENT APRÈS USAGE.
 *
 * Avant d'uploader ce fichier sur Hostinger, changez la valeur de SETUP_KEY
 * ci-dessous pour une chaîne secrète connue de vous seul, puis ouvrez :
 * https://kalinzagroup.com/admin/setup.php?key=VOTRE_CLE_SECRETE
 */

define('SETUP_KEY', 'monmotdepasseultrasecret'); // Changez cette valeur avant d'uploader le fichier sur le serveur.

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_GET['key']) || !hash_equals(SETUP_KEY, $_GET['key'])) {
    http_response_code(403);
    die('Accès refusé. Ajoutez ?key=VOTRE_CLE_SECRETE à l\'URL.');
}

// Sécurité : si un administrateur existe déjà, on bloque toute création supplémentaire via ce script.
$existing = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
if ($existing > 0) {
    die('Un compte existe déjà dans la table "users". Pour des raisons de sécurité, ce script refuse d\'en créer un autre. Supprimez ce fichier (admin/setup.php) du serveur.');
}

$errors = [];
$done = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if ($name === '') $errors[] = "Le nom est obligatoire.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "E-mail invalide.";
    if (strlen($password) < 8) $errors[] = "Le mot de passe doit faire au moins 8 caractères.";
    if ($password !== $confirm) $errors[] = "Les deux mots de passe ne correspondent pas.";

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "INSERT INTO users (name, email, password_hash, role, is_active) VALUES (:name, :email, :hash, 'admin', 1)"
        );
        $stmt->execute([
            'name'  => $name,
            'email' => $email,
            'hash'  => password_hash($password, PASSWORD_DEFAULT),
        ]);
        $done = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Création du compte admin — Kalinza Group</title>
<meta name="robots" content="noindex, nofollow">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
  body { display:flex; align-items:center; justify-content:center; min-height:100vh; background:var(--kg-ink); }
  .box { background:var(--kg-white); padding:40px; border-radius:var(--radius); width:100%; max-width:420px; }
  .warn { background:#FFF4E5; border-left:4px solid var(--kg-orange); padding:12px 16px; font-size:0.85rem; margin-bottom:20px; border-radius:6px; }
</style>
</head>
<body>
<div class="box">
    <h1 style="font-size:1.3rem;">Créer le compte administrateur</h1>

    <?php if ($done): ?>
        <div class="warn" style="border-left-color:var(--kg-green);">
            <strong>Compte créé avec succès.</strong><br>
            Connectez-vous maintenant sur <a href="login.php">admin/login.php</a>.<br><br>
            <strong>Supprimez ce fichier (admin/setup.php) du serveur dès maintenant</strong> — via le Gestionnaire de fichiers Hostinger.
        </div>
    <?php else: ?>
        <div class="warn">À supprimer du serveur juste après utilisation.</div>
        <?php if (!empty($errors)): ?>
            <p style="color:var(--kg-orange-dark);"><?= e(implode(' ', $errors)) ?></p>
        <?php endif; ?>
        <form method="post">
            <label for="name">Nom</label>
            <input type="text" id="name" name="name" required style="margin-bottom:14px;" value="<?= e($_POST['name'] ?? '') ?>">
            <label for="email">E-mail (identifiant de connexion)</label>
            <input type="email" id="email" name="email" required style="margin-bottom:14px;" value="<?= e($_POST['email'] ?? '') ?>">
            <label for="password">Mot de passe (8 caractères min.)</label>
            <input type="password" id="password" name="password" required style="margin-bottom:14px;">
            <label for="confirm">Confirmer le mot de passe</label>
            <input type="password" id="confirm" name="confirm" required style="margin-bottom:20px;">
            <button type="submit" class="btn btn--primary" style="width:100%; justify-content:center;">Créer le compte</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
