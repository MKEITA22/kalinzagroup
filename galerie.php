<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Galerie — Réalisations Kalinza Group';
$pageDescription = "Découvrez nos réalisations en photographie, vidéo, imprimerie et design graphique.";

$allowedCategories = ['photo', 'video', 'imprimerie', 'design'];
$filter = $_GET['categorie'] ?? '';
if (!in_array($filter, $allowedCategories, true)) {
    $filter = '';
}

if ($filter !== '') {
    $stmt = $pdo->prepare("SELECT * FROM portfolio_items WHERE category = :cat ORDER BY sort_order DESC, created_at DESC");
    $stmt->execute(['cat' => $filter]);
} else {
    $stmt = $pdo->query("SELECT * FROM portfolio_items ORDER BY sort_order DESC, created_at DESC");
}
$items = $stmt->fetchAll();

$labels = ['photo' => 'Photo', 'video' => 'Vidéo', 'imprimerie' => 'Imprimerie', 'design' => 'Design'];

require_once __DIR__ . '/includes/header.php';
?>

<section class="divisions">
    <div class="container">
        <h1>Notre galerie</h1>
        <p>Un aperçu de nos réalisations récentes en photographie, vidéo, imprimerie et design graphique.</p>

        <div class="hero__actions" style="margin:24px 0 0;">
            <a href="/galerie.php" class="btn <?= $filter === '' ? 'btn--primary' : 'btn--ghost' ?>">Tout</a>
            <?php foreach ($labels as $key => $label): ?>
                <a href="/galerie.php?categorie=<?= e($key) ?>" class="btn <?= $filter === $key ? 'btn--primary' : 'btn--ghost' ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($items)): ?>
            <div class="portfolio-grid">
                <?php foreach ($items as $item): ?>
                    <div class="portfolio-item">
                        <img src="<?= e($item['thumbnail_url'] ?: $item['media_url']) ?>" alt="<?= e($item['title']) ?>" loading="lazy">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="margin-top:30px;">Les premières réalisations seront ajoutées prochainement via l'espace d'administration.</p>
        <?php endif; ?>
    </div>
</section>

<section class="cta-band">
    <div class="container">
        <h2>Votre projet mérite le même soin</h2>
        <div class="cta-band__actions">
            <a href="/devis.php" class="btn btn--primary">Demander un devis</a>
            <a href="/reservation.php" class="btn btn--ghost">Réserver une séance</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
