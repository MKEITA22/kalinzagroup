<?php
/**
 * Fonctions utilitaires — Kalinza Group
 */

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function get_setting(PDO $pdo, string $key, string $default = ''): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        $stmt = $pdo->query('SELECT setting_key, setting_value FROM site_settings');
        foreach ($stmt->fetchAll() as $row) {
            $cache[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $cache[$key] !== '' && isset($cache[$key]) ? $cache[$key] : $default;
}

function whatsapp_link(string $number, string $message = ''): string
{
    $number = preg_replace('/\D/', '', $number);
    $url = 'https://wa.me/' . $number;
    if ($message !== '') {
        $url .= '?text=' . rawurlencode($message);
    }
    return $url;
}

/**
 * Lien WhatsApp pré-rempli pour une carte produit/service.
 */
function whatsapp_product_link(PDO $pdo, string $productName): string
{
    $number = get_setting($pdo, 'whatsapp_number', '2250000000000');
    return whatsapp_link($number, "Je suis intéressé par {$productName}");
}

/**
 * Protection anti-spam basique : champ honeypot + jeton CSRF.
 * Compléter avec une validation serveur stricte sur chaque formulaire (devis, réservation, contact).
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check(string $token): bool
{
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Construit la grille d'un mois (semaines de 7 jours, du lundi au dimanche),
 * avec les jours des mois adjacents pour compléter les premières/dernières semaines.
 * Retourne un tableau de semaines, chaque semaine un tableau de 7 jours.
 * Chaque jour : ['date' => 'Y-m-d', 'day' => int, 'inMonth' => bool, 'isPast' => bool, 'isToday' => bool]
 */
function build_month_grid(int $year, int $month): array
{
    $first = new DateTime(sprintf('%04d-%02d-01', $year, $month));
    $weekdayOfFirst = (int) $first->format('N'); // 1 (lundi) à 7 (dimanche)
    $gridStart = (clone $first)->modify('-' . ($weekdayOfFirst - 1) . ' days');

    $today = (new DateTime())->format('Y-m-d');
    $weeks = [];
    $cursor = clone $gridStart;

    for ($w = 0; $w < 6; $w++) {
        $week = [];
        for ($d = 0; $d < 7; $d++) {
            $dateStr = $cursor->format('Y-m-d');
            $week[] = [
                'date'     => $dateStr,
                'day'      => (int) $cursor->format('j'),
                'inMonth'  => (int) $cursor->format('n') === $month,
                'isPast'   => $dateStr < $today,
                'isToday'  => $dateStr === $today,
            ];
            $cursor->modify('+1 day');
        }
        $weeks[] = $week;
        // Arrêter dès que la semaine dépasse le mois ET qu'on a fini le mois
        if ($cursor->format('n') !== $month && (int) $cursor->format('j') > 7) {
            break;
        }
    }
    return $weeks;
}

/**
 * Petites icônes SVG en ligne (pas de dépendance externe), utilisées sur les cartes de service.
 */
function service_icon(string $name): string
{
    $icons = [
        'card'    => '<path d="M3 6h18v12H3z"/><path d="M3 10h18"/><path d="M7 14h4"/>',
        'flyer'   => '<path d="M4 3h16v18l-4-3-4 3-4-3-4 3z"/><path d="M8 8h8M8 12h8"/>',
        'banner'  => '<path d="M3 5h18v3H3zM3 16h18v3H3z"/><path d="M6 8v8M18 8v8"/>',
        'brochure'=> '<path d="M4 4h7v16H4zM13 4h7v16h-7z"/>',
        'signage' => '<path d="M4 4h16v10H4z"/><path d="M9 18h6M12 14v4"/>',
        'design'  => '<circle cx="12" cy="12" r="9"/><path d="M9 12a3 3 0 106 0 3 3 0 00-6 0z"/>',
        'camera'  => '<path d="M4 8h3l2-3h6l2 3h3v11H4z"/><circle cx="12" cy="13" r="4"/>',
        'family'  => '<circle cx="8" cy="8" r="3"/><circle cx="16" cy="8" r="3"/><path d="M2 20c0-3 3-5 6-5s6 2 6 5M14 20c0-2.5 2-4 4-4s4 1.5 4 4"/>',
        'building'=> '<path d="M4 21V7l8-4 8 4v14z"/><path d="M9 21v-6h6v6M9 11h.01M15 11h.01M9 15h.01M15 15h.01"/>',
        'video'   => '<path d="M3 6h12v12H3z"/><path d="M15 10l6-3v10l-6-3z"/>',
        'social'  => '<circle cx="6" cy="12" r="2.5"/><circle cx="18" cy="6" r="2.5"/><circle cx="18" cy="18" r="2.5"/><path d="M8.3 10.7l7.4-3.4M8.3 13.3l7.4 3.4"/>',
        'code'    => '<path d="M9 8l-5 4 5 4M15 8l5 4-5 4"/>',
        'cart'    => '<circle cx="9" cy="20" r="1.4"/><circle cx="17" cy="20" r="1.4"/><path d="M3 4h2l2.4 11.2A2 2 0 009.36 17H18a2 2 0 001.95-1.57L21.5 8H6"/>',
        'phone'   => '<rect x="7" y="3" width="10" height="18" rx="2"/><path d="M11 18h2"/>',
        'layers'  => '<path d="M12 3l9 5-9 5-9-5 9-5z"/><path d="M3 13l9 5 9-5M3 17l9 5 9-5"/>',
        'shield'  => '<path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6l8-3z"/>',
        'link'    => '<path d="M9 15l6-6M8 12l-2 2a4 4 0 105.6 5.6l2-2M16 12l2-2a4 4 0 10-5.6-5.6l-2 2"/>',
        'quantity'=> '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a4 4 0 018 0v2"/>',
        'clock'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
        'whatsapp'=> '<path d="M12 3a9 9 0 00-7.8 13.4L3 21l4.8-1.2A9 9 0 1012 3z"/><path d="M8.5 8.7c.2-.5.4-.5.7-.5h.5c.2 0 .4 0 .6.4.2.5.7 1.7.7 1.8.1.1.1.3 0 .4-.1.2-.1.3-.3.4-.1.2-.3.3-.4.5-.1.1-.3.3-.1.6.2.4.8 1.3 1.8 2.1 1.2 1 1.1 1 1.4.9.2 0 .5-.3.6-.5.2-.2.3-.2.5-.1.2 0 1.3.6 1.5.7.2.1.3.2.4.3 0 .2 0 1-.4 1.5-.4.6-1.6 1.1-2.3 1.1-.6 0-1.7-.2-3.3-1.4-2-1.5-3.3-3.7-3.4-3.9-.1-.2-.9-1.2-.9-2.3 0-1.1.6-1.6.8-1.8z"/>',
    ];
    $path = $icons[$name] ?? $icons['design'];
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg>';
}
