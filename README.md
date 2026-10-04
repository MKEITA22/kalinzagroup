# Kalinza Group — Site web (PHP / MySQL)

## Structure
```
/index.php              Accueil
/devis.php               Formulaire de devis (fonctionnel, connecté à la BDD)
/config/database.php     Connexion PDO — à configurer avec les identifiants Hostinger
/includes/               header.php, footer.php, functions.php (partagés par toutes les pages)
/admin/                  Espace d'administration (login.php, index.php = tableau de bord)
/database/schema.sql     Script de création de toutes les tables
/assets/                 css, js, images (logo inclus)
```

## Toutes les pages sont livrées
Public : `index.php`, `a-propos.php`, `imprimerie.php`, `prod.php`, `digital.php` (nouveau),
`galerie.php`, `devis.php`, `reservation.php`, `contact.php`, `faq.php`, `mentions-legales.php`,
`confidentialite.php`, `cgs.php`, `404.php`.

Admin (protégé par `admin/auth.php`) : `admin/login.php`, `admin/index.php` (tableau de bord),
`admin/quotes.php`, `admin/bookings.php`, `admin/services.php`, `admin/portfolio.php`.

## Nouveauté : Kalinza Digital
Une 3e division a été ajoutée : développement web et applications mobiles.
- Nouvelle page `digital.php` avec ses propres services par défaut (sites web, plateformes sur mesure, apps Android/iOS, UI/UX, maintenance, intégrations).
- Si vous avez déjà importé `database/schema.sql` avant cette mise à jour, exécutez `database/migration_002_add_digital_division.sql` pour ajouter la valeur `digital` aux colonnes `division` (tables `services` et `quote_requests`).
- Le formulaire de devis, le menu, le pied de page et l'admin (services) prennent désormais en compte cette 3e division.

## Design & animations
- Icônes SVG en ligne (`service_icon()` dans `includes/functions.php`) sur toutes les cartes de service et de division — aucune dépendance externe.
- Système de révélation au scroll : ajoutez `data-reveal` (et éventuellement `style="--delay:0.1s"`) à un élément pour qu'il apparaisse en fondu/translation lors du défilement (`assets/js/main.js`, via IntersectionObserver, avec repli gracieux si non supporté, et respect de `prefers-reduced-motion`).
- Hero animé : dégradé qui ondule doucement + formes décoratives flottantes évoquant le logo.
- Cartes avec ombre et effet de levée au survol, boutons avec ombre colorée, zoom doux sur les vignettes de la galerie, pulsation discrète du bouton WhatsApp.

## Cartes produit (Imprimerie, Prod, Digital)
Chaque service s'affiche désormais comme une carte produit complète : image, titre, description courte,
quantité minimum, délai de livraison, bouton « Demander un devis »/« Réserver » et bouton **WhatsApp**
dont le message est pré-rempli avec `Je suis intéressé par <nom du service>` (voir `whatsapp_product_link()`
dans `includes/functions.php`).

- Gestion complète depuis `admin/services.php` : image (upload), quantité minimum, délai, visibilité.
- Tant qu'aucun service n'est ajouté en base pour une division, des cartes par défaut s'affichent avec
  l'indication neutre « Sur devis » pour la quantité et le délai (aucune donnée inventée).
- Si vous avez déjà importé `database/schema.sql` avant cette mise à jour, exécutez
  `database/migration_003_product_cards.sql` pour ajouter les colonnes `min_quantity` et `delivery_time`.
- Le dossier `uploads/services/` est utilisé pour les images des cartes produit (créé automatiquement,
  vérifiez ses permissions après déploiement).

## Calendrier de réservation
- `reservation.php` (public) : calendrier mensuel — les jours avec créneaux disponibles sont cliquables
  (vert), les jours complets apparaissent en orange, les jours passés/sans créneau ne sont pas cliquables.
  En cliquant sur une date, les horaires disponibles s'affichent ; en choisissant un horaire, le formulaire
  de coordonnées apparaît avec le créneau pré-rempli. Le créneau est bloqué dès l'envoi de la demande
  (pour éviter les doublons) mais **jamais confirmé automatiquement** — l'admin valide ensuite.
- `admin/availability.php` (nouveau) : ouvrir des créneaux (date + liste d'horaires séparés par des
  virgules), vue calendrier du mois, activer/désactiver ou supprimer un créneau.
- `admin/bookings.php` : vue calendrier en plus de la liste — chaque jour affiche le nombre de réservations
  (vert = au moins une en attente, orange = toutes traitées), cliquer sur un jour filtre la liste en dessous.
  Annuler ou reporter une réservation **libère automatiquement** son créneau ; le confirmer ou le repasser
  en attente le ré-occupe.
- Aucune nouvelle table n'était nécessaire : `availability_slots` et `bookings.slot_id` existaient déjà
  dans le schéma initial.

## Déploiement sur Hostinger
1. Dans hPanel, créer une base de données MySQL et un utilisateur associé.
2. Importer `database/schema.sql` via phpMyAdmin.
3. Renseigner les identifiants réels dans `config/database.php` (DB_NAME, DB_USER, DB_PASS).
4. Créer un premier compte admin (mot de passe hashé avec `password_hash()`), par exemple via un script PHP temporaire :
   ```php
   echo password_hash('votre_mot_de_passe', PASSWORD_DEFAULT);
   ```
   puis insérer l'utilisateur dans la table `users` avec ce hash.
5. Uploader l'ensemble du dossier dans `public_html` (ou un sous-dossier si domaine additionnel).
6. Vérifier les permissions du dossier `uploads/` (créé automatiquement, doit rester inscriptible).
7. Mettre à jour `site_settings` (téléphone, WhatsApp, email, adresse, réseaux sociaux) directement en base ou via une future page admin dédiée.

## À compléter avant mise en ligne
- Adresse, e-mail professionnel, liens réseaux sociaux (dans `site_settings`)
- Numéro WhatsApp réel dans `site_settings.whatsapp_number`
- Notification e-mail automatique à l'administrateur (PHPMailer/SMTP Hostinger) sur `devis.php` et le futur `reservation.php`
- Contenu réel (textes, photos) remplaçant les textes provisoires
