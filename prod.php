<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Kalinza Prod — Studio photo & production vidéo';
$pageDescription = "Portraits, événements, corporate, production vidéo et contenus réseaux sociaux : découvrez Kalinza Prod.";

$services = $pdo->query(
    "SELECT * FROM services WHERE division = 'prod' AND is_visible = 1 ORDER BY sort_order"
)->fetchAll();

$defaultServices = [
    ['icon' => 'camera', 'title' => 'Portraits professionnels', 'description' => 'Photos corporate et portraits individuels en studio ou en extérieur.'],
    ['icon' => 'family', 'title' => 'Photos de famille & événements', 'description' => 'Immortalisez vos moments importants avec un regard professionnel.'],
    ['icon' => 'building', 'title' => 'Reportage corporate', 'description' => 'Vie d\'entreprise, équipes, locaux et événements professionnels.'],
    ['icon' => 'video', 'title' => 'Production vidéo', 'description' => 'Films de marque, interviews, reportages et contenus institutionnels.'],
    ['icon' => 'social', 'title' => 'Contenus réseaux sociaux', 'description' => 'Formats courts et visuels pensés pour Instagram, TikTok et Facebook.'],
];

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero" style="padding:70px 0 90px;">
    <div class="hero__shapes" aria-hidden="true"></div>
    <div class="container hero__inner">
        <span class="hero__eyebrow">Kalinza Prod</span>
        <h1>Nous racontons votre histoire en images</h1>
        <p>Studio photo, portraits, événements, corporate et production vidéo : des contenus qui marquent.</p>
        <div class="hero__actions">
            <a href="/reservation.php" class="btn btn--secondary">Réserver une séance</a>
        </div>
    </div>
</section>

<section class="divisions">
    <div class="container">
        <h2 data-reveal>Nos prestations</h2>
        <div class="services-grid">
            <?php if (!empty($services)): ?>
                <?php foreach ($services as $i => $s): ?>
                    <div class="service-card" data-reveal style="--delay: <?= $i * 0.06 ?>s">
                        <div class="service-card__image">
                            <?php if (!empty($s['cover_image'])): ?>
                                <img src="<?= e($s['cover_image']) ?>" alt="<?= e($s['title']) ?>" loading="lazy">
                            <?php else: ?>
                                <span class="service-card__icon"><?= service_icon('camera') ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="service-card__body">
                            <h4><?= e($s['title']) ?></h4>
                            <p><?= e($s['description']) ?></p>
                            <div class="service-card__meta">
                                <span class="meta-badge"><?= service_icon('quantity') ?><?= e($s['min_quantity'] ?: 'Sur devis') ?></span>
                                <span class="meta-badge"><?= service_icon('clock') ?><?= e($s['delivery_time'] ?: 'Sur devis') ?></span>
                            </div>
                            <div class="service-card__actions">
                                <a href="/reservation.php?service=<?= urlencode($s['slug']) ?>" class="btn btn--secondary">Réserver</a>
                                <a href="<?= e(whatsapp_product_link($pdo, $s['title'])) ?>" class="btn btn--whatsapp" target="_blank" rel="noopener"><?= service_icon('whatsapp') ?> WhatsApp</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php foreach ($defaultServices as $i => $s): ?>
                    <div class="service-card" data-reveal style="--delay: <?= $i * 0.06 ?>s">
                        <div class="service-card__image">
                            <span class="service-card__icon"><?= service_icon($s['icon']) ?></span>
                        </div>
                        <div class="service-card__body">
                            <h4><?= e($s['title']) ?></h4>
                            <p><?= e($s['description']) ?></p>
                            <div class="service-card__meta">
                                <span class="meta-badge"><?= service_icon('quantity') ?>Sur devis</span>
                                <span class="meta-badge"><?= service_icon('clock') ?>Sur devis</span>
                            </div>
                            <div class="service-card__actions">
                                <a href="/reservation.php" class="btn btn--secondary">Réserver</a>
                                <a href="<?= e(whatsapp_product_link($pdo, $s['title'])) ?>" class="btn btn--whatsapp" target="_blank" rel="noopener"><?= service_icon('whatsapp') ?> WhatsApp</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <p style="margin-top:10px; font-size:0.9rem;">Les tarifs seront affichés dès leur confirmation par Kalinza Group.</p>
    </div>
</section>

<section class="cta-band">
    <div class="container">
        <h2>Envie d'une séance photo ou vidéo ?</h2>
        <div class="cta-band__actions">
            <a href="/reservation.php" class="btn btn--primary">Réserver une séance</a>
            <a href="<?= e($whatsapp) ?>" class="btn btn--ghost" target="_blank" rel="noopener">Écrire sur WhatsApp</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
