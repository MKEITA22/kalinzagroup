<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Politique de confidentialité — Kalinza Group';
$pageDescription = "Comment Kalinza Group collecte et utilise vos données personnelles.";

require_once __DIR__ . '/includes/header.php';
?>

<section class="divisions">
    <div class="container" style="max-width:820px;">
        <h1>Politique de confidentialité</h1>

        <h3>Données collectées</h3>
        <p>Lorsque vous utilisez nos formulaires (demande de devis, réservation de séance, contact), nous collectons les informations que vous nous transmettez volontairement : nom, téléphone, e-mail, nature de votre demande, et le cas échéant un fichier joint.</p>

        <h3>Utilisation des données</h3>
        <p>Ces données sont utilisées exclusivement pour traiter votre demande, vous recontacter et assurer le suivi de votre dossier (devis, réservation, message). Elles ne sont ni vendues, ni transmises à des tiers à des fins commerciales.</p>

        <h3>Conservation</h3>
        <p>Les données sont conservées le temps nécessaire au traitement de votre demande et à la durée légale applicable.</p>

        <h3>Vos droits</h3>
        <p>Vous pouvez demander l'accès, la rectification ou la suppression de vos données en nous contactant à l'adresse indiquée sur la page Contact.</p>

        <h3>Cookies</h3>
        <p>Ce site utilise, le cas échéant, des cookies techniques nécessaires à son bon fonctionnement (session, sécurité des formulaires).</p>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
