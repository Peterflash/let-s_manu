<?php
/** Liste d'étapes numérotées (c'est une vraie séquence). Variable : $cle — préfixe des textes, ex. 'home.methode'. */
?>
<ol class="etapes">
<?php foreach (['a', 'b', 'c', 'd'] as $etape): ?>
    <li class="etapes__item">
        <h3><?= t("$cle.$etape.titre") ?></h3>
        <p><?= t("$cle.$etape.texte") ?></p>
    </li>
<?php endforeach; ?>
</ol>
