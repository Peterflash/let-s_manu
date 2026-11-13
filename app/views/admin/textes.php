<?php
/**
 * Modification des textes du site, rubrique par rubrique.
 * Variables : $sections, $langue, $sectionTextes, $defauts (clé => texte d'origine), $modifications.
 */
$lien = fn (string $l, string $s) => admin_url('textes') . '?langue=' . $l . '&section=' . $s;
?>
<h1>Textes du site</h1>
<p class="aide">Choisissez une rubrique, modifiez le texte, puis enregistrez. Pour une liste à puces, écrivez un élément par ligne. Pour revenir au texte d'origine, videz le champ.</p>

<div class="onglets" role="group" aria-label="Langue">
    <a href="<?= e($lien('fr', $sectionTextes)) ?>"<?= $langue === 'fr' ? ' aria-current="true"' : '' ?>>Français</a>
    <a href="<?= e($lien('en', $sectionTextes)) ?>"<?= $langue === 'en' ? ' aria-current="true"' : '' ?>>Anglais</a>
</div>

<nav class="rubriques" aria-label="Rubriques">
    <ul>
<?php foreach ($sections as $cle => $nom): ?>
        <li><a href="<?= e($lien($langue, $cle)) ?>"<?= $cle === $sectionTextes ? ' aria-current="true"' : '' ?>><?= e($nom) ?></a></li>
<?php endforeach; ?>
    </ul>
</nav>

<form method="post" class="formulaire">
    <?= csrf_field() ?>
    <section class="bloc">
        <h2><?= e($sections[$sectionTextes]) ?> — <?= $langue === 'fr' ? 'français' : 'anglais' ?></h2>
<?php foreach ($defauts as $cle => $defaut):
    $champ = str_replace('.', '__', $cle);
    $valeur = $modifications[$cle] ?? $defaut;
    $modifie = isset($modifications[$cle]);
    $lignes = max(1, substr_count($valeur, "\n") + 1, (int) ceil(mb_strlen($valeur) / 80));
    $repere = implode(' › ', array_slice(explode('.', $cle), 1));
?>
        <div class="champ champ--texte">
            <label for="t-<?= e($champ) ?>"><?= e($repere) ?><?= $modifie ? ' <b class="nouveau">Modifié</b>' : '' ?></label>
            <textarea id="t-<?= e($champ) ?>" name="t[<?= e($champ) ?>]" rows="<?= min(12, $lignes) ?>" lang="<?= $langue ?>"<?= $defaut === '' ? ' placeholder="Vide : la rubrique n\'est pas affichée sur le site"' : '' ?>><?= e($valeur) ?></textarea>
        </div>
<?php endforeach; ?>
    </section>
    <div class="actions actions--collantes">
        <button class="btn btn--primaire btn--grand" type="submit">Enregistrer les textes</button>
    </div>
</form>
