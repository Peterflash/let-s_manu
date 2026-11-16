<?php
/** Gabarit de l'espace d'administration. Variables : $section, $contenu. */
$connecte = !empty($_SESSION['admin']) && $section !== 'acces'; // pas de menu sur les pages de connexion et de création du compte
$menu = [
    'accueil'      => ['Tableau de bord', ''],
    'demandes'     => ['Demandes de devis', 'demandes'],
    'realisations' => ['Réalisations', 'realisations'],
    'textes'       => ['Textes du site', 'textes'],
    'photos'       => ['Photos du site', 'photos'],
    'faq'          => ['Questions rapides', 'faq'],
    'parametres'   => ['Paramètres', 'parametres'],
];
$actif = ['demande' => 'demandes', 'realisation' => 'realisations'][$section] ?? $section;
$nouvelles = $connecte ? count(array_filter(store_read('demandes'), fn ($d) => ($d['statut'] ?? '') === 'nouvelle')) : 0;
$succes = flash('succes');
$erreur = flash('erreur');
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Administration — <?= e(cfg('raison_sociale')) ?></title>
<?php partial('favicons'); ?>
    <link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
    <script src="<?= e(asset('js/admin.js')) ?>" defer></script>
</head>
<body class="admin">
<header class="admin-entete">
    <div class="admin-entete__barre">
        <a class="logo logo--clair" href="<?= e(admin_url()) ?>"><?= logo('sombre', 130, cfg('raison_sociale')) ?></a>
        <span class="admin-entete__titre">Administration</span>
<?php if ($connecte): ?>
        <a class="admin-entete__site" href="<?= e(url('home', 'fr')) ?>" target="_blank" rel="noopener">Voir le site</a>
        <form method="post" action="<?= e(admin_url('deconnexion')) ?>"><?= csrf_field() ?><button class="admin-entete__sortie" type="submit">Se déconnecter</button></form>
<?php endif; ?>
    </div>
<?php if ($connecte): ?>
    <nav class="admin-menu" aria-label="Administration">
        <ul>
<?php foreach ($menu as $cle => [$libelle, $chemin]): ?>
            <li><a href="<?= e(admin_url($chemin)) ?>"<?= $cle === $actif ? ' aria-current="page"' : '' ?>><?= e($libelle) ?><?php if ($cle === 'demandes' && $nouvelles > 0): ?> <span class="pastille"><?= $nouvelles ?></span><?php endif; ?></a></li>
<?php endforeach; ?>
        </ul>
    </nav>
<?php endif; ?>
</header>

<main class="admin-contenu">
<?php if ($succes): ?>
    <p class="message message--succes" role="status"><?= e($succes) ?></p>
<?php endif; ?>
<?php if ($erreur): ?>
    <p class="message message--erreur" role="alert"><?= e($erreur) ?></p>
<?php endif; ?>
<?= $contenu ?>
</main>
</body>
</html>
