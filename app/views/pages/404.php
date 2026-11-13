<?php /** Page introuvable. */ ?>
<section class="tete-page tete-page--haute">
    <div class="conteneur">
        <h1><?= t('ui.introuvable_titre') ?></h1>
        <p class="tete-page__chapeau"><?= t('ui.introuvable_texte') ?></p>
        <div class="tete-page__actions">
            <a class="btn btn--primaire" href="<?= e(url('home')) ?>"><?= t('ui.retour_accueil') ?></a>
            <a class="btn btn--contour" href="<?= e(url('contact')) ?>"><?= t('nav.contact') ?></a>
        </div>
    </div>
</section>
