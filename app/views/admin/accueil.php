<?php
/** Tableau de bord. Variables : $stats, $nouvelles, $dernieres, $publiees, $aFaire. */
$t = $stats['totaux'];
?>
<h1>Tableau de bord</h1>

<section class="bloc" aria-labelledby="titre-activite">
    <h2 id="titre-activite">Les 30 derniers jours</h2>
    <dl class="compteurs">
        <div><dt>Demandes de devis reçues</dt><dd><?= $t['demande'] ?></dd></div>
        <div><dt>Clics sur WhatsApp</dt><dd><?= $t['whatsapp'] ?></dd></div>
        <div><dt>Clics sur Appeler</dt><dd><?= $t['appel'] ?></dd></div>
        <div><dt>Clics sur Devis</dt><dd><?= $t['devis'] ?></dd></div>
        <div><dt>Téléchargements de la plaquette</dt><dd><?= $t['plaquette'] ?></dd></div>
        <div><dt>Fiches contact enregistrées (carte de visite)</dt><dd><?= $t['contact'] ?></dd></div>
        <div><dt>Pages vues</dt><dd><?= $t['vues'] ?></dd></div>
    </dl>
    <p class="aide">Comptage interne au site, sans cookie. Les robots des moteurs de recherche ne sont pas comptés.</p>
</section>

<section class="bloc" aria-labelledby="titre-demandes">
    <h2 id="titre-demandes">Demandes de devis<?php if ($nouvelles > 0): ?> <span class="pastille"><?= $nouvelles ?> à traiter</span><?php endif; ?></h2>
<?php if ($dernieres): ?>
    <ul class="lignes">
<?php foreach ($dernieres as $d): ?>
        <li>
            <a href="<?= e(admin_url('demandes/' . $d['ref'])) ?>">
                <strong><?= e($d['nom']) ?><?= $d['entreprise'] !== '' ? ' — ' . e($d['entreprise']) : '' ?></strong>
                <span><?= e($d['service_libelle']) ?>, <?= e($d['lieu']) ?></span>
                <span class="lignes__meta"><?= e(date_longue($d['date'])) ?><?= !empty($d['urgence']) ? ' <b class="urgent">Urgent</b>' : '' ?><?= ($d['statut'] ?? '') === 'nouvelle' ? ' <b class="nouveau">Nouvelle</b>' : '' ?></span>
            </a>
        </li>
<?php endforeach; ?>
    </ul>
    <p><a class="lien-fort" href="<?= e(admin_url('demandes')) ?>">Toutes les demandes</a></p>
<?php else: ?>
    <p class="aide">Aucune demande pour l'instant. Elles apparaîtront ici dès qu'un visiteur enverra le formulaire de devis.</p>
<?php endif; ?>
</section>

<?php if ($aFaire): ?>
<section class="bloc" aria-labelledby="titre-afaire">
    <h2 id="titre-afaire">À compléter avant la mise en ligne</h2>
    <ul class="lignes">
<?php foreach ($aFaire as [$libelle, $lien]): ?>
        <li><a href="<?= e($lien) ?>"><span><?= e($libelle) ?></span></a></li>
<?php endforeach; ?>
    </ul>
</section>
<?php endif; ?>

<section class="bloc" aria-labelledby="titre-raccourcis">
    <h2 id="titre-raccourcis">Raccourcis</h2>
    <div class="actions">
        <a class="btn btn--primaire" href="<?= e(admin_url('realisations/nouvelle')) ?>">Ajouter une réalisation</a>
        <a class="btn btn--contour" href="<?= e(admin_url('textes')) ?>">Modifier un texte</a>
        <a class="btn btn--contour" href="<?= e(admin_url('photos')) ?>">Changer une photo</a>
    </div>
    <p class="aide"><?= $publiees ?> réalisation(s) publiée(s) sur le site. Une nouvelle réalisation par mois fait vivre le site et améliore sa position dans Google.</p>
</section>
