<?php
/**
 * Démarrage commun à toutes les requêtes.
 */
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('APP', __DIR__);
define('DATA', ROOT . '/data');
define('UPLOADS', ROOT . '/uploads');

mb_internal_encoding('UTF-8');
date_default_timezone_set('Africa/Abidjan');

require APP . '/lib/helpers.php';
require APP . '/lib/store.php';
require APP . '/lib/icons.php';
require APP . '/lib/images.php';
require APP . '/lib/mailer.php';
require APP . '/lib/stats.php';
require APP . '/lib/qrcode.php';
require APP . '/lib/carte.php';
require APP . '/routes.php';

// Les erreurs ne sont affichées qu'en local ; en production elles vont dans le journal PHP.
error_reporting(E_ALL);
ini_set('display_errors', is_dev() ? '1' : '0');
ini_set('log_errors', '1');

// Chemin de base : le site fonctionne à la racine d'un domaine comme dans un sous-dossier.
define('BASE', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/'));

// Jeton à usage unique pour les rares scripts en ligne (politique CSP).
define('NONCE', base64_encode(random_bytes(16)));

/**
 * En-têtes de sécurité envoyés sur toutes les pages.
 */
function send_security_headers(): void
{
    $csp = [
        "default-src 'self'",
        "script-src 'self' 'nonce-" . NONCE . "' https://www.googletagmanager.com https://challenges.cloudflare.com",
        "style-src 'self' 'unsafe-inline'",
        // blob: : aperçu et allègement des photos dans le navigateur avant envoi (formulaire de devis, admin)
        "img-src 'self' data: blob: https://www.googletagmanager.com https://www.google-analytics.com",
        "font-src 'self'",
        "connect-src 'self' https://*.google-analytics.com https://*.analytics.google.com https://www.googletagmanager.com",
        "frame-src https://www.google.com https://challenges.cloudflare.com",
        "object-src 'none'",
        "base-uri 'self'",
        "form-action 'self'",
        "frame-ancestors 'self'",
    ];
    header('Content-Security-Policy: ' . implode('; ', $csp));
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    if (is_https()) {
        header('Strict-Transport-Security: max-age=31536000');
    }
}

/**
 * Session : ouverte uniquement là où elle sert (formulaire de devis, administration),
 * pour que les autres pages restent légères et sans cookie.
 */
function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name('lm_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => (BASE === '' ? '/' : BASE . '/'),
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
