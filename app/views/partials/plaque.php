<?php
/**
 * Informations légales présentées comme la plaque signalétique d'un équipement :
 * visibles et vérifiables, c'est un gage de sérieux pour un service achats.
 */
?>
<dl class="plaque">
    <div class="plaque__champ plaque__champ--large"><dt><?= t('about.legal.raison') ?></dt><dd><?= e(cfg('raison_sociale')) ?></dd></div>
    <div class="plaque__champ"><dt><?= t('about.legal.forme') ?></dt><dd><?= e(cfg('forme_juridique')) ?></dd></div>
    <div class="plaque__champ"><dt><?= t('about.legal.creation') ?></dt><dd><?= e(cfg('annee_creation')) ?></dd></div>
    <div class="plaque__champ plaque__champ--large"><dt><?= t('about.legal.idu') ?></dt><dd><?= e(cfg('idu')) ?></dd></div>
    <div class="plaque__champ plaque__champ--large"><dt><?= t('about.legal.gerante') ?></dt><dd><?= e(cfg('gerante')) ?></dd></div>
    <div class="plaque__champ plaque__champ--large"><dt><?= t('about.legal.siege') ?></dt><dd><?= e(cfg('ville')) ?>, <?= e(cfg_l('adresse')) ?></dd></div>
</dl>
