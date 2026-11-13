<?php
/**
 * Routeur pour le serveur PHP intégré (développement uniquement) :
 *   php -S localhost:8000 router.php
 * En production, ce rôle est tenu par .htaccess.
 */
$chemin = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Même protection que .htaccess : dossiers internes et fichiers de travail.
if (preg_match('#^/(app|data|docs|docker|letsmanu-logos)(/|$)#', $chemin) || preg_match('#(\.md|/router\.php|/Dockerfile|/docker-compose\.yml)$#', $chemin) || preg_match('#(^|/)\.(?!well-known)#', $chemin)) {
    http_response_code(403);
    exit('Accès interdit');
}

if ($chemin !== '/' && is_file(__DIR__ . $chemin) && !str_ends_with($chemin, '.php')) {
    return false; // fichier statique servi tel quel
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/index.php';
