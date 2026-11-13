<?php
/** Détail d'une demande de devis. Variable : $d. */
$poles = dot_get(content_raw('fr'), 'poles');
$numero = ltrim($d['telephone'], '+');
$nouvelle = ($d['statut'] ?? '') === 'nouvelle';
?>
<p class="fil"><a href="<?= e(admin_url('demandes')) ?>"><?= icon('chevron-g') ?><span>Toutes les demandes</span></a></p>
<h1>Demande <?= e($d['ref']) ?></h1>
<p class="lignes__meta"><?= e(date_longue($d['date'])) ?> <?= !empty($d['urgence']) ? '<b class="urgent">Urgent</b>' : '' ?> <?= $nouvelle ? '<b class="nouveau">Nouvelle</b>' : '<b class="traite">Traitée</b>' ?></p>

<section class="bloc">
    <h2><?= e($d['nom']) ?><?= $d['entreprise'] !== '' ? ' — ' . e($d['entreprise']) : '' ?></h2>
    <div class="actions">
        <a class="btn btn--fonce" href="tel:<?= e($d['telephone']) ?>"><?= icon('telephone') ?><span>Appeler <?= e($d['telephone']) ?></span></a>
        <a class="btn btn--contour" href="https://wa.me/<?= e($numero) ?>?text=<?= rawurlencode('Bonjour ' . $d['nom'] . ", nous avons bien reçu votre demande de devis " . $d['ref'] . '.') ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?><span>WhatsApp</span></a>
<?php if ($d['email'] !== ''): ?>
        <a class="btn btn--contour" href="mailto:<?= e($d['email']) ?>?subject=<?= rawurlencode('Votre demande de devis ' . $d['ref']) ?>"><?= icon('email') ?><span><?= e($d['email']) ?></span></a>
<?php endif; ?>
    </div>
    <dl class="details">
        <div><dt>Pôle</dt><dd><?= e($poles[$d['pole']] ?? $d['pole']) ?></dd></div>
        <div><dt>Service</dt><dd><?= e($d['service_libelle']) ?></dd></div>
        <div><dt>Lieu d'intervention</dt><dd><?= e($d['lieu']) ?></dd></div>
        <div><dt>Urgence</dt><dd><?= !empty($d['urgence']) ? 'Oui' : 'Non' ?></dd></div>
        <div><dt>Langue du visiteur</dt><dd><?= ($d['langue'] ?? 'fr') === 'en' ? 'Anglais' : 'Français' ?></dd></div>
    </dl>
    <h3>Description</h3>
    <div class="description"><?= paras($d['description']) ?></div>

<?php if ($d['photos']): ?>
    <h3>Photos jointes</h3>
    <ul class="vignettes">
<?php foreach ($d['photos'] as $photo): ?>
        <li><a href="<?= e(admin_url('media/' . $d['ref'] . '/' . $photo)) ?>" target="_blank" rel="noopener"><img src="<?= e(admin_url('media/' . $d['ref'] . '/' . $photo)) ?>" alt="Photo jointe à la demande" loading="lazy"></a></li>
<?php endforeach; ?>
    </ul>
<?php endif; ?>
</section>

<section class="bloc">
    <h2>Suivi</h2>
    <div class="actions">
        <form method="post"><?= csrf_field() ?><input type="hidden" name="operation" value="<?= $nouvelle ? 'traitee' : 'nouvelle' ?>"><button class="btn btn--primaire" type="submit"><?= $nouvelle ? 'Marquer comme traitée' : 'Remettre en « nouvelle »' ?></button></form>
        <form method="post" data-confirmer="Supprimer définitivement cette demande et ses photos ?"><?= csrf_field() ?><input type="hidden" name="operation" value="supprimer"><button class="btn btn--danger" type="submit">Supprimer la demande</button></form>
    </div>
</section>
