<?php
/** Liste des réalisations. Variable : $liste. */
$poles = dot_get(content_raw('fr'), 'poles');
$secteurs = dot_get(content_raw('fr'), 'secteurs');
$demos = count(array_filter($liste, fn ($r) => !empty($r['demo'])));
?>
<h1>Réalisations</h1>
<div class="actions">
    <a class="btn btn--primaire" href="<?= e(admin_url('realisations/nouvelle')) ?>">Ajouter un chantier</a>
</div>
<p class="aide">Chaque chantier devient une fiche « Réalisation » sur le site, avec ses photos (<?= PHOTOS_PAR_REALISATION ?> au plus). Un chantier en cours peut être publié dès les premières photos et complété au fil des travaux.</p>

<?php if ($demos > 0): ?>
<div class="message message--info">
    <p><strong><?= $demos ?> projet(s) type(s)</strong> ont été fournis avec le site pour montrer la mise en page. Ils décrivent des prestations courantes, pas de vrais chantiers : remplacez-les par vos réalisations, puis supprimez-les.</p>
    <form method="post" data-confirmer="Supprimer tous les projets types ?"><?= csrf_field() ?><input type="hidden" name="operation" value="supprimer-demos"><button class="btn btn--contour btn--petit" type="submit">Supprimer les projets types</button></form>
</div>
<?php endif; ?>

<?php if (!$liste): ?>
<p class="aide">Aucune réalisation. Ajoutez votre premier chantier : titre, lieu, quelques photos, et deux phrases sur le défi et la solution.</p>
<?php else: ?>
<ul class="lignes lignes--images">
<?php foreach ($liste as $r): $photo = $r['photos'][0]['fichier'] ?? ''; ?>
    <li>
        <a href="<?= e(admin_url('realisations/' . $r['id'])) ?>">
<?php if ($photo !== ''): ?>
            <img src="<?= e(str_ends_with($photo, '.svg') ? photo_url($photo) : photo_vignette($photo)) ?>" alt="" width="96" height="72" loading="lazy">
<?php endif; ?>
            <span>
                <strong><?= e($r['titre']['fr']) ?></strong>
                <span><?= e($poles[$r['pole']] ?? '') ?>, <?= e($secteurs[$r['secteur']] ?? '') ?> · <?= count($r['photos']) ?> photo(s)</span>
                <span class="lignes__meta"><?= empty($r['publie']) ? '<b class="traite">Non publiée</b>' : '<b class="nouveau">Publiée</b>' ?><?= ($r['etat'] ?? '') === 'en-cours' ? ' <b class="en-cours">En cours</b>' : '' ?><?= !empty($r['demo']) ? ' <b class="urgent">Projet type</b>' : '' ?><?= $r['titre']['en'] === '' ? ' <b class="traite">Sans traduction anglaise</b>' : '' ?></span>
            </span>
        </a>
    </li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
