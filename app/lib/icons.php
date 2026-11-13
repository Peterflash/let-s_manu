<?php
/**
 * Jeu d'icônes du site : un seul style (trait de 1,75 px, 24 × 24) pour une cohérence totale.
 * Les icônes sont décoratives (aria-hidden) : le libellé est toujours écrit à côté.
 */
declare(strict_types=1);

function icon(string $nom, string $classe = ''): string
{
    static $traces = [
        'telephone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
        'whatsapp'  => '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/><path d="M9.6 8.2c-.2 2.9 2.4 6 5.9 6.3l1-1.5-1.7-1.1-.9.7c-.9-.4-1.7-1.3-2.1-2.2l.7-.9-1-1.7z" fill="currentColor" stroke-width="1"/>',
        'email'     => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="m22 6-10 7L2 6"/>',
        'repere'    => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
        'horloge'   => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'menu'      => '<path d="M3 6h18M3 12h18M3 18h18"/>',
        'fermer'    => '<path d="M18 6 6 18M6 6l12 12"/>',
        'coche'     => '<path d="M20 6 9 17l-5-5"/>',
        'fleche'    => '<path d="M5 12h14M12 5l7 7-7 7"/>',
        'chevron-g' => '<path d="m15 18-6-6 6-6"/>',
        'chevron-d' => '<path d="m9 18 6-6-6-6"/>',
        'telecharger' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>',
        'bouclier'  => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
        'alerte'    => '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0zM12 9v4M12 17h.01"/>',
        'photo'     => '<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>',
        'bulle'     => '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.6 2.2c-.7.4-1.1.9-1.1 1.6M12 16.2h.01"/>',
        'document'  => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h5"/>',
        'qualite'   => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/>',
        'qr'        => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3M21 14v.01M14 21h3M21 17v4h-1M17 17.5v.01"/>',
        'oeil'      =>'<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        // Secteurs
        'pioche'    => '<path d="M9 3.5c5 0 10.5 4.5 11.5 11.5"/><path d="M16.4 7.6 4 20"/>',
        'goutte'    => '<path d="m12 2.69 5.66 5.66a8 8 0 1 1-11.31 0z"/>',
        'eclair'    => '<path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>',
        'silo'      => '<path d="M7 21V8a5 5 0 0 1 10 0v13M4 21h16M7 12h10M7 16.5h10"/>',
        'feuille'   => '<path d="M12 21V11"/><path d="M12 13c0-4 3-7 8-7 0 5-3 8-8 7z"/><path d="M12 16c0-3-2.5-5.5-7-5.5 0 4 3 6.500 7 5.5z"/>',
        'usine'     => '<path d="M3 21V10l6 4v-4l6 4V4h5v17zM2 21h20"/>',
        'casque'    => '<path d="M3 18h18M5 18v-2a7 7 0 0 1 14 0v2M10 9.3V6h4v3.3"/>',
    ];

    $trace = $traces[$nom] ?? '';
    return '<svg class="icone ' . e($classe) . '" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $trace . '</svg>';
}

/**
 * Logo officiel de l'entreprise (fichiers préparés dans assets/img/logo/ à partir de letsmanu-logos/).
 * $fond : 'clair' (logo en couleur, texte bleu nuit) ou 'sombre' (texte blanc, pour les fonds foncés).
 * $largeur : largeur d'affichage approximative en pixels ; le navigateur choisit le fichier adapté à l'écran.
 * Le logo ne se redessine pas et ne se recolore pas : on n'utilise que ces fichiers.
 */
function logo(string $fond = 'clair', int $largeur = 150, string $alt = ''): string
{
    $fond = $fond === 'sombre' ? 'sombre' : 'clair';
    $fichier = fn (int $taille) => asset("img/logo/logo-$fond-$taille.webp");
    $source = $largeur > 360 ? 720 : ($largeur > 180 ? 360 : 180);
    return '<img class="logo__image" src="' . e($fichier($source)) . '"'
        . ' srcset="' . e($fichier(180)) . ' 180w, ' . e($fichier(360)) . ' 360w, ' . e($fichier(720)) . ' 720w"'
        . ' sizes="' . $largeur . 'px" width="' . $largeur . '" height="' . (int) round($largeur / 2.953) . '"'
        . ' alt="' . e($alt) . '" decoding="async">';
}
