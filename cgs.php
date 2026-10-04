<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Conditions générales de service — Kalinza Group';
$pageDescription = "Conditions générales de service applicables aux prestations Kalinza Imprimerie et Kalinza Prod.";

require_once __DIR__ . '/includes/header.php';
?>

<section class="divisions">
    <div class="container" style="max-width:820px;">
        <h1>Conditions générales de service</h1>

        <h3>1. Objet</h3>
        <p>Les présentes conditions régissent les prestations proposées par Kalinza Group à travers ses divisions Kalinza Imprimerie et Kalinza Prod.</p>

        <h3>2. Devis et commande</h3>
        <p>Toute prestation fait l'objet d'un devis préalable établi sur la base des informations transmises par le client. La commande est considérée comme confirmée après accord du client sur le devis.</p>

        <h3>3. Réservation de séances</h3>
        <p>Une demande de réservation n'est confirmée qu'après validation explicite par Kalinza Group, en fonction des disponibilités réelles. Un statut (en attente, confirmée, reportée, annulée) est communiqué au client.</p>

        <h3>4. Délais</h3>
        <p>Les délais de réalisation sont communiqués lors de l'établissement du devis et peuvent varier selon la nature et la complexité de la prestation.</p>

        <h3>5. Paiement</h3>
        <p>Les modalités de paiement sont convenues directement avec le client lors de la validation du devis ou de la réservation ; ce site ne propose pas de paiement en ligne.</p>

        <h3>6. Annulation</h3>
        <p>Toute annulation ou report doit être signalé dans les meilleurs délais afin de permettre une réorganisation.</p>

        <h3>7. Contact</h3>
        <p>Pour toute question relative à ces conditions, vous pouvez nous contacter via la page <a href="/contact.php" style="color:var(--kg-orange); font-weight:600;">Contact</a>.</p>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
