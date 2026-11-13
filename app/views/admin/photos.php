<?php /** Photos des emplacements fixes du site. Variable : $emplacements (clé => libellé). */ ?>
<h1>Photos du site</h1>
<p class="aide">Tant qu'un emplacement n'a pas de photo, le site affiche un dessin technique. Remplacez-le par une vraie photo de chantier, d'atelier ou d'équipe : prise de jour, bien cadrée, en format paysage. Les photos sont redimensionnées et allégées automatiquement.</p>

<ul class="emplacements">
<?php foreach ($emplacements as $cle => $libelle):
    $photo = emplacement($cle);
    $dessin = is_file(ROOT . "/assets/img/illustrations/$cle.svg") ? asset("img/illustrations/$cle.svg") : null;
?>
    <li class="bloc">
        <h2><?= e($libelle) ?></h2>
<?php if ($photo): ?>
        <img src="<?= e($photo) ?>" alt="Photo actuelle" width="320" height="240" loading="lazy">
<?php elseif ($dessin): ?>
        <img src="<?= e($dessin) ?>" alt="Dessin affiché par défaut" width="320" height="240" loading="lazy">
<?php else: ?>
        <p class="aide">Aucune photo : cet élément n'est pas affiché sur le site.</p>
<?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="emplacement" value="<?= e($cle) ?>">
            <label for="photo-<?= e($cle) ?>"><?= $photo ? 'Remplacer la photo' : 'Choisir une photo' ?></label>
            <input type="file" id="photo-<?= e($cle) ?>" name="photo" accept="image/jpeg,image/png,image/webp" required>
            <button class="btn btn--primaire btn--petit" type="submit">Envoyer</button>
        </form>
<?php if ($photo): ?>
        <form method="post" data-confirmer="Retirer cette photo ?">
            <?= csrf_field() ?>
            <input type="hidden" name="emplacement" value="<?= e($cle) ?>">
            <input type="hidden" name="operation" value="supprimer">
            <button class="btn btn--danger btn--petit" type="submit">Retirer la photo</button>
        </form>
<?php endif; ?>
    </li>
<?php endforeach; ?>
</ul>
