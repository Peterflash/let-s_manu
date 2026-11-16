<?php
/**
 * Galerie des réalisations, filtrable par pôle et par secteur.
 * Les filtres sont de vrais liens (la page fonctionne sans JavaScript) ; site.js les rend instantanés.
 * Variables : $liste, $filtrePole, $filtreSecteur.
 */
$poles = array_keys(config('poles'));
$secteurs = array_keys(config('secteurs'));
if (!in_array($filtrePole, $poles, true)) {
    $filtrePole = '';
}
if (!in_array($filtreSecteur, $secteurs, true)) {
    $filtreSecteur = '';
}
$adresseFiltre = function (string $pole, string $secteur): string {
    $requete = http_build_query(array_filter(['pole' => $pole, 'secteur' => $secteur]));
    return url('realisations') . ($requete !== '' ? '?' . $requete : '');
};
$correspond = fn (array $r) => ($filtrePole === '' || $r['pole'] === $filtrePole) && ($filtreSecteur === '' || $r['secteur'] === $filtreSecteur);
$visibles = count(array_filter($liste, $correspond));
$aDesDemos = (bool) array_filter($liste, fn ($r) => !empty($r['demo']));
?>
<section class="tete-page">
    <div class="conteneur">
        <h1><?= t('realisations.titre') ?></h1>
        <p class="tete-page__chapeau"><?= t('realisations.chapeau') ?></p>
<?php if (array_filter($liste, fn ($r) => !empty($r['photos']))): ?>
        <p class="tete-page__lien"><a class="lien-fort lien-icone" href="<?= e(url('photos')) ?>"><?= icon('photo') ?><span><?= t('realisations.toutes_photos') ?></span></a></p>
<?php endif; ?>
    </div>
</section>

<section class="section section--serree">
    <div class="conteneur" data-galerie data-un="<?= t('realisations.compte_un') ?>" data-plusieurs="<?= t('realisations.compte_plusieurs') ?>">
        <div class="filtres" role="group" aria-label="<?= t('realisations.filtres') ?>">
            <div class="filtres__groupe">
                <h2 class="filtres__titre"><?= t('realisations.filtre_pole') ?></h2>
                <ul>
                    <li><a href="<?= e($adresseFiltre('', $filtreSecteur)) ?>" data-filtre="pole" data-valeur=""<?= $filtrePole === '' ? ' aria-current="true"' : '' ?>><?= t('realisations.tous') ?></a></li>
<?php foreach ($poles as $p): ?>
                    <li><a href="<?= e($adresseFiltre($p, $filtreSecteur)) ?>" data-filtre="pole" data-valeur="<?= e($p) ?>"<?= $filtrePole === $p ? ' aria-current="true"' : '' ?>><?= t("poles.$p") ?></a></li>
<?php endforeach; ?>
                </ul>
            </div>
            <div class="filtres__groupe">
                <h2 class="filtres__titre"><?= t('realisations.filtre_secteur') ?></h2>
                <ul>
                    <li><a href="<?= e($adresseFiltre($filtrePole, '')) ?>" data-filtre="secteur" data-valeur=""<?= $filtreSecteur === '' ? ' aria-current="true"' : '' ?>><?= t('realisations.tous') ?></a></li>
<?php foreach ($secteurs as $s): ?>
                    <li><a href="<?= e($adresseFiltre($filtrePole, $s)) ?>" data-filtre="secteur" data-valeur="<?= e($s) ?>"<?= $filtreSecteur === $s ? ' aria-current="true"' : '' ?>><?= t("secteurs.$s") ?></a></li>
<?php endforeach; ?>
                </ul>
            </div>
        </div>

        <p class="filtres__compte" role="status" data-compte><?= $visibles ?> <?= t($visibles > 1 ? 'realisations.compte_plusieurs' : 'realisations.compte_un') ?></p>

        <div class="fiches">
<?php foreach ($liste as $r) {
    partial('fiche', ['r' => $r, 'niveau' => 2, 'masquee' => !$correspond($r)]);
} ?>
        </div>

        <div class="vide" data-vide<?= $visibles > 0 ? ' hidden' : '' ?>>
            <p><?= t('realisations.aucune') ?></p>
            <a class="btn btn--contour" href="<?= e(url('realisations')) ?>" data-reinitialiser><?= t('realisations.reinitialiser') ?></a>
        </div>

<?php if ($aDesDemos): ?>
        <p class="note"><?= t('realisations.note_demo') ?></p>
<?php endif; ?>
    </div>
</section>

<?php partial('appel-final'); ?>
