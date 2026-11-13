<?php
/** Appel à l'action de fin de page : Devis / WhatsApp / Appeler. */
?>
<section class="appel" aria-labelledby="appel-titre">
    <div class="conteneur appel__grille">
        <div>
            <h2 id="appel-titre"><?= t('final.titre') ?></h2>
            <p><?= t('final.texte') ?></p>
        </div>
        <div class="appel__actions">
            <a class="btn btn--fonce" href="<?= e(url('contact')) ?>" data-suivi="devis"><?= t('ui.devis') ?></a>
            <a class="btn btn--contour-fonce" href="<?= e(wa_href((string) tx('ui.wa_general'))) ?>" target="_blank" rel="noopener" data-suivi="whatsapp"><?= icon('whatsapp') ?><span><?= t('ui.whatsapp') ?></span></a>
            <a class="btn btn--contour-fonce" href="<?= e(tel_href()) ?>" data-suivi="appel"><?= icon('telephone') ?><span><?= e(cfg('telephone')) ?></span></a>
        </div>
    </div>
</section>
