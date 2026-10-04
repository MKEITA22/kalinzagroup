<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Kalinza Group — Imprimerie, Studio Photo/Vidéo & Digital à Abidjan';
$pageDescription = get_setting($pdo, 'meta_description_default');

$featuredWorks = $pdo->query(
    "SELECT * FROM portfolio_items WHERE is_featured = 1 ORDER BY sort_order LIMIT 6"
)->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="hero__shapes" aria-hidden="true"></div>
    <div class="container hero__inner">
        <span class="hero__eyebrow">Kalinza Imprimerie · Kalinza Prod · Kalinza Digital</span>
        <h1>Votre image, notre expertise.</h1>
        <p>Un seul groupe, trois savoir-faire complémentaires : l'impression et la communication visuelle, la photo et la vidéo, le développement web et mobile. Nous donnons vie à votre image, sous toutes ses formes.</p>
        <div class="hero__actions">
            <a href="/devis.php" class="btn btn--primary">Demander un devis</a>
            <a href="/reservation.php" class="btn btn--ghost">Réserver une séance</a>
        </div>
    </div>
</section>

<section class="divisions">
    <div class="container">
        <h2 data-reveal>Trois divisions, une seule exigence</h2>
        <div class="divisions__grid">
            <div class="division-card" data-reveal>
                <span class="division-card__icon"><?= service_icon('flyer') ?></span>
                <h3>Kalinza Imprimerie</h3>
                <p>Cartes de visite, flyers, affiches, banderoles, brochures et conception graphique : tous vos supports de communication, pensés et imprimés avec précision.</p>
                <a href="/imprimerie.php" class="btn btn--primary">Découvrir l'imprimerie</a>
            </div>
            <div class="division-card division-card--prod" data-reveal style="--delay:0.1s">
                <span class="division-card__icon"><?= service_icon('camera') ?></span>
                <h3>Kalinza Prod</h3>
                <p>Studio photo, portraits, événements, corporate, production vidéo et contenus pour les réseaux sociaux : nous racontons votre histoire en images.</p>
                <a href="/prod.php" class="btn btn--secondary">Découvrir Kalinza Prod</a>
            </div>
            <div class="division-card division-card--digital" data-reveal style="--delay:0.2s">
                <span class="division-card__icon"><?= service_icon('code') ?></span>
                <h3>Kalinza Digital</h3>
                <p>Sites web, plateformes sur mesure et applications mobiles Android/iOS : des outils numériques fiables et bien pensés.</p>
                <a href="/digital.php" class="btn" style="background:var(--kg-ink); color:#fff;">Découvrir Kalinza Digital</a>
            </div>
        </div>
    </div>
</section>

<section class="section--alt">
    <div class="container">
        <h2 data-reveal>Nos services phares</h2>
        <div class="services-grid">
            <div class="service-card" data-reveal><div class="service-card__body"><span class="service-card__icon"><?= service_icon('card') ?></span><h4>Cartes de visite</h4><p>Impression premium, finitions soignées.</p></div></div>
            <div class="service-card" data-reveal style="--delay:0.05s"><div class="service-card__body"><span class="service-card__icon"><?= service_icon('flyer') ?></span><h4>Flyers &amp; affiches</h4><p>Pour vos campagnes et événements.</p></div></div>
            <div class="service-card" data-reveal style="--delay:0.1s"><div class="service-card__body"><span class="service-card__icon"><?= service_icon('banner') ?></span><h4>Banderoles &amp; bâches</h4><p>Grand format, visibilité maximale.</p></div></div>
            <div class="service-card" data-reveal style="--delay:0.15s"><div class="service-card__body"><span class="service-card__icon"><?= service_icon('camera') ?></span><h4>Studio photo</h4><p>Portraits, corporate, famille.</p></div></div>
            <div class="service-card" data-reveal style="--delay:0.2s"><div class="service-card__body"><span class="service-card__icon"><?= service_icon('video') ?></span><h4>Production vidéo</h4><p>Contenus de marque et réseaux sociaux.</p></div></div>
            <div class="service-card" data-reveal style="--delay:0.25s"><div class="service-card__body"><span class="service-card__icon"><?= service_icon('phone') ?></span><h4>Sites &amp; applications</h4><p>Développement web et mobile sur mesure.</p></div></div>
        </div>
    </div>
</section>

<?php if (!empty($featuredWorks)): ?>
<section class="divisions">
    <div class="container">
        <h2 data-reveal>Réalisations récentes</h2>
        <div class="portfolio-grid">
            <?php foreach ($featuredWorks as $i => $work): ?>
                <div class="portfolio-item" data-reveal style="--delay: <?= $i * 0.05 ?>s">
                    <img src="<?= e($work['thumbnail_url'] ?: $work['media_url']) ?>" alt="<?= e($work['title']) ?>" loading="lazy">
                </div>
            <?php endforeach; ?>
        </div>
        <p style="margin-top:24px;"><a href="/galerie.php" class="btn btn--ghost">Voir toute la galerie</a></p>
    </div>
</section>
<?php endif; ?>

<section class="section--alt">
    <div class="container">
        <h2 data-reveal>Pourquoi choisir Kalinza Group ?</h2>
        <div class="why-grid">
            <div class="why-item" data-reveal><span class="num">01</span><h4>Trois expertises, un seul interlocuteur</h4><p>Imprimerie, audiovisuel et digital réunis pour une image cohérente.</p></div>
            <div class="why-item" data-reveal style="--delay:0.08s"><span class="num">02</span><h4>Qualité et rigueur</h4><p>Un souci du détail à chaque étape de production.</p></div>
            <div class="why-item" data-reveal style="--delay:0.16s"><span class="num">03</span><h4>Réactivité</h4><p>Des délais respectés et un suivi personnalisé de vos demandes.</p></div>
            <div class="why-item" data-reveal style="--delay:0.24s"><span class="num">04</span><h4>Accompagnement sur mesure</h4><p>De la conception à la livraison, nous vous conseillons.</p></div>
        </div>
    </div>
</section>

<section class="cta-band">
    <div class="container" data-reveal>
        <h2>Un projet en tête ?</h2>
        <p style="color:rgba(255,255,255,0.9); margin: 0 auto;">Décrivez-nous votre besoin, nous revenons vers vous rapidement.</p>
        <div class="cta-band__actions">
            <a href="/devis.php" class="btn btn--primary">Demander un devis</a>
            <a href="/reservation.php" class="btn btn--ghost">Réserver une séance</a>
            <a href="<?= e($whatsapp) ?>" class="btn btn--ghost" target="_blank" rel="noopener">Écrire sur WhatsApp</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
