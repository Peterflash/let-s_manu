<?php
/** Mentions légales et politique de confidentialité. Variable : $type ('mentions' | 'confidentialite'). */
?>
<section class="tete-page">
    <div class="conteneur">
        <h1><?= t("$type.titre") ?></h1>
<?php if ($type === 'confidentialite'): ?>
        <p class="tete-page__chapeau"><?= t('confidentialite.intro') ?></p>
<?php endif; ?>
    </div>
</section>

<section class="section section--serree">
    <div class="conteneur prose prose--etroite">
<?php if ($type === 'mentions'): ?>
        <h2><?= t('mentions.editeur.titre') ?></h2>
        <?php partial('plaque'); ?>
        <p>
            <?= t('mentions.editeur.telephone') ?> : <a href="<?= e(tel_href()) ?>">+225 <?= e(cfg('telephone')) ?></a><br>
            <?= t('mentions.editeur.email') ?> : <a href="mailto:<?= e(cfg('email')) ?>"><?= e(cfg('email')) ?></a>
        </p>

        <h2><?= t('mentions.publication.titre') ?></h2>
        <p><?= t('mentions.publication.texte') ?> <?= e(cfg('gerante')) ?>.</p>

        <h2><?= t('mentions.hebergeur.titre') ?></h2>
        <p><?= e(cfg('hebergeur_nom')) ?><br><?= e(cfg('hebergeur_adresse')) ?><br><a href="<?= e(cfg('hebergeur_site')) ?>" target="_blank" rel="noopener"><?= e(preg_replace('#^https?://#', '', cfg('hebergeur_site'))) ?></a></p>

<?php foreach (['propriete', 'photos', 'responsabilite'] as $bloc): ?>
        <h2><?= t("mentions.$bloc.titre") ?></h2>
        <?= paras((string) tx("mentions.$bloc.texte")) ?>
<?php endforeach; ?>
<?php else: ?>
<?php foreach (['a', 'b', 'c', 'd', 'e', 'f', 'g'] as $bloc): ?>
        <h2><?= t("confidentialite.$bloc.titre") ?></h2>
        <?= paras((string) tx("confidentialite.$bloc.texte")) ?>
<?php endforeach; ?>
        <p><a class="lien-fort" href="<?= e(url('contact')) ?>"><?= t('nav.contact') ?></a></p>
<?php endif; ?>
    </div>
</section>
