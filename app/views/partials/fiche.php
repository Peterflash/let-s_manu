<?php
/**
 * Vignette d'une réalisation. Variables : $r (réalisation), $niveau (niveau du titre, 3 par défaut), $masquee (bool).
 * L'icône photo mène à la photothèque (page « photos »), directement au projet de la vignette.
 */
$niveau ??= 3;
$photo = $r['photos'][0]['fichier'] ?? 'assets/img/illustrations/soudure.svg';
$estDessin = str_ends_with($photo, '.svg');
$adresse = url('realisations') . '/' . $r['slug'];
$titre = L($r['titre']);
$nombrePhotos = count($r['photos'] ?? []);
$etiquette = etiquette_realisation($r);
?>
<article class="fiche<?= $nombrePhotos ? ' fiche--photos' : '' ?>" data-pole="<?= e($r['pole']) ?>" data-secteur="<?= e($r['secteur']) ?>"<?= !empty($masquee) ? ' hidden' : '' ?>>
    <a class="fiche__lien" href="<?= e($adresse) ?>">
        <div class="fiche__image">
            <img src="<?= e($estDessin ? photo_url($photo) : photo_vignette($photo)) ?>" alt="" width="480" height="360" loading="lazy" decoding="async">
<?php if ($etiquette !== ''): ?>
            <span class="etiquette"><?= e($etiquette) ?></span>
<?php endif; ?>
        </div>
        <div class="fiche__corps">
            <h<?= (int) $niveau ?> class="fiche__titre"><?= e($titre) ?></h<?= (int) $niveau ?>>
            <p class="fiche__lieu"><?= e(L($r['client'] ?? '')) ?><?= L($r['client'] ?? '') !== '' && L($r['lieu'] ?? '') !== '' ? ', ' : '' ?><?= e(L($r['lieu'] ?? '')) ?></p>
            <p class="fiche__categories">
                <span><?= t('poles.' . $r['pole']) ?></span>
                <span><?= t('secteurs.' . $r['secteur']) ?></span>
            </p>
        </div>
    </a>
<?php if ($nombrePhotos): ?>
    <a class="fiche__photos" href="<?= e(url('photos') . '#' . $r['slug']) ?>" title="<?= t('realisations.voir_photos') ?>" aria-label="<?= t('realisations.voir_photos') ?> — <?= e($titre) ?> (<?= $nombrePhotos ?>)"><?= icon('photo') ?></a>
<?php endif; ?>
</article>
