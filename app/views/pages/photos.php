<?php
/**
 * Photothèque : les photos des réalisations publiées, regroupées par projet (titre, date, lieu).
 * Chaque projet a une ancre (son slug) : l'icône photo des vignettes y mène directement.
 * Les vignettes sont des liens vers les photos (lisible sans JavaScript) ; site.js les ouvre dans la visionneuse.
 * Variable : $liste (réalisations qui ont au moins une photo).
 */
$aDesDemos = (bool) array_filter($liste, fn ($r) => !empty($r['demo']));
?>
<section class="tete-page">
    <div class="conteneur">
        <p class="fil"><a href="<?= e(url('realisations')) ?>"><?= icon('chevron-g') ?><span><?= t('realisations.retour') ?></span></a></p>
        <h1><?= t('photos.titre') ?></h1>
        <p class="tete-page__chapeau"><?= t('photos.chapeau') ?></p>
    </div>
</section>

<section class="section section--serree">
    <div class="conteneur albums">
<?php foreach ($liste as $r):
    $visuels = photos_visionneuse($r);
    $date = date_projet($r);
    $lieu = L($r['lieu'] ?? '');
    $nombre = count($visuels);
    $ancre = 'album-' . $r['slug'];
    $etiquette = etiquette_realisation($r);
?>
        <section class="album" id="<?= e($r['slug']) ?>" aria-labelledby="<?= e($ancre) ?>">
            <div class="album__tete">
<?php if ($etiquette !== ''): ?>
                <p><span class="etiquette etiquette--ligne"><?= e($etiquette) ?></span></p>
<?php endif; ?>
                <h2 class="album__titre" id="<?= e($ancre) ?>"><?= e(L($r['titre'])) ?></h2>
                <p class="album__infos">
<?php if ($date !== ''): ?>
                    <span><?= icon('calendrier') ?><span class="invisible"><?= t('realisations.photo_date') ?></span><?= e($date) ?></span>
<?php endif; ?>
<?php if ($lieu !== ''): ?>
                    <span><?= icon('repere') ?><span class="invisible"><?= t('realisations.photo_lieu') ?></span><?= e($lieu) ?></span>
<?php endif; ?>
                    <span><?= icon('photo') ?><?= $nombre ?> <?= t($nombre > 1 ? 'photos.nombre_plusieurs' : 'photos.nombre_un') ?></span>
                </p>
            </div>
            <ul class="album__photos" data-visionneuse="<?= e(json_encode($visuels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>">
<?php foreach ($r['photos'] as $i => $photo):
    $fichier = $photo['fichier'];
    $estDessin = str_ends_with($fichier, '.svg');
?>
                <li>
                    <a href="<?= e(photo_url($fichier)) ?>" aria-label="<?= t('realisations.agrandir') ?> <?= $i + 1 ?> — <?= e(L($r['titre'])) ?>">
                        <img src="<?= e($estDessin ? photo_url($fichier) : photo_vignette($fichier)) ?>" alt="<?= e($visuels[$i]['alt']) ?>" width="480" height="360" loading="lazy" decoding="async">
<?php if ($visuels[$i]['legende'] !== ''): ?>
                        <span class="etiquette"><?= e($visuels[$i]['legende']) ?></span>
<?php endif; ?>
                    </a>
                </li>
<?php endforeach; ?>
            </ul>
            <p class="album__lien"><a class="lien-fort" href="<?= e(url('realisations') . '/' . $r['slug']) ?>"><?= t('photos.fiche') ?></a></p>
        </section>
<?php endforeach; ?>

<?php if (!$liste): ?>
        <p><?= t('photos.aucune') ?></p>
<?php endif; ?>
<?php if ($aDesDemos): ?>
        <p class="note"><?= t('realisations.note_demo') ?></p>
<?php endif; ?>
    </div>
</section>

<?php if ($liste) {
    partial('visionneuse');
} ?>

<?php partial('appel-final'); ?>
