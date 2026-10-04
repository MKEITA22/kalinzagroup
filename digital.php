<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Kalinza Digital — Développement web & applications mobiles';
$pageDescription = "Sites web, plateformes sur mesure et applications mobiles Android/iOS conçus par Kalinza Digital.";

$services = $pdo->query(
    "SELECT * FROM services WHERE division = 'digital' AND is_visible = 1 ORDER BY sort_order"
)->fetchAll();

$defaultServices = [
    ['icon' => 'code', 'title' => 'Sites web vitrine & institutionnels', 'description' => 'Des sites rapides, responsives et optimisés pour présenter votre activité et convertir vos visiteurs.'],
    ['icon' => 'cart', 'title' => 'Plateformes & applications sur mesure', 'description' => 'E-commerce, espaces de réservation, portails métiers : des solutions pensées pour vos besoins réels.'],
    ['icon' => 'phone', 'title' => 'Applications mobiles Android & iOS', 'description' => 'Des applications natives ou multiplateformes, fluides et pensées pour vos utilisateurs.'],
    ['icon' => 'layers', 'title' => 'Interfaces & expérience utilisateur (UI/UX)', 'description' => 'Des parcours clairs et une identité visuelle cohérente sur tous les écrans.'],
    ['icon' => 'shield', 'title' => 'Maintenance & évolutions', 'description' => 'Suivi, mises à jour de sécurité et nouvelles fonctionnalités après la mise en ligne.'],
    ['icon' => 'link', 'title' => 'Intégrations & API', 'description' => 'Paiement, messagerie, CRM : nous connectons vos outils entre eux.'],
];
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<section class="hero hero--digital">
    <div class="hero__shapes" aria-hidden="true"></div>
    <div class="container hero__inner" data-reveal>
        <span class="hero__eyebrow">Kalinza Digital</span>
        <h1>Vos idées, transformées en sites et applications</h1>
        <p>Du site vitrine à l'application mobile sur mesure, Kalinza Digital conçoit des outils numériques fiables, rapides et pensés pour vos utilisateurs.</p>
        <div class="hero__actions">
            <a href="/devis.php" class="btn btn--primary">Demander un devis</a>
            <a href="<?= e($whatsapp) ?>" class="btn btn--ghost" target="_blank" rel="noopener">Discuter de mon projet</a>
        </div>
    </div>
</section>

<section class="divisions">
    <div class="container">
        <h2 data-reveal>Nos prestations</h2>
        <div class="services-grid">
            <?php if (!empty($services)): ?>
                <?php foreach ($services as $i => $s): ?>
                    <div class="service-card" data-reveal style="--delay: <?= $i * 0.08 ?>s">
                        <div class="service-card__image">
                            <?php if (!empty($s['cover_image'])): ?>
                                <img src="<?= e($s['cover_image']) ?>" alt="<?= e($s['title']) ?>" loading="lazy">
                            <?php else: ?>
                                <span class="service-card__icon"><?= service_icon('code') ?></span>
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
                                <a href="/devis.php" class="btn btn--primary">Demander un devis</a>
                                <a href="<?= e(whatsapp_product_link($pdo, $s['title'])) ?>" class="btn btn--whatsapp" target="_blank" rel="noopener"><?= service_icon('whatsapp') ?> WhatsApp</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php foreach ($defaultServices as $i => $s): ?>
                    <div class="service-card" data-reveal style="--delay: <?= $i * 0.08 ?>s">
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
                                <a href="/devis.php" class="btn btn--primary">Demander un devis</a>
                                <a href="<?= e(whatsapp_product_link($pdo, $s['title'])) ?>" class="btn btn--whatsapp" target="_blank" rel="noopener"><?= service_icon('whatsapp') ?> WhatsApp</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section--alt">
    <div class="container">
        <h2 data-reveal>Notre méthode</h2>
        <div class="why-grid">
            <div class="why-item" data-reveal><span class="num">01</span><h4>Cadrage</h4><p>Nous clarifions vos objectifs, votre cible et le périmètre du projet.</p></div>
            <div class="why-item" data-reveal style="--delay:0.08s"><span class="num">02</span><h4>Conception</h4><p>Maquettes et architecture technique validées avant le développement.</p></div>
            <div class="why-item" data-reveal style="--delay:0.16s"><span class="num">03</span><h4>Développement</h4><p>Un code propre, documenté et testé, livré par étapes.</p></div>
            <div class="why-item" data-reveal style="--delay:0.24s"><span class="num">04</span><h4>Suivi</h4><p>Mise en ligne, formation et accompagnement dans la durée.</p></div>
        </div>
    </div>
</section>

<section class="cta-band">
    <div class="container" data-reveal>
        <h2>Un site ou une application à créer ?</h2>
        <div class="cta-band__actions">
            <a href="/devis.php" class="btn btn--primary">Demander un devis</a>
            <a href="<?= e($whatsapp) ?>" class="btn btn--ghost" target="_blank" rel="noopener">Écrire sur WhatsApp</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
