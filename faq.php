<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'FAQ — Kalinza Group';
$pageDescription = "Réponses aux questions fréquentes sur les services de Kalinza Imprimerie et Kalinza Prod.";

$faqs = [
    ['q' => "Comment demander un devis ?", 'a' => "Remplissez le formulaire de la page « Demander un devis » en précisant la division concernée et votre besoin. Nous revenons vers vous rapidement."],
    ['q' => "Quels sont les délais de réalisation ?", 'a' => "Les délais varient selon la prestation. Ils vous seront communiqués avec votre devis."],
    ['q' => "Comment réserver une séance photo ou vidéo ?", 'a' => "Utilisez le formulaire « Réserver une séance ». Votre créneau est confirmé par notre équipe après vérification des disponibilités."],
    ['q' => "Proposez-vous le paiement en ligne ?", 'a' => "Non, les devis et réservations sont traités manuellement par notre équipe, qui vous recontacte directement."],
    ['q' => "Puis-je joindre des fichiers à ma demande de devis ?", 'a' => "Oui, le formulaire de devis permet de joindre un fichier (logo, maquette, exemple visuel)."],
];

require_once __DIR__ . '/includes/header.php';
?>

<section class="divisions">
    <div class="container" style="max-width:820px;">
        <h1>Questions fréquentes</h1>
        <div style="margin-top:30px; display:grid; gap:16px;">
            <?php foreach ($faqs as $item): ?>
                <div class="service-card">
                    <h4><?= e($item['q']) ?></h4>
                    <p><?= e($item['a']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <p style="margin-top:30px;">Une autre question ? <a href="/contact.php" style="color:var(--kg-orange); font-weight:600;">Contactez-nous</a>.</p>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
