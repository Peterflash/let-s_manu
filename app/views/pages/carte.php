<?php
/**
 * Carte de visite numérique : la page qu'ouvre le QR code.
 * Coordonnées lues dans les réglages (admin > Paramètres), fiche contact .vcf à enregistrer.
 */
$slug = ROUTES[lang()]['carte'];
$fichier = fn (string $extension): string => (lang() === 'en' ? BASE . '/en/' : BASE . '/') . $slug . '.' . $extension;
$site = preg_replace('#^https?://#', '', rtrim(abs_url(url('home')), '/'));
$carteRequete = (cfg('gps_lat') !== '' && cfg('gps_lng') !== '') ? cfg('gps_lat') . ',' . cfg('gps_lng') : cfg('carte_recherche');
$carteLien = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($carteRequete);
?>
<section class="section section--serree">
    <div class="conteneur cv">
        <div class="cv__principal">
            <h1 class="cv__titre"><?= t('carte.titre') ?></h1>
            <p class="tete-page__chapeau"><?= t('carte.chapeau') ?></p>

            <article class="cv-carte" aria-labelledby="cv-nom">
                <div class="cv-carte__tete">
                    <?= logo('sombre', 220) ?>
                    <h2 class="invisible" id="cv-nom"><?= e(cfg('raison_sociale')) ?></h2>
                    <p class="cv-carte__activite"><?= t('carte.activite') ?></p>
                </div>
                <ul class="cv-carte__lignes">
                    <li><a href="<?= e(tel_href()) ?>" data-suivi="appel"><?= icon('telephone') ?><span><small><?= t('carte.telephone') ?></small>+225 <?= e(cfg('telephone')) ?></span></a></li>
                    <li><a href="<?= e(wa_href((string) tx('ui.wa_general'))) ?>" target="_blank" rel="noopener" data-suivi="whatsapp"><?= icon('whatsapp') ?><span><small><?= t('carte.whatsapp') ?></small>+225 <?= e(cfg('telephone')) ?></span></a></li>
                    <li><a href="mailto:<?= e(cfg('email')) ?>"><?= icon('email') ?><span><small><?= t('carte.email') ?></small><?= e(cfg('email')) ?></span></a></li>
                    <li><a href="<?= e($carteLien) ?>" target="_blank" rel="noopener"><?= icon('repere') ?><span><small><?= t('carte.adresse') ?></small><?= e(cfg('ville')) ?>, <?= e(cfg_l('adresse')) ?></span></a></li>
                    <li><a href="<?= e(url('home')) ?>"><?= icon('fleche') ?><span><small><?= t('carte.site') ?></small><?= e($site) ?></span></a></li>
                </ul>
                <p class="cv-carte__legal"><?= e(cfg('forme_juridique')) ?> · IDU <?= e(cfg('idu')) ?></p>
            </article>

            <div class="cv__actions">
                <a class="btn btn--primaire btn--grand btn--bloc" href="<?= e($fichier('vcf')) ?>"><?= icon('telecharger') ?><span><?= t('carte.enregistrer') ?></span></a>
                <p class="champ__aide"><?= t('carte.enregistrer_aide') ?></p>
                <div class="cv__boutons">
                    <a class="btn btn--fonce" href="<?= e(tel_href()) ?>" data-suivi="appel"><?= icon('telephone') ?><span><?= t('ui.appeler') ?></span></a>
                    <a class="btn btn--contour" href="<?= e(wa_href((string) tx('ui.wa_general'))) ?>" target="_blank" rel="noopener" data-suivi="whatsapp"><?= icon('whatsapp') ?><span><?= t('ui.whatsapp') ?></span></a>
                    <a class="btn btn--contour" href="<?= e(url('contact')) ?>" data-suivi="devis"><?= icon('document') ?><span><?= t('ui.devis') ?></span></a>
                </div>
            </div>
        </div>

        <aside class="cv__partage" aria-labelledby="cv-partage">
            <h2 id="cv-partage"><?= t('carte.qr_titre') ?></h2>
            <div class="qr-cadre"><?= qr_svg(carte_adresse(), (string) tx('carte.qr_alt')) ?></div>
            <p><?= t('carte.qr_texte') ?></p>
            <p><a class="lien-icone lien-fort" href="<?= e($fichier('png')) ?>" download><?= icon('telecharger') ?><span><?= t('carte.qr_telecharger') ?></span></a></p>
        </aside>
    </div>
</section>
