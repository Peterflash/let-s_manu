<?php
/**
 * Page d'un pôle (Services industriels ou Construction métallique).
 * Variables : $pole ('industriel' | 'construction'), $liees (réalisations du pôle).
 */
$services = config("poles.$pole");
?>
<section class="tete-page">
    <div class="conteneur">
        <h1><?= t("$pole.titre") ?></h1>
        <p class="tete-page__chapeau"><?= t("$pole.chapeau") ?></p>
        <div class="tete-page__actions">
            <a class="btn btn--primaire" href="<?= e(url('contact') . '?pole=' . $pole) ?>#formulaire" data-suivi="devis"><?= t('ui.devis') ?></a>
<?php if ($pole === 'industriel'): ?>
            <a class="btn btn--contour" href="#urgence"><?= icon('alerte') ?><span><?= t('industriel.urgence.titre') ?></span></a>
<?php endif; ?>
        </div>
        <nav class="sommaire" aria-label="<?= t("$pole.sommaire") ?>">
            <ul>
<?php foreach ($services as $s): ?>
                <li><a href="#<?= e($s) ?>"><?= t("$pole.services.$s.titre") ?></a></li>
<?php endforeach; ?>
            </ul>
        </nav>
    </div>
</section>

<section class="section section--serree" aria-labelledby="pourquoi-titre">
    <div class="conteneur">
        <h2 id="pourquoi-titre" class="revele"><?= t("$pole.pourquoi.titre") ?></h2>
        <div class="raisons revele">
<?php foreach (['a', 'b', 'c'] as $r): ?>
            <div class="raisons__item">
                <h3><?= t("$pole.pourquoi.$r.titre") ?></h3>
                <p><?= t("$pole.pourquoi.$r.texte") ?></p>
            </div>
<?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--gris" aria-labelledby="prestations-titre">
    <div class="conteneur">
        <h2 id="prestations-titre" class="revele"><?= t("$pole.sommaire") ?></h2>
<?php foreach ($services as $s): $nomService = (string) tx("$pole.services.$s.titre"); ?>
        <article class="prestation revele" id="<?= e($s) ?>">
            <div class="prestation__image"><?= visuel($s, $nomService) ?></div>
            <div class="prestation__corps">
                <h3><?= e($nomService) ?></h3>
                <p><?= t("$pole.services.$s.texte") ?></p>
                <div class="prestation__listes">
                    <div>
                        <h4><?= t("$pole.libelles.exemples") ?></h4>
                        <ul class="liste-traits">
<?php foreach (lines("$pole.services.$s.exemples") as $ligne): ?>
                            <li><?= e($ligne) ?></li>
<?php endforeach; ?>
                        </ul>
                    </div>
                    <div>
                        <h4><?= t("$pole.libelles.equipements") ?></h4>
                        <ul class="liste-traits">
<?php foreach (lines("$pole.services.$s.equipements") as $ligne): ?>
                            <li><?= e($ligne) ?></li>
<?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <div class="prestation__actions">
                    <a class="btn btn--primaire" href="<?= e(url('contact') . '?pole=' . $pole . '&service=' . $s) ?>#formulaire" data-suivi="devis"><?= t("$pole.libelles.devis") ?></a>
                    <a class="lien-icone" href="<?= e(wa_href(tx('ui.wa_service') . $nomService)) ?>" target="_blank" rel="noopener" data-suivi="whatsapp"><?= icon('whatsapp') ?><span><?= t('ui.ecrire_whatsapp') ?></span></a>
                </div>
            </div>
        </article>
<?php endforeach; ?>
    </div>
</section>

<?php if ($pole === 'industriel'): ?>
<section class="urgence" id="urgence" aria-labelledby="urgence-titre">
    <div class="conteneur urgence__grille">
        <div class="urgence__texte">
            <h2 id="urgence-titre"><?= icon('alerte') ?><span><?= t('industriel.urgence.titre') ?></span></h2>
            <p><?= t('industriel.urgence.texte') ?></p>
        </div>
        <div class="urgence__actions">
            <a class="btn btn--primaire btn--grand" href="<?= e(tel_href()) ?>" data-suivi="appel"><?= icon('telephone') ?><span><?= e(cfg('telephone')) ?></span></a>
            <a class="btn btn--clair btn--grand" href="<?= e(wa_href((string) tx('ui.wa_urgence'))) ?>" target="_blank" rel="noopener" data-suivi="whatsapp"><?= icon('whatsapp') ?><span><?= t('ui.whatsapp') ?></span></a>
        </div>
    </div>
</section>

<section class="section" aria-labelledby="moyens-titre">
    <div class="conteneur duo revele">
        <div class="duo__texte">
            <h2 id="moyens-titre"><?= t('industriel.moyens.titre') ?></h2>
            <p><?= t('industriel.moyens.texte') ?></p>
        </div>
        <ul class="liste-regles">
<?php foreach (lines('industriel.moyens.liste') as $ligne): ?>
            <li><?= icon('coche') ?><span><?= e($ligne) ?></span></li>
<?php endforeach; ?>
        </ul>
    </div>
</section>

<section class="section section--gris" aria-labelledby="secteurs-titre">
    <div class="conteneur">
        <div class="section__tete revele">
            <h2 id="secteurs-titre"><?= t('industriel.secteurs.titre') ?></h2>
            <p><?= t('industriel.secteurs.texte') ?></p>
        </div>
        <div class="revele"><?php partial('secteurs'); ?></div>
    </div>
</section>
<?php else: ?>
<section class="section" aria-labelledby="processus-titre">
    <div class="conteneur">
        <div class="section__tete revele">
            <h2 id="processus-titre"><?= t('construction.processus.titre') ?></h2>
            <p><?= t('construction.processus.texte') ?></p>
        </div>
        <div class="revele"><?php partial('etapes', ['cle' => 'construction.processus']); ?></div>
    </div>
</section>

<section class="section section--gris" aria-labelledby="exemples-titre">
    <div class="conteneur">
        <div class="section__tete revele">
            <h2 id="exemples-titre"><?= t('construction.exemples.titre') ?></h2>
            <p><?= t('construction.exemples.texte') ?></p>
        </div>
        <dl class="exemples revele">
<?php foreach (['a', 'b', 'c', 'd', 'e'] as $x): ?>
            <div>
                <dt><?= t("construction.exemples.$x.titre") ?></dt>
                <dd><?= t("construction.exemples.$x.texte") ?></dd>
            </div>
<?php endforeach; ?>
        </dl>
    </div>
</section>
<?php endif; ?>

<?php if ($liees): ?>
<section class="section" aria-labelledby="liees-titre">
    <div class="conteneur">
        <div class="section__tete section__tete--lien revele">
            <h2 id="liees-titre"><?= t("$pole.liees") ?></h2>
            <a class="lien-fort" href="<?= e(url('realisations') . '?pole=' . $pole) ?>"><?= t('home.realisations.lien') ?></a>
        </div>
        <div class="fiches revele">
<?php foreach ($liees as $r) {
    partial('fiche', ['r' => $r]);
} ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php partial('appel-final'); ?>
