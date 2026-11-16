<?php
/**
 * Création ou modification d'une réalisation : un chantier et ses photos. Variables : $r, $nouvelle, $erreurs.
 * Pensé pour le téléphone, sur le chantier : l'essentiel (titre, état, lieu) puis les photos en haut ;
 * la description et la version anglaise, facultatives, sont repliées en bas.
 */
$poles = dot_get(content_raw('fr'), 'poles');
$secteurs = dot_get(content_raw('fr'), 'secteurs');
$champs = [
    'client'       => ['Client ou type de client', 'Si le client refuse d\'être nommé : « Usine agroalimentaire », « Site minier »…', false],
    'intervention' => ["Type d'intervention", 'Ex. : Tuyauterie industrielle', false],
    'duree'        => ['Durée', 'Ex. : 3 jours, pendant un arrêt technique', false],
    'defi'         => ['Le défi', 'Deux ou trois phrases : quel était le problème du client ?', true],
    'solution'     => ['La solution', 'Deux ou trois phrases : qu\'avez-vous fait, et avec quel résultat ?', true],
];
$etiquettes = ['' => 'Aucune', 'avant' => 'Avant', 'pendant' => 'Pendant les travaux', 'apres' => 'Après'];
$restantes = PHOTOS_PAR_REALISATION - count($r['photos']);
$enCours = ($r['etat'] ?? '') === 'en-cours';
// La partie repliée s'ouvre d'elle-même si elle contient déjà quelque chose.
$complements = ($r['titre']['en'] ?? '') . ($r['lieu']['en'] ?? '');
foreach (array_keys($champs) as $cle) {
    $complements .= implode('', (array) ($r[$cle] ?? []));
}
$ouvrir = $erreurs || trim($complements) !== '';
?>
<p class="fil"><a href="<?= e(admin_url('realisations')) ?>"><?= icon('chevron-g') ?><span>Toutes les réalisations</span></a></p>
<h1><?= $nouvelle ? 'Nouveau chantier' : e($r['titre']['fr']) ?></h1>

<?php foreach ($erreurs as $message): ?>
<p class="message message--erreur" role="alert"><?= e($message) ?></p>
<?php endforeach; ?>

<form method="post" enctype="multipart/form-data" class="formulaire">
    <?= csrf_field() ?>

    <section class="bloc">
        <h2>Le chantier</h2>
        <div class="champ">
            <label for="titre_fr">Titre</label>
            <input type="text" id="titre_fr" name="titre_fr" value="<?= e($r['titre']['fr']) ?>" lang="fr" maxlength="140" required aria-describedby="aide-titre">
            <p class="champ__aide" id="aide-titre">Ce que vous avez fait, en quelques mots. Ex. : Remplacement d'une ligne de vapeur</p>
        </div>
        <fieldset class="champ">
            <legend class="champ__libelle">État du chantier</legend>
            <div class="choix">
                <label class="choix__option"><input type="radio" name="etat" value="termine"<?= $enCours ? '' : ' checked' ?>><span>Terminé</span></label>
                <label class="choix__option"><input type="radio" name="etat" value="en-cours"<?= $enCours ? ' checked' : '' ?>><span>En cours</span></label>
            </div>
            <p class="champ__aide">« En cours » affiche l'étiquette « Chantier en cours » sur le site. Ajoutez les photos au fil des travaux, puis passez sur « Terminé » à la fin.</p>
        </fieldset>
        <div class="champs-2">
            <div class="champ">
                <label for="lieu_fr">Lieu <span class="champ__facultatif">(facultatif)</span></label>
                <input type="text" id="lieu_fr" name="lieu_fr" value="<?= e($r['lieu']['fr']) ?>" lang="fr" maxlength="140" aria-describedby="aide-lieu">
                <p class="champ__aide" id="aide-lieu">Ville, commune ou zone. Ex. : Zone industrielle de Yopougon, Abidjan</p>
            </div>
            <div class="champ">
                <label for="annee">Année des travaux <span class="champ__facultatif">(facultatif)</span></label>
                <input type="text" id="annee" name="annee" inputmode="numeric" maxlength="4" value="<?= e($r['annee']) ?>">
            </div>
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
        </div>
        <div class="champ champ--case">
            <label class="case"><input type="checkbox" name="publie" value="1"<?= !empty($r['publie']) ? ' checked' : '' ?> aria-describedby="aide-publie"><span>Publier sur le site</span></label>
            <p class="champ__aide" id="aide-publie">Décochez pour préparer la fiche sans la montrer : chantier à venir, ou accord du client pas encore obtenu.</p>
        </div>
<?php if (!empty($r['demo'])): ?>
        <div class="champ champ--case">
            <label class="case"><input type="checkbox" name="demo" value="1" checked><span>Projet type (exemple). Décochez si cette fiche décrit désormais un vrai chantier.</span></label>
        </div>
<?php endif; ?>
    </section>

    <section class="bloc" aria-labelledby="titre-photos">
        <h2 id="titre-photos">Photos <span class="champ__facultatif">(<?= count($r['photos']) ?> sur <?= PHOTOS_PAR_REALISATION ?>)</span></h2>
        <p class="aide">Avant, pendant et après les travaux, prises de jour et bien cadrées. Avant de publier, assurez-vous d'avoir l'accord du client et des personnes visibles.</p>
<?php if ($restantes > 0): ?>
        <div class="champ">
            <label for="photos">Ajouter des photos</label>
            <div class="depot"><?= icon('photo') ?><input type="file" id="photos" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple data-reduire="<?= $restantes ?>"></div>
            <p class="champ__aide" data-reduire-statut aria-live="polite">Prenez la photo ou choisissez-la dans la galerie du téléphone, plusieurs à la fois si vous voulez. Elles sont allégées avant l'envoi pour économiser vos données.</p>
            <ul class="depot__apercus" data-reduire-apercus></ul>
            <div data-reduire-dates></div>
        </div>
<?php else: ?>
        <p class="message message--info">Limite de <?= PHOTOS_PAR_REALISATION ?> photos atteinte : supprimez-en une pour en ajouter une autre.</p>
<?php endif; ?>

<?php if ($r['photos']): ?>
        <h3>Photos enregistrées</h3>
        <p class="aide">La photo principale sert de vignette sur le site. Le lieu et la date s'affichent sous chaque photo agrandie. La date est lue dans la photo quand le téléphone l'a enregistrée : vérifiez-la. Lieu vide : celui du chantier. Date vide : l'année des travaux.</p>
        <ul class="photos-edit">
<?php foreach ($r['photos'] as $i => $photo): $fichier = $photo['fichier']; ?>
            <li>
                <img src="<?= e(str_ends_with($fichier, '.svg') ? photo_url($fichier) : photo_vignette($fichier)) ?>" alt="Photo <?= $i + 1 ?>" width="160" height="120" loading="lazy">
                <label class="case"><input type="radio" name="photo_principale" value="<?= $i ?>"<?= $i === 0 ? ' checked' : '' ?>><span>Photo principale</span></label>
                <label for="etiquette-<?= $i ?>">Étiquette</label>
                <select id="etiquette-<?= $i ?>" name="photo_etiquette[<?= $i ?>]">
<?php foreach ($etiquettes as $valeur => $libelle): ?>
                    <option value="<?= e($valeur) ?>"<?= ($photo['etiquette'] ?? '') === $valeur ? ' selected' : '' ?>><?= e($libelle) ?></option>
<?php endforeach; ?>
                </select>
                <label for="photo-lieu-<?= $i ?>">Lieu de la prise de vue</label>
                <input type="text" id="photo-lieu-<?= $i ?>" name="photo_lieu[<?= $i ?>]" maxlength="140" value="<?= e($photo['lieu'] ?? '') ?>" placeholder="<?= e($r['lieu']['fr'] !== '' ? $r['lieu']['fr'] : 'Lieu du chantier') ?>">
                <label for="photo-date-<?= $i ?>">Date de la prise de vue</label>
                <input type="date" id="photo-date-<?= $i ?>" name="photo_date[<?= $i ?>]" value="<?= e($photo['date'] ?? '') ?>" max="<?= date('Y-m-d') ?>">
                <label class="case"><input type="checkbox" name="photo_supprimer[<?= $i ?>]" value="1"><span>Supprimer</span></label>
            </li>
<?php endforeach; ?>
        </ul>
<?php endif; ?>
    </section>

    <details class="bloc bloc--repli"<?= $ouvrir ? ' open' : '' ?>>
        <summary><h2>Description et version anglaise</h2> <span class="champ__facultatif">(facultatif)</span></summary>
        <p class="aide">Deux phrases sur le défi et la solution rendent la fiche plus convaincante pour un service achats. Sans traduction anglaise, la version anglaise du site affiche le texte français.</p>
        <fieldset class="bilingue">
            <legend>Titre et lieu en anglais</legend>
            <div class="champs-2">
                <div class="champ">
                    <label for="titre_en">Titre (anglais)</label>
                    <input type="text" id="titre_en" name="titre_en" value="<?= e($r['titre']['en'] ?? '') ?>" lang="en" maxlength="140">
                </div>
                <div class="champ">
                    <label for="lieu_en">Lieu (anglais)</label>
                    <input type="text" id="lieu_en" name="lieu_en" value="<?= e($r['lieu']['en'] ?? '') ?>" lang="en" maxlength="140">
                </div>
            </div>
        </fieldset>
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
                    <input type="text" id="<?= $cle . '_' . $langue ?>" name="<?= $cle . '_' . $langue ?>" value="<?= e($r[$cle][$langue] ?? '') ?>" lang="<?= $langue ?>" maxlength="140">
<?php endif; ?>
                </div>
<?php endforeach; ?>
            </div>
        </fieldset>
<?php endforeach; ?>
        <div class="champ">
            <label for="date">Date de publication</label>
            <input type="text" id="date" name="date" value="<?= e($r['date']) ?>" aria-describedby="aide-date" placeholder="AAAA-MM-JJ">
            <p class="champ__aide" id="aide-date">Format AAAA-MM-JJ. Les réalisations les plus récentes s'affichent en premier.</p>
        </div>
    </details>

    <div class="actions actions--collantes">
        <button class="btn btn--primaire btn--grand" type="submit" data-envoi="Envoi en cours…">Enregistrer</button>
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
