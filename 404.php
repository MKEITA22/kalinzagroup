<?php
http_response_code(404);
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Page introuvable — Kalinza Group';
$pageDescription = "La page que vous recherchez n'existe pas.";

require_once __DIR__ . '/includes/header.php';
?>
<section class="divisions">
    <div class="container" style="text-align:center; padding:60px 0;">
        <h1>404 — Page introuvable</h1>
        <p style="margin:0 auto 24px;">La page que vous cherchez n'existe pas ou a été déplacée.</p>
        <a href="/index.php" class="btn btn--primary">Retour à l'accueil</a>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
