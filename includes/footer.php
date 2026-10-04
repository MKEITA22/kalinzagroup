    <footer class="site-footer">
        <div class="container site-footer__grid">
            <div class="site-footer__brand">
                <img src="/assets/images/logo.jpg" alt="Kalinza Group" class="site-footer__logo">
                <p>Kalinza Imprimerie, Kalinza Prod &amp; Kalinza Digital — votre image, notre expertise.</p>
            </div>
            <div class="site-footer__col">
                <h3>Navigation</h3>
                <ul>
                    <li><a href="/imprimerie.php">Kalinza Imprimerie</a></li>
                    <li><a href="/prod.php">Kalinza Prod</a></li>
                    <li><a href="/digital.php">Kalinza Digital</a></li>
                    <li><a href="/galerie.php">Galerie</a></li>
                    <li><a href="/devis.php">Demander un devis</a></li>
                    <li><a href="/reservation.php">Réserver une séance</a></li>
                </ul>
            </div>
            <div class="site-footer__col">
                <h3>Informations</h3>
                <ul>
                    <li><a href="/faq.php">FAQ</a></li>
                    <li><a href="/mentions-legales.php">Mentions légales</a></li>
                    <li><a href="/confidentialite.php">Politique de confidentialité</a></li>
                    <li><a href="/cgs.php">Conditions générales de service</a></li>
                </ul>
            </div>
            <div class="site-footer__col">
                <h3>Contact</h3>
                <ul>
                    <li><?= e(get_setting($pdo, 'phone_primary', 'À compléter')) ?></li>
                    <li><?= e(get_setting($pdo, 'email_contact', 'À compléter')) ?></li>
                    <li><?= e(get_setting($pdo, 'address', 'Adresse à compléter')) ?></li>
                </ul>
            </div>
        </div>
        <div class="container site-footer__bottom">
            <p>&copy; <?= date('Y') ?> Kalinza Group. Tous droits réservés.</p>
        </div>
    </footer>

    <a href="<?= e($whatsapp) ?>" class="whatsapp-float" target="_blank" rel="noopener" aria-label="Nous écrire sur WhatsApp">
        <svg viewBox="0 0 32 32" width="28" height="28" fill="currentColor" aria-hidden="true">
            <path d="M16 2C8.3 2 2 8.3 2 16c0 2.6.7 5.1 2 7.3L2 30l6.9-1.8c2.1 1.2 4.5 1.8 7.1 1.8 7.7 0 14-6.3 14-14S23.7 2 16 2zm0 25.4c-2.3 0-4.5-.6-6.4-1.8l-.5-.3-4.1 1.1 1.1-4-.3-.5C4.6 20.1 4 18.1 4 16 4 9.4 9.4 4 16 4s12 5.4 12 12-5.4 11.4-12 11.4z"/>
            <path d="M22.4 19.1c-.3-.2-2-1-2.3-1.1-.3-.1-.5-.2-.8.2s-.9 1.1-1.1 1.3c-.2.2-.4.2-.8.1-.3-.2-1.4-.5-2.7-1.7-1-.9-1.7-2-1.9-2.3-.2-.3 0-.5.1-.7.1-.1.3-.4.5-.5.2-.2.2-.3.3-.5.1-.2 0-.4 0-.6 0-.2-.8-1.9-1-2.6-.3-.7-.6-.6-.8-.6h-.7c-.2 0-.6.1-.9.4-.3.3-1.2 1.1-1.2 2.8 0 1.7 1.2 3.3 1.4 3.5.2.2 2.4 3.6 5.8 5 .8.3 1.4.6 1.9.7.8.3 1.5.2 2.1.1.6-.1 2-.8 2.3-1.6.3-.8.3-1.4.2-1.6-.1-.1-.3-.2-.6-.4z"/>
        </svg>
    </a>

    <script src="/assets/js/main.js"></script>
</body>
</html>
