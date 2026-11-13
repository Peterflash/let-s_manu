<?php
/** Création ou modification d'une réalisation. Variables : $r, $nouvelle, $erreurs. */
$poles = dot_get(content_raw('fr'), 'poles');
$secteurs = dot_get(content_raw('fr'), 'secteurs');
$champs = [
    'titre'        => ['Titre du projet', 'Ex. : Remplacement d\'une ligne de vapeur', false],
    'client'       => ['Client ou type de client', 'Si le client refuse d\'être nommé : « Usine agroalimentaire », « Site minier »…', false],
    'lieu'         => ['Lieu', 'Ville, commune ou région. Ex. : Zone industrielle de Yopougon, Abidjan', false],
    'intervention' => ["Type d'intervention", 'Ex. : Tuyauterie industrielle', false],
    'duree'        => ['Durée', 'Ex. : 3 jours, pendant un arrêt technique', false],
    'defi'         => ['Le défi', 'Deux ou trois phrases : quel était le problème du client ?', true],
    'solution'     => ['La solution', 'Deux ou trois phrases : qu\'avez-vous fait, et avec quel résultat ?', true],
];
?>
<p class="fil"><a href="<?= e(admin_url('realisations')) ?>"><?= icon('chevron-g') ?><span>Toutes les réalisations</span></a></p>
<h1><?= $nouvelle ? 'Nouvelle réalisation' : e($r['titre']['fr']) ?></h1>

<?php foreach ($erreurs as $message): ?>
<p class="message message--erreur" role="alert"><?= e($message) ?></p>
<?php endforeach; ?>

<form method="post" enctype="multipart/form-data" class="formulaire">
    <?= csrf_field() ?>

    <section class="bloc">
        <h2>Classement</h2>
        <div class="champs-2">
            <div class="champ">
                <label for="pole">Pôle</label>
                <select id="pole" name="pole">
<?php foreach ($poles as $cle => $nom): ?>
                    <option value="<?= e($cle) ?>"<?= $r['pole'] === $cle ? ' selected' : '' ?>><?= e($nom) ?></option>
<?php endforeach; ?>
                </select>
            </div>
            <div class="champ">
                <label for="secteur">Secteur</label>
                <select id="secteur" name="secteur">
<?php foreach ($secteurs as $cle => $nom): ?>
                    <option value="<?= e($cle) ?>"<?= $r['secteur'] === $cle ? ' selected' : '' ?>><?= e($nom) ?></option>
<?php endforeach; ?>
                </select>
            </div>
            <div class="champ">
                <label for="annee">Année des travaux <span class="champ__facultatif">(facultatif)</span></label>
                <input type="text" id="annee" name="annee" inputmode="numeric" maxlength="4" value="<?= e($r['annee']) ?>">
            </div>
            <div class="champ">
                <label for="date">Date de publication</label>
                <input type="text" id="date" name="date" value="<?= e($r['date']) ?>" aria-describedby="aide-date" placeholder="AAAA-MM-JJ">
                <p class="champ__aide" id="aide-date">Format AAAA-MM-JJ. Les plus récentes s'affichent en premier.</p>
            </div>
        </div>
        <div class="champ champ--case">
            <label class="case"><input type="checkbox" name="publie" value="1"<?= !empty($r['publie']) ? ' checked' : '' ?>><span>Publier sur le site</span></label>
        </div>
<?php if (!empty($r['demo'])): ?>
        <div class="champ champ--case">
            <label class="case"><input type="checkbox" name="demo" value="1" checked><span>Projet type (exemple). Décochez si cette fiche décrit désormais un vrai chantier.</span></label>
        </div>
<?php endif; ?>
    </section>

    <section class="bloc">
        <h2>Textes</h2>
        <p class="aide">Le français est obligatoire. Sans traduction anglaise, la version anglaise du site affiche le texte français.</p>
<?php foreach ($champs as $cle => [$libelle, $aide, $long]): ?>
        <fieldset class="bilingue">
            <legend><?= e($libelle) ?></legend>
            <p class="champ__aide"><?= e($aide) ?></p>
            <div class="champs-2">
<?php foreach (['fr' => 'Français', 'en' => 'Anglais'] as $langue => $nomLangue): ?>
                <div class="champ">
                    <label for="<?= $cle . '_' . $langue ?>"><?= $nomLangue ?></label>
<?php if ($long): ?>
                    <textarea id="<?= $cle . '_' . $langue ?>" name="<?= $cle . '_' . $langue ?>" rows="5" lang="<?= $langue ?>"><?= e($r[$cle][$langue] ?? '') ?></textarea>
<?php else: ?>
                    <input type="text" id="<?= $cle . '_' . $langue ?>" name="<?= $cle . '_' . $langue ?>" value="<?= e($r[$cle][$langue] ?? '') ?>" lang="<?= $langue ?>"<?= $cle === 'titre' && $langue === 'fr' ? ' required' : '' ?>>
<?php endif; ?>
                </div>
<?php endforeach; ?>
            </div>
        </fieldset>
<?php endforeach; ?>
    </section>

    <section class="bloc">
        <h2>Photos</h2>
        <p class="aide">De 3 à <?= PHOTOS_PAR_REALISATION ?> photos, prises de jour et bien cadrées. La première sert de vignette. Avant de publier, assurez-vous d'avoir l'accord du client et des personnes visibles.</p>
<?php if ($r['photos']): ?>
        <ul class="photos-edit">
<?php foreach ($r['photos'] as $i => $photo): $fichier = $photo['fichier']; ?>
            <li>
                <img src="<?= e(str_ends_with($fichier, '.svg') ? photo_url($fichier) : photo_vignette($fichier)) ?>" alt="Photo <?= $i + 1 ?>" width="160" height="120" loading="lazy">
                <label for="etiquette-<?= $i ?>">Étiquette</label>
                <select id="etiquette-<?= $i ?>" name="photo_etiquette[<?= $i ?>]">
                    <option value="">Aucune</option>
                    <option value="avant"<?= ($photo['etiquette'] ?? '') === 'avant' ? ' selected' : '' ?>>Avant</option>
                    <option value="apres"<?= ($photo['etiquette'] ?? '') === 'apres' ? ' selected' : '' ?>>Après</option>
                </select>
                <label class="case"><input type="checkbox" name="photo_supprimer[<?= $i ?>]" value="1"><span>Supprimer</span></label>
            </li>
<?php endforeach; ?>
        </ul>
<?php endif; ?>
        <div class="champ">
            <label for="photos">Ajouter des photos</label>
            <div class="depot"><?= icon('photo') ?><input type="file" id="photos" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple></div>
            <p class="champ__aide">JPG, PNG ou WebP. Les photos sont automatiquement redimensionnées et allégées.</p>
        </div>
    </section>

    <div class="actions actions--collantes">
        <button class="btn btn--primaire btn--grand" type="submit">Enregistrer</button>
<?php if (!$nouvelle && !empty($r['publie']) && $r['slug'] !== ''): ?>
        <a class="btn btn--contour" href="<?= e(url('realisations', 'fr') . '/' . $r['slug']) ?>" target="_blank" rel="noopener">Voir la fiche sur le site</a>
<?php endif; ?>
    </div>
</form>

<?php if (!$nouvelle): ?>
<section class="bloc bloc--danger">
    <h2>Supprimer cette réalisation</h2>
    <form method="post" data-confirmer="Supprimer définitivement cette réalisation et ses photos ?"><?= csrf_field() ?><input type="hidden" name="operation" value="supprimer"><button class="btn btn--danger" type="submit">Supprimer</button></form>
</section>
<?php endif; ?>
