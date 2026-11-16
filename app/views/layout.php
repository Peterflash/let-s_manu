<?php
/**
 * Gabarit commun à toutes les pages publiques.
 * Variables attendues : $page, $titre, $description, $contenu ; facultatives : $alternates, $noindex.
 */
$langue = lang();
$autreLangue = $langue === 'fr' ? 'en' : 'fr';
$page ??= 'home';
$alternates ??= ['fr' => url('home', 'fr'), 'en' => url('home', 'en')];
$canonique = abs_url($alternates[$langue]);
$waGeneral = wa_href((string) tx('ui.wa_general'));

$donneesStructurees = [
    '@context'    => 'https://schema.org',
    '@type'       => 'LocalBusiness',
    '@id'         => abs_url(url('home', 'fr')) . '#entreprise',
    'name'        => cfg('raison_sociale'),
    'description' => tx('meta.home.desc'),
    'url'         => abs_url(url('home')),
    'telephone'   => cfg('telephone_intl'),
    'email'       => cfg('email'),
    'foundingDate' => cfg('annee_creation'),
    'image'       => abs_url(BASE . '/assets/img/partage.png'),
    'logo'        => abs_url(BASE . '/assets/img/logo/logo-letsmanu.png'),
    'address'     => [
        '@type'           => 'PostalAddress',
        'streetAddress'   => cfg('adresse_fr'),
        'addressLocality' => cfg('ville'),
        'addressCountry'  => 'CI',
    ],
    'areaServed'  => ['@type' => 'Country', 'name' => "Côte d'Ivoire"],
    'knowsLanguage' => ['fr', 'en'],
];
if (cfg('gps_lat') !== '' && cfg('gps_lng') !== '') {
    $donneesStructurees['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => (float) cfg('gps_lat'), 'longitude' => (float) cfg('gps_lng')];
}
$reseaux = array_values(array_filter([cfg('linkedin'), cfg('facebook')]));
if ($reseaux) {
    $donneesStructurees['sameAs'] = $reseaux;
}

$menu = ['home', 'industriel', 'construction', 'realisations', 'about', 'contact'];
$pageActive = in_array($page, ['realisation', 'photos'], true) ? 'realisations' : $page;
?>
<!doctype html>
<html lang="<?= $langue ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titre) ?></title>
<?php if (!empty($description)): ?>
    <meta name="description" content="<?= e($description) ?>">
<?php endif; ?>
<?php if (!empty($noindex)): ?>
    <meta name="robots" content="noindex">
<?php else: ?>
    <link rel="canonical" href="<?= e($canonique) ?>">
    <link rel="alternate" hreflang="fr" href="<?= e(abs_url($alternates['fr'])) ?>">
    <link rel="alternate" hreflang="en" href="<?= e(abs_url($alternates['en'])) ?>">
    <link rel="alternate" hreflang="x-default" href="<?= e(abs_url($alternates['fr'])) ?>">
<?php endif; ?>
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= e(cfg('raison_sociale')) ?>">
    <meta property="og:title" content="<?= e($titre) ?>">
    <meta property="og:description" content="<?= e($description ?? '') ?>">
    <meta property="og:url" content="<?= e($canonique) ?>">
    <meta property="og:image" content="<?= e(abs_url(BASE . '/assets/img/partage.png')) ?>">
    <meta property="og:locale" content="<?= $langue === 'fr' ? 'fr_CI' : 'en_GB' ?>">
    <meta name="theme-color" content="#0B1725">
<?php partial('favicons'); ?>
    <link rel="preload" href="<?= e(BASE) ?>/assets/fonts/montserrat-var.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= e(BASE) ?>/assets/fonts/archivo-var.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
    <script nonce="<?= NONCE ?>">document.documentElement.classList.add('js');</script>
    <script type="application/ld+json" nonce="<?= NONCE ?>"><?= json_encode($donneesStructurees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
    <script src="<?= e(asset('js/site.js')) ?>" defer></script>
</head>
<body data-base="<?= e(BASE) ?>" data-ga="<?= e(cfg('ga4_id')) ?>">
<a class="evitement" href="#contenu"><?= t('ui.aller_contenu') ?></a>

<header class="entete-site" id="haut">
    <div class="conteneur entete-site__barre">
        <a class="logo" href="<?= e(url('home')) ?>" aria-label="<?= e(cfg('raison_sociale')) ?> — <?= t('nav.home') ?>">
            <?= logo('clair', 150) ?>
        </a>

        <nav class="menu" id="menu" aria-label="<?= t('nav.principale') ?>">
            <ul class="menu__liste">
<?php foreach ($menu as $cle): ?>
                <li><a href="<?= e(url($cle)) ?>"<?= $cle === $pageActive ? ' aria-current="page"' : '' ?>><?= t("nav.$cle") ?></a></li>
<?php endforeach; ?>
            </ul>
        </nav>

        <a class="langue" href="<?= e($alternates[$autreLangue]) ?>" hreflang="<?= $autreLangue ?>" lang="<?= $autreLangue ?>" aria-label="<?= t('ui.autre_langue') ?>"><?= strtoupper($autreLangue) ?></a>
        <a class="btn btn--primaire entete-site__devis" href="<?= e(url('contact')) ?>" data-suivi="devis"><?= t('ui.devis') ?></a>
        <button class="menu-bouton" type="button" aria-controls="menu" aria-expanded="false">
            <span class="menu-bouton__ouvrir"><?= icon('menu') ?></span>
            <span class="menu-bouton__fermer"><?= icon('fermer') ?></span>
            <span class="menu-bouton__libelle"><?= t('ui.menu') ?></span>
        </button>
    </div>
</header>

<main id="contenu">
<?= $contenu ?>
</main>

<footer class="pied">
    <div class="conteneur pied__grille">
        <div class="pied__marque">
            <a class="logo logo--clair" href="<?= e(url('home')) ?>" aria-label="<?= e(cfg('raison_sociale')) ?>">
                <?= logo('sombre', 180) ?>
            </a>
            <p><?= t('pied.accroche') ?></p>
            <p class="pied__documents"><?= t('pied.documents') ?></p>
            <p><a class="lien-icone" href="<?= e(plaquette_url()) ?>" data-suivi="plaquette"><?= icon('telecharger') ?><span><?= t('ui.plaquette') ?></span></a></p>
        </div>

        <nav class="pied__colonne" aria-label="<?= t('pied.pages') ?>">
            <h2 class="pied__titre"><?= t('pied.pages') ?></h2>
            <ul>
<?php foreach ($menu as $cle): ?>
                <li><a href="<?= e(url($cle)) ?>"><?= t("nav.$cle") ?></a></li>
<?php endforeach; ?>
            </ul>
        </nav>

        <div class="pied__colonne">
            <h2 class="pied__titre"><?= t('pied.contact') ?></h2>
            <ul class="pied__contact">
                <li><a class="lien-icone" href="<?= e(tel_href()) ?>" data-suivi="appel"><?= icon('telephone') ?><span><?= e(cfg('telephone')) ?></span></a></li>
                <li><a class="lien-icone" href="<?= e($waGeneral) ?>" target="_blank" rel="noopener" data-suivi="whatsapp"><?= icon('whatsapp') ?><span><?= t('ui.ecrire_whatsapp') ?></span></a></li>
                <li><a class="lien-icone" href="mailto:<?= e(cfg('email')) ?>"><?= icon('email') ?><span><?= e(cfg('email')) ?></span></a></li>
                <li class="lien-icone"><?= icon('repere') ?><span><?= e(cfg('ville')) ?>, <?= e(cfg_l('adresse')) ?></span></li>
                <li><a class="lien-icone" href="<?= e(url('carte')) ?>"><?= icon('qr') ?><span><?= t('carte.pied') ?></span></a></li>
<?php if (cfg('linkedin') !== ''): ?>
                <li><a href="<?= e(cfg('linkedin')) ?>" target="_blank" rel="noopener">LinkedIn</a></li>
<?php endif; ?>
<?php if (cfg('facebook') !== ''): ?>
                <li><a href="<?= e(cfg('facebook')) ?>" target="_blank" rel="noopener">Facebook</a></li>
<?php endif; ?>
            </ul>
        </div>
    </div>

    <div class="conteneur pied__bas">
        <p>© <?= date('Y') ?> <?= e(cfg('raison_sociale')) ?>, <?= e(cfg('forme_juridique')) ?>. IDU <?= e(cfg('idu')) ?>. <?= t('pied.droits') ?></p>
        <ul>
            <li><a href="<?= e(url('mentions')) ?>"><?= t('pied.mentions') ?></a></li>
            <li><a href="<?= e(url('confidentialite')) ?>"><?= t('pied.confidentialite') ?></a></li>
            <li><a href="<?= e($alternates[$autreLangue]) ?>" hreflang="<?= $autreLangue ?>" lang="<?= $autreLangue ?>"><?= t('ui.autre_langue') ?></a></li>
            <li><a href="<?= e(BASE) ?>/admin/connexion" rel="nofollow"><?= t('pied.admin') ?></a></li>
        </ul>
    </div>
</footer>

<?php /* Barre d'actions fixe en bas d'écran, sur téléphone */ ?>
<nav class="barre-actions" aria-label="<?= t('ui.actions_rapides') ?>">
    <a href="<?= e(tel_href()) ?>" data-suivi="appel"><?= icon('telephone') ?><span><?= t('ui.appeler') ?></span></a>
    <a href="<?= e($waGeneral) ?>" target="_blank" rel="noopener" data-suivi="whatsapp"><?= icon('whatsapp') ?><span><?= t('ui.whatsapp') ?></span></a>
    <a class="barre-actions__devis" href="<?= e(url('contact')) ?>" data-suivi="devis"><?= icon('document') ?><span><?= t('ui.devis_court') ?></span></a>
</nav>

<?php /* Bouton WhatsApp flottant (écrans larges ; sur téléphone la barre d'actions le remplace) */ ?>
<a class="wa-flottant" href="<?= e($waGeneral) ?>" target="_blank" rel="noopener" data-suivi="whatsapp">
    <?= icon('whatsapp') ?><span><?= t('ui.whatsapp') ?></span>
</a>

<?php partial('assistant'); ?>

<?php if (cfg('ga4_id') !== ''): ?>
<div class="consentement" id="consentement" hidden>
    <p><?= t('consentement.texte') ?> <a href="<?= e(url('confidentialite')) ?>"><?= t('pied.confidentialite') ?></a></p>
    <div class="consentement__actions">
        <button type="button" class="btn btn--contour btn--petit" data-consentement="non"><?= t('consentement.refuser') ?></button>
        <button type="button" class="btn btn--primaire btn--petit" data-consentement="oui"><?= t('consentement.accepter') ?></button>
    </div>
</div>
<?php endif; ?>
</body>
</html>
