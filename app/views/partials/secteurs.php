<?php /** Grille des secteurs servis. */ ?>
<ul class="secteurs">
<?php foreach (config('secteurs') as $cle => $icone): ?>
    <li><?= icon($icone) ?><span><?= t("secteurs.$cle") ?></span></li>
<?php endforeach; ?>
</ul>
