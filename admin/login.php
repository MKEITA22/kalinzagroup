<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error = "Session expirée, merci de réessayer.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND is_active = 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']   = $user['id'];
            $_SESSION['admin_name'] = $user['name'];
            $_SESSION['admin_role'] = $user['role'];
            header('Location: index.php');
            exit;
        }
        $error = "Identifiants incorrects.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Connexion — Administration Kalinza Group</title>
<meta name="robots" content="noindex, nofollow">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
  body { display:flex; align-items:center; justify-content:center; min-height:100vh; background:var(--kg-ink); }
  .login-box { background:var(--kg-white); padding:40px; border-radius:var(--radius); width:100%; max-width:380px; }
  .login-box img { height:48px; margin-bottom:20px; }
</style>
</head>
<body>
<div class="login-box">
    <img src="../assets/images/logo.jpg" alt="Kalinza Group">
    <h1 style="font-size:1.4rem;">Administration</h1>
    <?php if ($error): ?><p style="color:var(--kg-orange-dark);"><?= e($error) ?></p><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" required style="margin-bottom:16px;">
        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" required style="margin-bottom:20px;">
        <button type="submit" class="btn btn--primary" style="width:100%; justify-content:center;">Se connecter</button>
    </form>
</div>
</body>
</html>
