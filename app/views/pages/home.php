<?php
/** Page d'accueil. Variable : $recentes (réalisations récentes). */
$photoBandeau = emplacement('accueil');
$servicesIndustriels = array_map(fn ($s) => tx("industriel.services.$s.titre"), config('poles.industriel'));
$servicesConstruction = array_map(fn ($s) => tx("construction.services.$s.titre"), config('poles.construction'));
?>
<section class="hero<?= $photoBandeau ? ' hero--photo' : '' ?>">
    <div class="conteneur hero__grille">
        <div class="hero__texte">
            <h1><?= t('home.hero.titre') ?></h1>
            <p class="hero__chapeau"><?= t('home.hero.texte') ?></p>
            <div class="hero__entrees">
                <a class="btn btn--primaire btn--grand" href="<?= e(url('industriel')) ?>"><?= t('nav.industriel') ?></a>
                <a class="btn btn--clair btn--grand" href="<?= e(url('construction')) ?>"><?= t('nav.construction') ?></a>
            </div>
        </div>
        <div class="hero__visuel">
<?php if ($photoBandeau): ?>
            <img src="<?= e($photoBandeau) ?>" alt="" width="1200" height="900" fetchpriority="high">
<?php else: ?>
            <?php partial('hero-dessin'); ?>
<?php endif; ?>
        </div>
    </div>
</section>

<section class="chiffres" aria-labelledby="chiffres-titre">
    <div class="conteneur">
        <h2 class="invisible" id="chiffres-titre"><?= t('home.chiffres.titre') ?></h2>
        <dl class="chiffres__liste">
<?php foreach (['a', 'b', 'c', 'd'] as $c): ?>
            <div class="chiffres__item">
                <dt><?= t("home.chiffres.$c.libelle") ?></dt>
                <dd><?= t("home.chiffres.$c.valeur") ?></dd>
            </div>
<?php endforeach; ?>
        </dl>
    </div>
</section>

<section class="section" aria-labelledby="poles-titre">
    <div class="conteneur">
        <div class="section__tete revele">
            <h2 id="poles-titre"><?= t('home.poles.titre') ?></h2>
            <p><?= t('home.poles.texte') ?></p>
        </div>
        <div class="poles">
            <article class="pole pole--principal revele">
                <div class="pole__image"><?= visuel('soudure', tx('nav.industriel')) ?></div>
                <div class="pole__corps">
                    <p class="pole__rang"><?= t('home.poles.principal') ?></p>
                    <h3><?= t('nav.industriel') ?></h3>
                    <p><?= t('home.poles.industriel_texte') ?></p>
                    <ul class="liste-coches">
<?php foreach ($servicesIndustriels as $nom): ?>
                        <li><?= icon('coche') ?><span><?= e($nom) ?></span></li>
<?php endforeach; ?>
                    </ul>
                    <a class="btn btn--primaire" href="<?= e(url('industriel')) ?>"><?= t('home.poles.industriel_lien') ?></a>
                </div>
            </article>
            <article class="pole revele">
                <div class="pole__image"><?= visuel('hangars', tx('nav.construction')) ?></div>
                <div class="pole__corps">
                    <h3><?= t('nav.construction') ?></h3>
                    <p><?= t('home.poles.construction_texte') ?></p>
                    <ul class="liste-coches">
<?php foreach ($servicesConstruction as $nom): ?>
                        <li><?= icon('coche') ?><span><?= e($nom) ?></span></li>
<?php endforeach; ?>
                    </ul>
                    <a class="btn btn--contour" href="<?= e(url('construction')) ?>"><?= t('home.poles.construction_lien') ?></a>
                </div>
            </article>
        </div>
    </div>
</section>

<section class="section section--gris" aria-labelledby="methode-titre">
    <div class="conteneur">
        <div class="section__tete revele">
            <h2 id="methode-titre"><?= t('home.methode.titre') ?></h2>
            <p><?= t('home.methode.texte') ?></p>
        </div>
        <div class="revele"><?php partial('etapes', ['cle' => 'home.methode']); ?></div>
    </div>
</section>

<?php if ($recentes): ?>
<section class="section" aria-labelledby="recentes-titre">
    <div class="conteneur">
        <div class="section__tete section__tete--lien revele">
            <div>
                <h2 id="recentes-titre"><?= t('home.realisations.titre') ?></h2>
                <p><?= t('home.realisations.texte') ?></p>
            </div>
            <a class="lien-fort" href="<?= e(url('realisations')) ?>"><?= t('home.realisations.lien') ?></a>
        </div>
        <div class="fiches revele">
<?php foreach ($recentes as $r) {
    partial('fiche', ['r' => $r]);
} ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section section--gris" aria-labelledby="secteurs-titre">
    <div class="conteneur">
        <div class="section__tete revele">
            <h2 id="secteurs-titre"><?= t('home.secteurs.titre') ?></h2>
            <p><?= t('home.secteurs.texte') ?></p>
        </div>
        <div class="revele"><?php partial('secteurs'); ?></div>
    </div>
</section>

<section class="section" aria-labelledby="hse-titre">
    <div class="conteneur duo revele">
        <div class="duo__texte">
            <h2 id="hse-titre"><?= t('home.hse.titre') ?></h2>
            <p><?= t('home.hse.texte') ?></p>
            <p><a class="lien-fort" href="<?= e(url('about')) ?>#securite"><?= t('home.hse.lien') ?></a></p>
        </div>
        <ul class="liste-regles">
<?php foreach (lines('home.hse.points') as $point): ?>
            <li><?= icon('bouclier') ?><span><?= e($point) ?></span></li>
<?php endforeach; ?>
        </ul>
    </div>
</section>

<?php partial('appel-final'); ?>
