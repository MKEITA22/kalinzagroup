<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Mentions légales — Kalinza Group';
$pageDescription = "Mentions légales du site Kalinza Group.";

require_once __DIR__ . '/includes/header.php';
?>

<section class="divisions">
    <div class="container" style="max-width:820px;">
        <h1>Mentions légales</h1>

        <h3>Éditeur du site</h3>
        <p>Kalinza Group — [Forme juridique à compléter], immatriculée sous le numéro [RCCM à compléter], dont le siège social est situé [adresse à compléter], Côte d'Ivoire.</p>
        <p>Téléphone : <?= e(get_setting($pdo, 'phone_primary', 'À compléter')) ?><br>
        E-mail : <?= e(get_setting($pdo, 'email_contact', 'À compléter')) ?></p>

        <h3>Directeur de publication</h3>
        <p>[Nom du responsable à compléter]</p>

        <h3>Hébergement</h3>
        <p>Ce site est hébergé par Hostinger. [Coordonnées de l'hébergeur à compléter selon les mentions légales de Hostinger applicables à votre offre.]</p>

        <h3>Propriété intellectuelle</h3>
        <p>L'ensemble des contenus présents sur ce site (textes, images, logo, réalisations) est la propriété de Kalinza Group, sauf mention contraire, et ne peut être reproduit sans autorisation préalable.</p>

        <h3>Données personnelles</h3>
        <p>Les informations transmises via les formulaires (devis, réservation, contact) sont utilisées uniquement pour le traitement de votre demande. Voir notre <a href="/confidentialite.php" style="color:var(--kg-orange); font-weight:600;">Politique de confidentialité</a>.</p>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
