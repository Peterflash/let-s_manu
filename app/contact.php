<?php
/**
 * Formulaire de devis : affichage, validation, enregistrement, e-mails.
 * Chaque demande est d'abord enregistrée (data/demandes.json + photos), puis envoyée par e-mail :
 * même si l'e-mail n'arrive pas, la demande reste consultable dans l'admin.
 */
declare(strict_types=1);

function page_contact(array $commun): void
{
    start_session();
    $polesServices = config('poles');

    $valeurs = [
        'nom' => '', 'entreprise' => '', 'telephone' => '', 'email' => '',
        'pole' => '', 'service' => '', 'lieu' => '', 'urgence' => 'non',
        'description' => '', 'consentement' => false,
    ];
    $erreurs = [];

    // Présélection depuis un bouton « Demander un devis pour ce service »
    $poleDemande = (string) ($_GET['pole'] ?? '');
    $serviceDemande = (string) ($_GET['service'] ?? '');
    if (isset($polesServices[$poleDemande])) {
        $valeurs['pole'] = $poleDemande;
        if (in_array($serviceDemande, $polesServices[$poleDemande], true)) {
            $valeurs['service'] = $serviceDemande;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        [$erreurs, $valeurs, $demande] = devis_traiter($valeurs);
        if (!$erreurs && $demande) {
            flash('devis_ok', ['ref' => $demande['ref'], 'email' => $demande['email'] !== '', 'urgence' => $demande['urgence']]);
            redirect(url('contact') . '?envoye=1#formulaire');
        }
        http_response_code(422);
    } else {
        $_SESSION['devis_affiche'] = time();
    }

    $succes = isset($_GET['envoye']) ? flash('devis_ok') : null;

    render('pages/contact', $commun + ['valeurs' => $valeurs, 'erreurs' => $erreurs, 'succes' => $succes]);
}

/**
 * Valide et enregistre une demande. Renvoie [erreurs, valeurs saisies, demande enregistrée ou null].
 */
function devis_traiter(array $valeurs): array
{
    $polesServices = config('poles');
    $propre = function (string $champ, int $max): string {
        $brut = $_POST[$champ] ?? '';
        $texte = is_string($brut) ? $brut : '';
        if (!mb_check_encoding($texte, 'UTF-8')) { // client qui n'envoie pas d'UTF-8 : on convertit plutôt que de perdre la saisie
            $texte = mb_convert_encoding($texte, 'UTF-8', 'Windows-1252');
        }
        return mb_substr(trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $texte) ?? ''), 0, $max);
    };

    $valeurs = [
        'nom'          => $propre('nom', 100),
        'entreprise'   => $propre('entreprise', 120),
        'telephone'    => $propre('telephone', 24),
        'email'        => $propre('email', 150),
        'pole'         => $propre('pole', 20),
        'service'      => $propre('service', 20),
        'lieu'         => $propre('lieu', 150),
        'urgence'      => ($_POST['urgence'] ?? '') === 'oui' ? 'oui' : 'non',
        'description'  => $propre('description', 3000),
        'consentement' => !empty($_POST['consentement']),
    ];
    $erreurs = [];

    // --- Protections ---
    if (!csrf_check()) {
        return [['_global' => tx('contact.erreurs.securite')], $valeurs, null];
    }
    $tropRapide = time() - (int) ($_SESSION['devis_affiche'] ?? 0) < 3;
    $envoisRecents = array_filter($_SESSION['devis_envois'] ?? [], fn ($t) => $t > time() - 3600);
    if (($_POST['site_web'] ?? '') !== '' || $tropRapide || count($envoisRecents) >= 5 || !turnstile_valide()) {
        return [['_global' => tx('contact.erreurs.spam')], $valeurs, null];
    }

    // --- Validation ---
    if (mb_strlen($valeurs['nom']) < 2) {
        $erreurs['nom'] = tx('contact.erreurs.nom');
    }
    $telephone = telephone_normaliser($valeurs['telephone']);
    if ($telephone === null) {
        $erreurs['telephone'] = tx('contact.erreurs.telephone');
    }
    if ($valeurs['email'] !== '' && !filter_var($valeurs['email'], FILTER_VALIDATE_EMAIL)) {
        $erreurs['email'] = tx('contact.erreurs.email');
    }
    // Le service choisi détermine le pôle ; « autre » est accepté pour les deux.
    foreach ($polesServices as $pole => $services) {
        if (in_array($valeurs['service'], $services, true)) {
            $valeurs['pole'] = $pole;
        }
    }
    if (!isset($polesServices[$valeurs['pole']])) {
        $erreurs['pole'] = tx('contact.erreurs.pole');
    }
    if ($valeurs['service'] !== 'autre' && !in_array($valeurs['service'], $polesServices[$valeurs['pole']] ?? [], true)) {
        $erreurs['service'] = tx('contact.erreurs.service');
    }
    if (mb_strlen($valeurs['lieu']) < 2) {
        $erreurs['lieu'] = tx('contact.erreurs.lieu');
    }
    if (mb_strlen($valeurs['description']) < 10) {
        $erreurs['description'] = tx('contact.erreurs.description');
    }
    if (!$valeurs['consentement']) {
        $erreurs['consentement'] = tx('contact.erreurs.consentement');
    }

    $photos = fichiers_envoyes('photos');
    if (count($photos) > (int) config('devis.photos_max')) {
        $erreurs['photos'] = tx('contact.erreurs.photos_nombre');
    } else {
        foreach ($photos as $photo) {
            if ($photo['error'] === UPLOAD_ERR_INI_SIZE || $photo['error'] === UPLOAD_ERR_FORM_SIZE || $photo['size'] > (int) config('devis.photo_poids_max')) {
                $erreurs['photos'] = tx('contact.erreurs.photos_poids');
            } elseif ($photo['error'] !== UPLOAD_ERR_OK || image_type($photo['tmp_name']) === null) {
                $erreurs['photos'] = tx('contact.erreurs.photos_type');
            }
        }
    }

    if ($erreurs) {
        return [$erreurs, $valeurs, null];
    }

    // --- Enregistrement ---
    $ref = 'LM-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
    $dossier = DATA . '/demandes/' . $ref;
    $fichiersPhotos = [];
    foreach ($photos as $i => $photo) {
        $nom = 'photo-' . ($i + 1) . '.jpg';
        if (image_importer($photo['tmp_name'], $dossier . '/' . $nom, 1600, 82)) {
            $fichiersPhotos[] = $nom;
        }
    }

    $serviceLibelle = $valeurs['service'] === 'autre'
        ? dot_get(content_raw('fr'), 'contact.form.autre')
        : dot_get(content_raw('fr'), $valeurs['pole'] . '.services.' . $valeurs['service'] . '.titre');

    $demande = [
        'ref'             => $ref,
        'date'            => date('c'),
        'statut'          => 'nouvelle',
        'langue'          => lang(),
        'nom'             => $valeurs['nom'],
        'entreprise'      => $valeurs['entreprise'],
        'telephone'       => $telephone,
        'email'           => $valeurs['email'],
        'pole'            => $valeurs['pole'],
        'service'         => $valeurs['service'],
        'service_libelle' => (string) $serviceLibelle,
        'lieu'            => $valeurs['lieu'],
        'urgence'         => $valeurs['urgence'] === 'oui',
        'description'     => $valeurs['description'],
        'photos'          => $fichiersPhotos,
    ];

    $enregistre = false;
    store_update('demandes', function (array $liste) use ($demande, &$enregistre) {
        array_unshift($liste, $demande);
        $enregistre = true;
        return $liste;
    });
    if (!$enregistre || !is_file(store_path('demandes'))) {
        return [['_global' => tx('contact.erreurs.enregistrement')], $valeurs, null];
    }

    $_SESSION['devis_envois'] = array_merge($envoisRecents, [time()]);
    stats_hit('demande');
    devis_envoyer_mails($demande, $dossier);

    return [[], $valeurs, $demande];
}

/** Accepte un numéro ivoirien à 10 chiffres ou un numéro international ; renvoie la forme +XXXXXXXX ou null. */
function telephone_normaliser(string $saisie): ?string
{
    $numero = preg_replace('/[\s.\-()]/', '', $saisie) ?? '';
    if (str_starts_with($numero, '00')) {
        $numero = '+' . substr($numero, 2);
    }
    if (preg_match('/^0\d{9}$/', $numero)) {
        return '+225' . $numero;
    }
    if (preg_match('/^225(0\d{9})$/', $numero, $m)) {
        return '+225' . $m[1];
    }
    if (preg_match('/^\+\d{8,15}$/', $numero)) {
        return $numero;
    }
    return null;
}

/** Vérification Cloudflare Turnstile, uniquement si les deux clés sont renseignées dans Paramètres. */
function turnstile_valide(): bool
{
    $secret = cfg('turnstile_secret');
    if ($secret === '' || cfg('turnstile_site') === '') {
        return true;
    }
    $jeton = (string) ($_POST['cf-turnstile-response'] ?? '');
    if ($jeton === '' || !function_exists('curl_init')) {
        return false;
    }
    $requete = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
    curl_setopt_array($requete, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query(['secret' => $secret, 'response' => $jeton, 'remoteip' => client_ip()]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
    ]);
    $reponse = json_decode((string) curl_exec($requete), true);
    curl_close($requete);
    return !empty($reponse['success']);
}

function devis_envoyer_mails(array $d, string $dossierPhotos): void
{
    $joli = fn (string $tel) => preg_match('/^\+225(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$/', $tel, $m) ? "+225 $m[1] $m[2] $m[3] $m[4] $m[5]" : $tel;

    // 1. Message interne, toujours en français
    $poles = dot_get(content_raw('fr'), 'poles');
    $lignes = [
        ($d['urgence'] ? "*** URGENCE ***\n\n" : '') . 'Nouvelle demande de devis reçue depuis le site.',
        '',
        'Référence : ' . $d['ref'],
        'Date : ' . date_longue($d['date']),
        '',
        'Nom : ' . $d['nom'],
        'Entreprise : ' . ($d['entreprise'] ?: '—'),
        'Téléphone : ' . $joli($d['telephone']),
        'WhatsApp : https://wa.me/' . ltrim($d['telephone'], '+'),
        'E-mail : ' . ($d['email'] ?: '—'),
        '',
        'Pôle : ' . ($poles[$d['pole']] ?? $d['pole']),
        'Service : ' . $d['service_libelle'],
        "Lieu d'intervention : " . $d['lieu'],
        'Urgence : ' . ($d['urgence'] ? 'OUI' : 'non'),
        '',
        'Description :',
        $d['description'],
        '',
        count($d['photos']) . ' photo(s) jointe(s).',
        "Retrouvez cette demande dans l'administration : " . abs_url(BASE . '/admin/demandes/' . $d['ref']),
    ];
    $pieces = array_map(fn ($nom) => ['chemin' => $dossierPhotos . '/' . $nom, 'nom' => $d['ref'] . '-' . $nom], $d['photos']);
    $sujet = ($d['urgence'] ? '[URGENT] ' : '') . 'Demande de devis ' . $d['ref'] . ' — ' . $d['nom'] . ($d['entreprise'] ? ' (' . $d['entreprise'] . ')' : '');
    envoyer_mail(cfg('email_devis') ?: cfg('email'), $sujet, implode("\n", $lignes), $d['email'], $pieces);

    // 2. Accusé de réception au client, dans sa langue
    if ($d['email'] !== '') {
        $remplacements = [
            '{nom}'            => $d['nom'],
            '{ref}'            => $d['ref'],
            '{telephone}'      => $joli($d['telephone']),
            '{tel_entreprise}' => '+225 ' . cfg('telephone'),
            '{adresse}'        => cfg('ville') . ', ' . cfg_l('adresse'),
        ];
        envoyer_mail(
            $d['email'],
            strtr((string) tx('contact.mail.accuse_sujet'), $remplacements),
            strtr((string) tx('contact.mail.accuse_texte'), $remplacements),
            cfg('email')
        );
    }
}
