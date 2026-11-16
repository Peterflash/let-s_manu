<?php
/** Fiche détaillée d'une réalisation. Variable : $r. */
$photos = $r['photos'] ?? [];
$etiquettes = etiquettes_photo();
$etiquetteProjet = etiquette_realisation($r);
$nomService = L($r['intervention'] ?? '');
?>
<section class="tete-page">
    <div class="conteneur">
        <p class="fil"><a href="<?= e(url('realisations')) ?>"><?= icon('chevron-g') ?><span><?= t('realisations.retour') ?></span></a></p>
<?php if ($etiquetteProjet !== ''): ?>
        <p><span class="etiquette etiquette--ligne"><?= e($etiquetteProjet) ?></span></p>
<?php endif; ?>
        <h1><?= e(L($r['titre'])) ?></h1>
        <p class="tete-page__chapeau"><?= e(L($r['client'] ?? '')) ?><?= L($r['client'] ?? '') !== '' && L($r['lieu'] ?? '') !== '' ? ', ' : '' ?><?= e(L($r['lieu'] ?? '')) ?></p>
    </div>
</section>

<section class="section section--serree">
    <div class="conteneur projet">
        <div class="projet__principal">
<?php if ($photos): ?>
            <h2 class="invisible"><?= t('realisations.photos') ?></h2>
            <ul class="galerie<?= count($photos) === 1 ? ' galerie--seule' : '' ?>" id="photos" data-visionneuse="<?= e(json_encode(photos_visionneuse($r), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>">
<?php foreach ($photos as $i => $photo):
    $fichier = $photo['fichier'];
    $estDessin = str_ends_with($fichier, '.svg');
    $legende = $etiquettes[$photo['etiquette'] ?? ''] ?? ($estDessin ? tx('ui.illustration') : '');
    $alt = L($r['titre']) . ' (' . ($i + 1) . '/' . count($photos) . ')';
?>
                <li>
                    <a href="<?= e(photo_url($fichier)) ?>" data-legende="<?= e($legende) ?>" aria-label="<?= t('realisations.agrandir') ?> <?= $i + 1 ?>">
                        <img<?= $estDessin ? ' class="est-dessin"' : '' ?> src="<?= e($estDessin ? photo_url($fichier) : photo_vignette($fichier)) ?>" alt="<?= e($alt) ?>" width="480" height="360"<?= $i > 0 ? ' loading="lazy"' : '' ?> decoding="async">
<?php if ($legende !== ''): ?>
                        <span class="etiquette"><?= e($legende) ?></span>
<?php endif; ?>
                    </a>
                </li>
<?php endforeach; ?>
            </ul>
<?php endif; ?>

<?php if (L($r['defi'] ?? '') !== ''): ?>
            <h2><?= t('realisations.defi') ?></h2>
            <?= paras(L($r['defi'])) ?>
<?php endif; ?>
<?php if (L($r['solution'] ?? '') !== ''): ?>
            <h2><?= t('realisations.solution') ?></h2>
            <?= paras(L($r['solution'])) ?>
<?php endif; ?>
        </div>

        <aside class="projet__fiche">
            <dl class="fiche-technique">
                <div><dt><?= t('realisations.pole') ?></dt><dd><?= t('poles.' . $r['pole']) ?></dd></div>
                <div><dt><?= t('realisations.secteur') ?></dt><dd><?= t('secteurs.' . $r['secteur']) ?></dd></div>
<?php if ($nomService !== ''): ?>
                <div><dt><?= t('realisations.intervention') ?></dt><dd><?= e($nomService) ?></dd></div>
<?php endif; ?>
<?php if (L($r['lieu'] ?? '') !== ''): ?>
                <div><dt><?= t('realisations.lieu') ?></dt><dd><?= e(L($r['lieu'])) ?></dd></div>
<?php endif; ?>
<?php if (L($r['duree'] ?? '') !== ''): ?>
                <div><dt><?= t('realisations.duree') ?></dt><dd><?= e(L($r['duree'])) ?></dd></div>
<?php endif; ?>
<?php if (($r['annee'] ?? '') !== ''): ?>
                <div><dt><?= t('realisations.annee') ?></dt><dd><?= e($r['annee']) ?></dd></div>
<?php endif; ?>
            </dl>
            <div class="projet__devis">
                <h2><?= t('realisations.similaire') ?></h2>
                <p><?= t('realisations.similaire_texte') ?></p>
                <a class="btn btn--primaire btn--bloc" href="<?= e(url('contact') . '?pole=' . $r['pole']) ?>#formulaire" data-suivi="devis"><?= t('ui.devis') ?></a>
                <a class="btn btn--contour btn--bloc" href="<?= e(wa_href(tx('ui.wa_service') . L($r['titre']))) ?>" target="_blank" rel="noopener" data-suivi="whatsapp"><?= icon('whatsapp') ?><span><?= t('ui.whatsapp') ?></span></a>
            </div>
        </aside>
    </div>
</section>

<?php if ($photos) {
    partial('visionneuse');
} ?>

<?php partial('appel-final'); ?>
