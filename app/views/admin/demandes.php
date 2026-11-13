<?php /** Liste des demandes de devis. Variable : $demandes. */ ?>
<h1>Demandes de devis</h1>
<?php if (!$demandes): ?>
<p class="aide">Aucune demande pour l'instant.</p>
<?php else: ?>
<ul class="lignes">
<?php foreach ($demandes as $d): ?>
    <li class="<?= ($d['statut'] ?? '') === 'nouvelle' ? 'est-nouvelle' : '' ?>">
        <a href="<?= e(admin_url('demandes/' . $d['ref'])) ?>">
            <strong><?= e($d['nom']) ?><?= $d['entreprise'] !== '' ? ' — ' . e($d['entreprise']) : '' ?></strong>
            <span><?= e($d['service_libelle']) ?>, <?= e($d['lieu']) ?><?= $d['photos'] ? ' · ' . count($d['photos']) . ' photo(s)' : '' ?></span>
            <span class="lignes__meta"><?= e($d['ref']) ?>, <?= e(date_longue($d['date'])) ?><?= !empty($d['urgence']) ? ' <b class="urgent">Urgent</b>' : '' ?> <?= ($d['statut'] ?? '') === 'nouvelle' ? '<b class="nouveau">Nouvelle</b>' : '<b class="traite">Traitée</b>' ?></span>
        </a>
    </li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
