<?php
/**
 * Connexion à la base de données — Kalinza Group
 * À adapter avec les identifiants fournis par Hostinger (hPanel > Bases de données MySQL)
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'kalinza_local');   // à remplacer par le vrai nom Hostinger
define('DB_USER', 'kalinza');     // à remplacer
define('DB_PASS', 'Kalinza2023');                     // à remplacer — ne jamais versionner ce fichier avec le vrai mot de passe

define('SITE_URL', 'https://www.kalinzagroup.ci'); // à ajuster au domaine réel

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // En production : journaliser l'erreur, ne jamais afficher les détails techniques au visiteur
    error_log('Erreur de connexion base de données : ' . $e->getMessage());
    http_response_code(500);
    die('Le site est momentanément indisponible. Merci de réessayer plus tard.');
}