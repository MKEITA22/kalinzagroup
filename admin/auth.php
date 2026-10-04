<?php
/**
 * À inclure en tête de chaque page de l'espace admin.
 * Bloque l'accès si l'utilisateur n'est pas connecté.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

function require_role(string ...$roles): void
{
    if (!in_array($_SESSION['admin_role'] ?? '', $roles, true)) {
        http_response_code(403);
        die('Accès refusé.');
    }
}
