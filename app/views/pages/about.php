<?php
/** Page À propos. */
$motGerante = trim((string) tx('about.gerante.message'));
$portrait = emplacement('gerante');
$photoEquipe = emplacement('equipe');
$iconesValeurs = ['securite' => 'bouclier', 'qualite' => 'qualite', 'delais' => 'horloge', 'transparence' => 'oeil'];
?>
<section class="tete-page">
    <div class="conteneur">
        <h1><?= t('about.titre') ?></h1>
        <p class="tete-page__chapeau"><?= t('about.chapeau') ?></p>
    </div>
</section>

<section class="section" aria-labelledby="histoire-titre">
    <div class="conteneur duo duo--texte revele">
        <div class="prose">
            <h2 id="histoire-titre"><?= t('about.histoire.titre') ?></h2>
            <?= paras((string) tx('about.histoire.texte')) ?>
        </div>
        <div class="mission">
            <h2><?= t('about.mission.titre') ?></h2>
            <p><?= t('about.mission.texte') ?></p>
        </div>
    </div>
</section>

<?php if ($motGerante !== ''): ?>
<section class="section section--gris" aria-labelledby="gerante-titre">
    <div class="conteneur mot revele">
<?php if ($portrait): ?>
        <img class="mot__portrait" src="<?= e($portrait) ?>" alt="<?= e(cfg('gerante')) ?>" width="320" height="400" loading="lazy">
<?php endif; ?>
        <div>
            <h2 id="gerante-titre"><?= t('about.gerante.titre') ?></h2>
            <blockquote><?= paras($motGerante) ?></blockquote>
            <p class="mot__signature"><strong><?= e(cfg('gerante')) ?></strong><br><?= t('about.gerante.fonction') ?></p>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section<?= $motGerante === '' ? ' section--gris' : '' ?>" aria-labelledby="valeurs-titre">
    <div class="conteneur">
        <h2 id="valeurs-titre" class="revele"><?= t('about.valeurs.titre') ?></h2>
        <div class="valeurs revele">
<?php foreach ($iconesValeurs as $cle => $icone): ?>
            <div class="valeurs__item">
                <?= icon($icone) ?>
                <h3><?= t("about.valeurs.$cle.titre") ?></h3>
                <p><?= t("about.valeurs.$cle.texte") ?></p>
            </div>
<?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section" aria-labelledby="equipe-titre">
    <div class="conteneur duo duo--texte revele">
        <div class="prose">
            <h2 id="equipe-titre"><?= t('about.equipe.titre') ?></h2>
            <p><?= t('about.equipe.texte') ?></p>
<?php if ($photoEquipe): ?>
            <img class="photo-pleine" src="<?= e($photoEquipe) ?>" alt="<?= t('about.equipe.titre') ?>" width="1200" height="800" loading="lazy">
<?php endif; ?>
        </div>
        <div id="securite" class="ancre">
            <h2><?= t('about.hse.titre') ?></h2>
            <p><?= t('about.hse.texte') ?></p>
            <ul class="liste-regles">
<?php foreach (lines('about.hse.points') as $point): ?>
                <li><?= icon('bouclier') ?><span><?= e($point) ?></span></li>
<?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>

<section class="section section--gris" aria-labelledby="secteurs-titre">
    <div class="conteneur">
        <h2 id="secteurs-titre" class="revele"><?= t('about.secteurs.titre') ?></h2>
        <div class="revele"><?php partial('secteurs'); ?></div>
    </div>
</section>

<section class="section" aria-labelledby="legal-titre">
    <div class="conteneur duo revele">
        <div>
            <h2 id="legal-titre"><?= t('about.legal.titre') ?></h2>
            <?php partial('plaque'); ?>
        </div>
        <div class="prose">
            <p><?= t('about.legal.documents') ?></p>
            <p><?= t('about.legal.autres') ?></p>
            <div class="telechargement">
                <h3><?= t('about.plaquette.titre') ?></h3>
                <p><?= t('about.plaquette.texte') ?></p>
                <a class="btn btn--primaire" href="<?= e(plaquette_url()) ?>" data-suivi="plaquette"><?= icon('telecharger') ?><span><?= t('ui.plaquette') ?></span></a>
            </div>
        </div>
    </div>
</section>

<?php partial('appel-final'); ?>
