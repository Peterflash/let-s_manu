<?php
/** Connexion, ou création du compte à la première utilisation. Variables : $mode, $erreur. */
$installation = $mode === 'installation';
?>
<div class="acces">
    <h1><?= $installation ? 'Créer le compte administrateur' : 'Connexion' ?></h1>
<?php if ($installation): ?>
    <p>Première utilisation : choisissez l'identifiant et le mot de passe qui protégeront l'administration du site. Cette étape n'est possible qu'une seule fois.</p>
<?php endif; ?>
<?php if ($erreur !== ''): ?>
    <p class="message message--erreur" role="alert"><?= e($erreur) ?></p>
<?php endif; ?>
    <form method="post" class="formulaire">
        <?= csrf_field() ?>
        <div class="champ">
            <label for="identifiant">Identifiant</label>
            <input type="text" id="identifiant" name="identifiant" autocomplete="username" required value="<?= e((string) ($_POST['identifiant'] ?? '')) ?>">
        </div>
        <div class="champ">
            <label for="mot_de_passe">Mot de passe</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" autocomplete="<?= $installation ? 'new-password' : 'current-password' ?>" required<?= $installation ? ' minlength="10" aria-describedby="aide-mdp"' : '' ?>>
<?php if ($installation): ?>
            <p class="champ__aide" id="aide-mdp">10 caractères au moins. Une phrase facile à retenir vaut mieux qu'un mot compliqué.</p>
<?php endif; ?>
        </div>
<?php if ($installation): ?>
        <div class="champ">
            <label for="confirmation">Confirmez le mot de passe</label>
            <input type="password" id="confirmation" name="confirmation" autocomplete="new-password" required minlength="10">
        </div>
<?php endif; ?>
        <button class="btn btn--primaire btn--bloc" type="submit"><?= $installation ? 'Créer le compte' : 'Se connecter' ?></button>
    </form>
</div>
