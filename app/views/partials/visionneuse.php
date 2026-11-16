<?php
/**
 * Visionneuse plein écran, partagée par la fiche d'une réalisation et par la photothèque.
 * À inclure une seule fois par page. Pilotée par site.js ; sans JavaScript, elle n'est jamais affichée.
 */
?>
<dialog class="visionneuse" aria-label="<?= t('realisations.photos') ?>">
    <button class="visionneuse__fermer" type="button" data-action="fermer" aria-label="<?= t('realisations.fermer') ?>"><?= icon('fermer') ?></button>
    <button class="visionneuse__nav visionneuse__nav--prec" type="button" data-action="precedente" aria-label="<?= t('realisations.precedente') ?>"><?= icon('chevron-g') ?></button>
    <figure>
        <img src="" alt="">
        <figcaption>
            <span class="visionneuse__titre" data-titre></span>
            <span class="visionneuse__legende" data-legende></span>
            <span class="visionneuse__infos">
                <span class="visionneuse__info" data-lieu hidden><?= icon('repere') ?><span class="invisible"><?= t('realisations.photo_lieu') ?></span><span data-texte></span></span>
                <span class="visionneuse__info" data-date hidden><?= icon('calendrier') ?><span class="invisible"><?= t('realisations.photo_date') ?></span><span data-texte></span></span>
            </span>
        </figcaption>
    </figure>
    <button class="visionneuse__nav visionneuse__nav--suiv" type="button" data-action="suivante" aria-label="<?= t('realisations.suivante') ?>"><?= icon('chevron-d') ?></button>
</dialog>
