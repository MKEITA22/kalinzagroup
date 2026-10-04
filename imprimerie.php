<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Kalinza Imprimerie — Impression & communication visuelle';
$pageDescription = "Cartes de visite, flyers, affiches, banderoles, brochures et conception graphique : les services de Kalinza Imprimerie.";

$services = $pdo->query(
    "SELECT * FROM services WHERE division = 'imprimerie' AND is_visible = 1 ORDER BY sort_order"
)->fetchAll();

// Services par défaut affichés si la table est encore vide (avant remplissage via l'admin)
$defaultServices = [
    ['icon' => 'card', 'title' => 'Cartes de visite', 'description' => 'Impression premium et finitions soignées pour une première impression professionnelle.'],
    ['icon' => 'flyer', 'title' => 'Flyers & affiches', 'description' => 'Supports percutants pour vos campagnes, promotions et événements.'],
    ['icon' => 'banner', 'title' => 'Banderoles & bâches', 'description' => 'Grand format, résistantes, pour une visibilité maximale en extérieur.'],
    ['icon' => 'brochure', 'title' => 'Brochures & dépliants', 'description' => 'Présentez vos produits et services avec des supports pliés soignés.'],
    ['icon' => 'signage', 'title' => 'Supports de communication', 'description' => 'Kakémonos, roll-up, panneaux et signalétique sur mesure.'],
    ['icon' => 'design', 'title' => 'Conception graphique & PAO', 'description' => 'Création de votre identité visuelle et mise en page professionnelle.'],
];

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero" style="padding:70px 0 90px;">
    <div class="hero__shapes" aria-hidden="true"></div>
    <div class="container hero__inner">
        <span class="hero__eyebrow">Kalinza Imprimerie</span>
        <h1>Vos supports imprimés, pensés et réalisés avec précision</h1>
        <p>De la carte de visite à la bâche publicitaire, nous donnons corps à votre communication visuelle.</p>
        <div class="hero__actions">
            <a href="/devis.php" class="btn btn--primary">Demander un devis</a>
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
                                <span class="service-card__icon"><?= service_icon('flyer') ?></span>
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
                                <a href="/devis.php?service=<?= urlencode($s['slug']) ?>" class="btn btn--primary">Demander un devis</a>
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

<section class="cta-band">
    <div class="container">
        <h2>Un projet d'impression à lancer ?</h2>
        <div class="cta-band__actions">
            <a href="/devis.php" class="btn btn--primary">Demander un devis</a>
            <a href="<?= e($whatsapp) ?>" class="btn btn--ghost" target="_blank" rel="noopener">Écrire sur WhatsApp</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
