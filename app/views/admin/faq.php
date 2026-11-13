<?php
/** Questions de l'assistant (réponses rédigées à l'avance). Variable : $questions. */
$ligne = function (int|string $i, array $q): void { ?>
    <fieldset class="bloc question">
        <legend>Question</legend>
        <div class="champs-2">
            <div class="champ"><label>Question (français)<input type="text" name="q_fr[<?= $i ?>]" value="<?= e($q['q']['fr'] ?? '') ?>" lang="fr"></label></div>
            <div class="champ"><label>Question (anglais)<input type="text" name="q_en[<?= $i ?>]" value="<?= e($q['q']['en'] ?? '') ?>" lang="en"></label></div>
            <div class="champ"><label>Réponse (français)<textarea name="r_fr[<?= $i ?>]" rows="4" lang="fr"><?= e($q['r']['fr'] ?? '') ?></textarea></label></div>
            <div class="champ"><label>Réponse (anglais)<textarea name="r_en[<?= $i ?>]" rows="4" lang="en"><?= e($q['r']['en'] ?? '') ?></textarea></label></div>
        </div>
        <button class="btn btn--danger btn--petit" type="button" data-retirer-question>Retirer cette question</button>
    </fieldset>
<?php };
?>
<h1>Questions rapides</h1>
<p class="aide">Ces questions apparaissent dans la bulle « Questions rapides » du site. Gardez des réponses courtes : deux ou trois phrases. Une question sans réponse en français n'est pas enregistrée.</p>

<form method="post" class="formulaire">
    <?= csrf_field() ?>
    <div data-questions>
<?php foreach ($questions as $i => $q) { $ligne($i, $q); } ?>
    </div>
    <template data-modele><?php $ligne('__i__', []); ?></template>
    <div class="actions actions--collantes">
        <button class="btn btn--contour" type="button" data-ajouter-question>Ajouter une question</button>
        <button class="btn btn--primaire btn--grand" type="submit">Enregistrer</button>
    </div>
</form>
