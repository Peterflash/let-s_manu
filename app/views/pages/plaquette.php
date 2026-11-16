<?php
/**
 * Plaquette de présentation : page A4 autonome, pensée pour l'impression et l'export PDF.
 * Elle reprend les textes du site, donc reste à jour quand ils changent.
 */
$langue = lang();
?>
<!doctype html>
<html lang="<?= $langue ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= e($titre) ?></title>
<?php partial('favicons'); ?>
    <link rel="stylesheet" href="<?= e(asset('css/plaquette.css')) ?>">
</head>
<body>
<p class="outils"><a href="<?= e(url('about')) ?>"><?= t('nav.about') ?></a> <button type="button" id="imprimer"><?= t('plaquette.imprimer') ?></button></p>
<script nonce="<?= NONCE ?>">document.getElementById('imprimer').addEventListener('click', function () { window.print(); });</script>

<article class="feuille">
    <header class="couverture">
        <div class="couverture__marque">
            <?= logo('sombre', 520, cfg('raison_sociale')) ?>
        </div>
        <p class="couverture__sur"><?= t('plaquette.surtitre') ?></p>
        <h1><?= t('home.hero.titre') ?></h1>
        <p class="couverture__texte"><?= t('plaquette.promesse') ?></p>
        <img src="<?= e(asset('img/illustrations/hangars.svg')) ?>" alt="" width="480" height="360">
    </header>

    <section class="bloc">
        <h2><?= t('about.histoire.titre') ?></h2>
        <?= paras((string) tx('about.histoire.texte')) ?>
        <dl class="reperes">
<?php foreach (['a', 'b', 'c', 'd'] as $c): ?>
            <div><dd><?= t("home.chiffres.$c.valeur") ?></dd><dt><?= t("home.chiffres.$c.libelle") ?></dt></div>
<?php endforeach; ?>
        </dl>
    </section>
</article>

<?php /* Une feuille par pôle : le pôle industriel (7 prestations) remplit la sienne ; la méthode complète celle du dernier pôle */ ?>
<?php $dernierPole = array_key_last(config('poles')); ?>
<?php foreach (config('poles') as $pole => $services): ?>
<article class="feuille">
    <section class="bloc">
        <h2><?= t("poles.$pole") ?></h2>
        <p><?= t("$pole.chapeau") ?></p>
        <div class="prestations">
<?php foreach ($services as $s): ?>
            <div>
                <h3><?= t("$pole.services.$s.titre") ?></h3>
                <ul>
<?php foreach (lines("$pole.services.$s.exemples") as $ligne): ?>
                    <li><?= e($ligne) ?></li>
<?php endforeach; ?>
                </ul>
            </div>
<?php endforeach; ?>
        </div>
    </section>
<?php if ($pole === $dernierPole): ?>

    <section class="bloc">
        <h2><?= t('plaquette.methode') ?></h2>
        <ol class="methode">
<?php foreach (['a', 'b', 'c', 'd'] as $etape): ?>
            <li><strong><?= t("home.methode.$etape.titre") ?></strong><span><?= t("home.methode.$etape.texte") ?></span></li>
<?php endforeach; ?>
        </ol>
    </section>
<?php endif; ?>
</article>
<?php endforeach; ?>

<article class="feuille">

    <section class="bloc">
        <h2><?= t('about.hse.titre') ?></h2>
        <p><?= t('about.hse.texte') ?></p>
        <ul class="coches">
<?php foreach (lines('about.hse.points') as $point): ?>
            <li><?= e($point) ?></li>
<?php endforeach; ?>
        </ul>
    </section>

    <section class="bloc">
        <h2><?= t('home.secteurs.titre') ?></h2>
        <p class="secteurs"><?php foreach (array_keys(config('secteurs')) as $i => $s): ?><span><?= t("secteurs.$s") ?></span><?php endforeach; ?></p>
    </section>

    <section class="bloc bloc--contact">
        <div>
            <h2><?= t('plaquette.contact') ?></h2>
            <p class="grand">+225 <?= e(cfg('telephone')) ?></p>
            <p><?= t('ui.whatsapp') ?> : +225 <?= e(cfg('telephone')) ?><br><?= e(cfg('email')) ?><?= cfg('site_url') !== '' ? '<br>' . e(preg_replace('#^https?://#', '', rtrim(cfg('site_url'), '/'))) : '' ?></p>
            <p><?= e(cfg('ville')) ?>, <?= e(cfg_l('adresse')) ?></p>
<?php if (carte_adresse_publique()): /* pas de QR code vers une adresse locale dans un document imprimé */ ?>
            <div class="qr-plaquette">
                <?= qr_svg(carte_adresse(), (string) tx('carte.qr_alt')) ?>
                <p><?= t('plaquette.qr') ?></p>
            </div>
<?php endif; ?>
        </div>
        <div>
            <h2><?= t('about.legal.titre') ?></h2>
            <?php partial('plaque'); ?>
            <p class="petit"><?= t('about.legal.documents') ?></p>
        </div>
    </section>
</article>
</body>
</html>
