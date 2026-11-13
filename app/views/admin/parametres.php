<?php
/** Paramètres du site. Variable : $defauts (réglages par défaut de config.php). */
$groupes = [
    'Coordonnées' => [
        'telephone'        => ['Téléphone affiché', 'Ex. : 07 47 31 41 39'],
        'telephone_intl'   => ['Téléphone au format international', 'Sert au bouton Appeler. Ex. : +2250747314139'],
        'whatsapp'         => ['Numéro WhatsApp', 'Indicatif puis numéro, sans + ni espace. Ex. : 2250747314139'],
        'email'            => ['E-mail affiché sur le site', ''],
        'email_devis'      => ['E-mail qui reçoit les demandes de devis', ''],
        'email_expediteur' => ["E-mail d'expédition des messages du site", 'Une adresse du domaine, ex. no-reply@letsmanu.ci. Laissez vide si vous ne savez pas.'],
    ],
    'Adresse et carte' => [
        'ville'           => ['Ville', ''],
        'adresse_fr'      => ['Adresse et repère (français)', ''],
        'adresse_en'      => ['Adresse et repère (anglais)', ''],
        'gps_lat'         => ['Latitude GPS du siège', 'Dans Google Maps, clic droit sur le point exact : le premier nombre. Ex. : 5.2987'],
        'gps_lng'         => ['Longitude GPS du siège', 'Le second nombre. Ex. : -3.9521'],
        'carte_recherche' => ['Recherche Google Maps de secours', 'Utilisée tant que les coordonnées GPS ne sont pas renseignées.'],
    ],
    'Horaires' => [
        'horaires_fr' => ['Horaires (français)', 'Une ligne par plage.'],
        'horaires_en' => ['Horaires (anglais)', ''],
    ],
    'Identité légale' => [
        'raison_sociale'  => ['Raison sociale', ''],
        'nom_court'       => ['Nom court (logo)', ''],
        'forme_juridique' => ['Forme juridique', ''],
        'annee_creation'  => ['Année de création', ''],
        'idu'             => ['IDU', ''],
        'gerante'         => ['Gérante', ''],
    ],
    'Réseaux sociaux' => [
        'linkedin' => ['Page LinkedIn', 'Adresse complète. Laissez vide pour ne pas afficher le lien.'],
        'facebook' => ['Page Facebook', ''],
    ],
    'Référencement et statistiques' => [
        'site_url' => ['Adresse officielle du site', 'Ex. : https://letsmanu.ci'],
        'ga4_id'   => ['Identifiant Google Analytics 4', 'Ex. : G-XXXXXXXXXX. Facultatif : le site compte déjà les visites et les clics.'],
    ],
    'Protection anti-spam (facultatif)' => [
        'turnstile_site'   => ['Cloudflare Turnstile — clé du site', 'Le formulaire est déjà protégé ; à activer seulement si du spam passe.'],
        'turnstile_secret' => ['Cloudflare Turnstile — clé secrète', ''],
    ],
    'Hébergeur (mentions légales)' => [
        'hebergeur_nom'     => ['Nom', ''],
        'hebergeur_adresse' => ['Adresse', ''],
        'hebergeur_site'    => ['Site web', ''],
    ],
];
$multilignes = ['horaires_fr', 'horaires_en'];
$plaquettes = ['fr' => 'Plaquette en français', 'en' => 'Plaquette en anglais'];
?>
<h1>Paramètres</h1>

<form method="post" class="formulaire">
    <?= csrf_field() ?>
    <input type="hidden" name="operation" value="reglages">
<?php foreach ($groupes as $titreGroupe => $champs): ?>
    <section class="bloc">
        <h2><?= e($titreGroupe) ?></h2>
        <div class="champs-2">
<?php foreach ($champs as $cle => [$libelle, $aide]): ?>
            <div class="champ">
                <label for="r-<?= e($cle) ?>"><?= e($libelle) ?></label>
<?php if (in_array($cle, $multilignes, true)): ?>
                <textarea id="r-<?= e($cle) ?>" name="r[<?= e($cle) ?>]" rows="3"><?= e(cfg($cle)) ?></textarea>
<?php else: ?>
                <input type="text" id="r-<?= e($cle) ?>" name="r[<?= e($cle) ?>]" value="<?= e(cfg($cle)) ?>"<?= $cle === 'turnstile_secret' ? ' autocomplete="off"' : '' ?>>
<?php endif; ?>
<?php if ($aide !== ''): ?>
                <p class="champ__aide"><?= e($aide) ?></p>
<?php endif; ?>
            </div>
<?php endforeach; ?>
        </div>
    </section>
<?php endforeach; ?>
    <div class="actions actions--collantes">
        <button class="btn btn--primaire btn--grand" type="submit">Enregistrer les paramètres</button>
    </div>
</form>

<section class="bloc">
    <h2>Plaquette PDF</h2>
    <p class="aide">Le site propose par défaut une plaquette générée à partir de ses propres textes. Vous pouvez la remplacer par votre document.</p>
<?php foreach ($plaquettes as $langue => $libelle):
    $personnalisee = is_file(UPLOADS . '/site/plaquette' . ($langue === 'en' ? '-en' : '') . '.pdf');
?>
    <form method="post" enctype="multipart/form-data" class="ligne-form">
        <?= csrf_field() ?>
        <input type="hidden" name="operation" value="plaquette">
        <input type="hidden" name="langue" value="<?= $langue ?>">
        <label for="pdf-<?= $langue ?>"><?= e($libelle) ?><?= $personnalisee ? ' <b class="nouveau">Personnalisée</b>' : '' ?></label>
        <input type="file" id="pdf-<?= $langue ?>" name="pdf" accept="application/pdf">
        <button class="btn btn--contour btn--petit" type="submit">Envoyer le PDF</button>
<?php if ($personnalisee): ?>
        <button class="btn btn--danger btn--petit" type="submit" name="retirer" value="1">Retirer</button>
<?php endif; ?>
    </form>
<?php endforeach; ?>
    <p><a class="lien-fort" href="<?= e(url('plaquette', 'fr')) ?>" target="_blank" rel="noopener">Voir la plaquette générée par le site</a></p>
</section>

<section class="bloc">
    <h2>Carte de visite et QR code</h2>
    <p class="aide">Le QR code ouvre la carte de visite du site, d'où le visiteur enregistre vos coordonnées dans son téléphone. Il reprend automatiquement les coordonnées saisies ci-dessus : inutile de le refaire quand elles changent. Imprimez-le sur vos cartes, devis, véhicules ou panneaux de chantier.</p>
<?php if (!carte_adresse_publique()): ?>
    <p class="aide"><strong>Attention :</strong> l'adresse officielle du site n'est pas renseignée et vous êtes en local. Ce QR code pointe vers <?= e(carte_adresse('fr')) ?>, qu'un téléphone ne peut pas ouvrir. Renseignez « Adresse officielle du site » avant de l'imprimer.</p>
<?php endif; ?>
    <div class="qr-apercu"><?= qr_svg(carte_adresse('fr'), 'QR code de la carte de visite') ?></div>
    <p>Adresse contenue : <a class="lien-fort" href="<?= e(url('carte', 'fr')) ?>" target="_blank" rel="noopener"><?= e(carte_adresse('fr')) ?></a></p>
    <ul>
        <li><a class="lien-fort" href="<?= e(BASE . '/' . ROUTES['fr']['carte'] . '.png') ?>" download>Télécharger en PNG</a> (WhatsApp, Word, réseaux sociaux)</li>
        <li><a class="lien-fort" href="<?= e(BASE . '/' . ROUTES['fr']['carte'] . '.svg') ?>" download>Télécharger en SVG</a> (imprimeur : net à toutes les tailles)</li>
        <li><a class="lien-fort" href="<?= e(BASE . '/en/' . ROUTES['en']['carte'] . '.png') ?>" download>Version anglaise (PNG)</a></li>
    </ul>
    <p class="aide">Taille minimale à l'impression : 2 × 2 cm, sur fond clair.</p>
</section>

<section class="bloc">
    <h2>Mot de passe</h2>
    <form method="post" class="formulaire">
        <?= csrf_field() ?>
        <input type="hidden" name="operation" value="mot-de-passe">
        <div class="champs-2">
            <div class="champ"><label for="actuel">Mot de passe actuel</label><input type="password" id="actuel" name="actuel" autocomplete="current-password" required></div>
            <div></div>
            <div class="champ"><label for="nouveau">Nouveau mot de passe</label><input type="password" id="nouveau" name="nouveau" autocomplete="new-password" minlength="10" required></div>
            <div class="champ"><label for="confirmation">Confirmez le nouveau mot de passe</label><input type="password" id="confirmation" name="confirmation" autocomplete="new-password" minlength="10" required></div>
        </div>
        <button class="btn btn--contour" type="submit">Changer le mot de passe</button>
    </form>
</section>
