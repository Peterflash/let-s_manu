<?php
/**
 * Table des URL (françaises et anglaises) et distribution des requêtes.
 */
declare(strict_types=1);

const ROUTES = [
    'fr' => [
        'home'            => '',
        'industriel'      => 'services-industriels',
        'construction'    => 'construction-metallique',
        'realisations'    => 'realisations',
        'about'           => 'a-propos',
        'contact'         => 'contact',
        'mentions'        => 'mentions-legales',
        'confidentialite' => 'politique-de-confidentialite',
        'plaquette'       => 'plaquette',
        'carte'           => 'carte-de-visite',
    ],
    'en' => [
        'home'            => '',
        'industriel'      => 'industrial-services',
        'construction'    => 'steel-construction',
        'realisations'    => 'projects',
        'about'           => 'about',
        'contact'         => 'contact',
        'mentions'        => 'legal-notice',
        'confidentialite' => 'privacy-policy',
        'plaquette'       => 'brochure',
        'carte'           => 'business-card',
    ],
];

function dispatch(): void
{
    $chemin = rawurldecode((string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'));
    if (BASE !== '' && str_starts_with($chemin, BASE)) {
        $chemin = substr($chemin, strlen(BASE));
    }
    $chemin = trim($chemin, '/');
    if ($chemin === 'index.php') {
        $chemin = '';
    }

    send_security_headers();

    // Routes techniques
    if ($chemin === 'sitemap.xml') {
        page_sitemap();
        return;
    }
    if ($chemin === 'robots.txt') {
        header('Content-Type: text/plain; charset=UTF-8');
        echo "User-agent: *\nDisallow: " . BASE . "/admin\n\nSitemap: " . abs_url(BASE . '/sitemap.xml') . "\n";
        return;
    }
    if ($chemin === 't') { // comptage d'un clic (Appeler, WhatsApp, Devis, Plaquette)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            stats_hit((string) ($_POST['e'] ?? ''));
        }
        http_response_code(204);
        return;
    }
    if ($chemin === 'admin' || str_starts_with($chemin, 'admin/')) {
        require APP . '/admin.php';
        admin_dispatch(trim(substr($chemin, 5), '/'));
        return;
    }

    // Langue : préfixe /en pour l'anglais
    $segments = $chemin === '' ? [] : explode('/', $chemin);
    if (($segments[0] ?? '') === 'en') {
        lang('en');
        array_shift($segments);
    }
    $slug = $segments[0] ?? '';
    $suite = array_slice($segments, 1);

    // Fichiers de la carte de visite : fiche contact et QR code (carte-de-visite.vcf, .svg, .png)
    if (!$suite && preg_match('/^(.+)\.(vcf|svg|png)$/', $slug, $fichier) && $fichier[1] === ROUTES[lang()]['carte']) {
        page_carte_fichier($fichier[2]);
        return;
    }

    $page = array_search($slug, ROUTES[lang()], true);
    if ($page === false || (count($suite) > 0 && $page !== 'realisations') || count($suite) > 1) {
        not_found();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        stats_hit('vues');
    }

    $commun = [
        'page'        => $page,
        'titre'       => tx("meta.$page.title"),
        'description' => tx("meta.$page.desc"),
        'alternates'  => ['fr' => url($page, 'fr'), 'en' => url($page, 'en')],
    ];

    switch ($page) {
        case 'home':
            render('pages/home', $commun + ['recentes' => array_slice(realisations(), 0, 6)]);
            break;

        case 'industriel':
        case 'construction':
            $liees = array_values(array_filter(realisations(), fn ($r) => ($r['pole'] ?? '') === $page));
            render('pages/pole', $commun + ['pole' => $page, 'liees' => array_slice($liees, 0, 3)]);
            break;

        case 'realisations':
            if ($suite) {
                page_realisation($suite[0], $commun);
            } else {
                render('pages/realisations', $commun + [
                    'liste'         => realisations(),
                    'filtrePole'    => (string) ($_GET['pole'] ?? ''),
                    'filtreSecteur' => (string) ($_GET['secteur'] ?? ''),
                ]);
            }
            break;

        case 'about':
            render('pages/about', $commun);
            break;

        case 'carte':
            render('pages/carte', $commun);
            break;

        case 'contact':
            require APP . '/contact.php';
            page_contact($commun);
            break;

        case 'mentions':
        case 'confidentialite':
            render('pages/legal', $commun + ['type' => $page]);
            break;

        case 'plaquette':
            extract($commun);
            require APP . '/views/pages/plaquette.php';
            break;
    }
}

function page_realisation(string $slug, array $commun): void
{
    foreach (realisations() as $realisation) {
        if (($realisation['slug'] ?? '') === $slug) {
            $titre = L($realisation['titre']) . ' | ' . cfg('raison_sociale');
            render('pages/realisation', [
                'titre'       => $titre,
                'description' => mb_strimwidth(L($realisation['defi'] ?? ''), 0, 158, '…'),
                'alternates'  => ['fr' => url('realisations', 'fr') . '/' . $slug, 'en' => url('realisations', 'en') . '/' . $slug],
                'r'           => $realisation,
            ] + $commun);
            return;
        }
    }
    not_found();
}

function page_sitemap(): void
{
    header('Content-Type: application/xml; charset=UTF-8');
    $pages = ['home', 'industriel', 'construction', 'realisations', 'about', 'contact', 'carte', 'mentions', 'confidentialite'];
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
    $ligne = function (string $fr, string $en, string $priorite): void {
        foreach ([$fr, $en] as $adresse) {
            echo '  <url><loc>' . e(abs_url($adresse)) . '</loc>'
                . '<xhtml:link rel="alternate" hreflang="fr" href="' . e(abs_url($fr)) . '"/>'
                . '<xhtml:link rel="alternate" hreflang="en" href="' . e(abs_url($en)) . '"/>'
                . '<priority>' . $priorite . '</priority></url>' . "\n";
        }
    };
    foreach ($pages as $page) {
        $ligne(url($page, 'fr'), url($page, 'en'), $page === 'home' ? '1.0' : (in_array($page, ['mentions', 'confidentialite'], true) ? '0.2' : '0.8'));
    }
    foreach (realisations() as $realisation) {
        $ligne(url('realisations', 'fr') . '/' . $realisation['slug'], url('realisations', 'en') . '/' . $realisation['slug'], '0.6');
    }
    echo '</urlset>';
}
