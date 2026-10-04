<?php
$current = basename($_SERVER['SCRIPT_NAME'], '.php');
$whatsapp = whatsapp_link(get_setting($pdo, 'whatsapp_number', '2250000000000'), 'Bonjour Kalinza Group, je souhaite plus d\'informations.');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? get_setting($pdo, 'meta_title_default')) ?></title>
<meta name="description" content="<?= e($pageDescription ?? get_setting($pdo, 'meta_description_default')) ?>">
<link rel="icon" href="/assets/images/logo.jpg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="container site-header__inner">
        <a href="/index.php" class="brand">
            <img src="/assets/images/logo.jpg" alt="Kalinza Group" class="brand__logo">
        </a>
        <nav class="nav" aria-label="Navigation principale">
            <button class="nav__toggle" aria-expanded="false" aria-controls="nav-menu" aria-label="Ouvrir le menu">
                <span></span><span></span><span></span>
            </button>
            <ul id="nav-menu" class="nav__menu">
                <li><a href="/index.php" class="<?= $current === 'index' ? 'is-active' : '' ?>">Accueil</a></li>
                <li><a href="/a-propos.php" class="<?= $current === 'a-propos' ? 'is-active' : '' ?>">À propos</a></li>
                <li><a href="/imprimerie.php" class="<?= $current === 'imprimerie' ? 'is-active' : '' ?>">Imprimerie</a></li>
                <li><a href="/prod.php" class="<?= $current === 'prod' ? 'is-active' : '' ?>">Prod</a></li>
                <li><a href="/digital.php" class="<?= $current === 'digital' ? 'is-active' : '' ?>">Digital</a></li>
                <li><a href="/galerie.php" class="<?= $current === 'galerie' ? 'is-active' : '' ?>">Galerie</a></li>
                <li><a href="/contact.php" class="<?= $current === 'contact' ? 'is-active' : '' ?>">Contact</a></li>
            </ul>
        </nav>
        <div class="site-header__cta">
            <a href="<?= e($whatsapp) ?>" class="btn btn--ghost" target="_blank" rel="noopener">WhatsApp</a>
            <a href="/devis.php" class="btn btn--primary">Demander un devis</a>
        </div>
    </div>
</header>
