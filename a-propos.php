<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'À propos — Kalinza Group';
$pageDescription = "Découvrez Kalinza Group, ses deux divisions Kalinza Imprimerie et Kalinza Prod, sa vision et ses valeurs.";

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero" style="padding:70px 0 90px;">
    <div class="hero__shapes" aria-hidden="true"></div>
    <div class="container hero__inner">
        <span class="hero__eyebrow">À propos</span>
        <h1>Un groupe, deux expertises au service de votre image</h1>
        <p>Kalinza Group réunit l'impression et la communication visuelle d'un côté, la photo et la vidéo de l'autre, pour accompagner particuliers et entreprises de bout en bout.</p>
    </div>
</section>

<section class="divisions">
    <div class="container">
        <h2>Notre histoire &amp; notre vision</h2>
        <p>Kalinza Group est né de la volonté de réunir sous une même exigence de qualité deux métiers complémentaires : l'imprimerie et la production audiovisuelle. Notre vision : permettre à chaque client de disposer, au même endroit, de tout ce qui construit son image — du support imprimé au contenu photo et vidéo.</p>
    </div>
</section>

<section class="section--alt">
    <div class="container">
        <h2>Nos missions &amp; nos valeurs</h2>
        <div class="why-grid">
            <div class="why-item"><span class="num">01</span><h4>Qualité</h4><p>Un souci du détail à chaque étape, de la conception à la livraison.</p></div>
            <div class="why-item"><span class="num">02</span><h4>Proximité</h4><p>Un accompagnement personnalisé pour chaque projet, petit ou grand.</p></div>
            <div class="why-item"><span class="num">03</span><h4>Fiabilité</h4><p>Des délais respectés et une communication transparente.</p></div>
            <div class="why-item"><span class="num">04</span><h4>Créativité</h4><p>Des solutions visuelles pensées pour marquer les esprits.</p></div>
        </div>
    </div>
</section>

<section class="divisions">
    <div class="container">
        <h2>Nos trois divisions</h2>
        <div class="divisions__grid">
            <div class="division-card" data-reveal>
                <span class="division-card__icon"><?= service_icon('flyer') ?></span>
                <h3>Kalinza Imprimerie</h3>
                <p>Cartes de visite, flyers, affiches, banderoles, brochures, supports de communication et conception graphique.</p>
                <a href="/imprimerie.php" class="btn btn--primary">Découvrir</a>
            </div>
            <div class="division-card division-card--prod" data-reveal style="--delay:0.1s">
                <span class="division-card__icon"><?= service_icon('camera') ?></span>
                <h3>Kalinza Prod</h3>
                <p>Studio photo, portraits, événements, corporate, production vidéo et contenus pour les réseaux sociaux.</p>
                <a href="/prod.php" class="btn btn--secondary">Découvrir</a>
            </div>
            <div class="division-card division-card--digital" data-reveal style="--delay:0.2s">
                <span class="division-card__icon"><?= service_icon('code') ?></span>
                <h3>Kalinza Digital</h3>
                <p>Sites web, plateformes sur mesure et applications mobiles Android/iOS.</p>
                <a href="/digital.php" class="btn" style="background:var(--kg-ink); color:#fff;">Découvrir</a>
            </div>
        </div>
    </div>
</section>

<section class="section--alt">
    <div class="container">
        <h2>Notre équipe</h2>
        <p>Présentation de l'équipe à compléter avec les photos et profils réels des membres de Kalinza Group.</p>
    </div>
</section>

<section class="cta-band">
    <div class="container">
        <h2>Envie de travailler avec nous ?</h2>
        <div class="cta-band__actions">
            <a href="/devis.php" class="btn btn--primary">Demander un devis</a>
            <a href="/contact.php" class="btn btn--ghost">Nous contacter</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
