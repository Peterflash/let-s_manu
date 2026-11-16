<?php
/**
 * Espace d'administration : demandes de devis, réalisations, textes, photos, FAQ, paramètres.
 * Pensé pour être utilisé sans développeur, depuis un téléphone comme depuis un ordinateur.
 */
declare(strict_types=1);

const ADMIN_INACTIVITE = 7200;      // déconnexion après 2 h sans activité
const ADMIN_ESSAIS = 5;             // tentatives de connexion autorisées…
const ADMIN_ESSAIS_FENETRE = 900;   // …par quart d'heure et par adresse IP
const PHOTOS_PAR_REALISATION = 20; // avant, pendant et après les travaux

function admin_url(string $chemin = ''): string
{
    return BASE . '/admin' . ($chemin !== '' ? '/' . $chemin : '');
}

/**
 * Empreinte du compte, gardée en session : elle change si le compte est recréé (mot de passe perdu)
 * ou si son mot de passe est modifié. Les sessions ouvertes avant, sur un autre appareil, sont alors refusées.
 */
function admin_empreinte(array $compte): string
{
    return hash('sha256', ($compte['identifiant'] ?? '') . '|' . ($compte['hash'] ?? ''));
}

function admin_ouvrir_session(array $compte): void
{
    session_regenerate_id(true);
    $_SESSION['admin'] = $compte['identifiant'];
    $_SESSION['admin_empreinte'] = admin_empreinte($compte);
    $_SESSION['admin_vu'] = time();
}

function admin_fermer_session(): void
{
    unset($_SESSION['admin'], $_SESSION['admin_empreinte'], $_SESSION['admin_vu']);
}

/** Limite du serveur en clair : « 96M » devient « 96 Mo ». */
function admin_limite(string $directive): string
{
    return (string) preg_replace(['/^(\d+)\s*K$/i', '/^(\d+)\s*M$/i', '/^(\d+)\s*G$/i'], ['$1 Ko', '$1 Mo', '$1 Go'], trim((string) ini_get($directive)));
}

function admin_render(string $vue, array $vars = []): void
{
    render("admin/$vue", $vars + ['section' => $vue], 'admin/layout');
}

function admin_dispatch(string $chemin): void
{
    start_session();
    header('X-Robots-Tag: noindex, nofollow');
    header('Cache-Control: no-store');

    $segments = $chemin === '' ? [] : explode('/', $chemin);
    $action = $segments[0] ?? 'accueil';
    $post = $_SERVER['REQUEST_METHOD'] === 'POST';

    // Première utilisation : création du compte (une seule fois)
    if (!is_file(store_path('admin'))) {
        admin_fermer_session(); // une session d'un compte supprimé ne vaut plus rien
        if ($action !== 'installation') {
            redirect(admin_url('installation'));
        }
        admin_installation($post);
        return;
    }
    if ($action === 'installation') {
        redirect(admin_url());
    }
    if ($action === 'connexion') {
        admin_connexion($post);
        return;
    }

    // Tout le reste exige d'être connecté, avec une session ouverte sur le compte actuel
    if (empty($_SESSION['admin'])
        || !hash_equals(admin_empreinte(store_read('admin')), (string) ($_SESSION['admin_empreinte'] ?? ''))
        || time() - (int) ($_SESSION['admin_vu'] ?? 0) > ADMIN_INACTIVITE) {
        admin_fermer_session();
        redirect(admin_url('connexion'));
    }
    $_SESSION['admin_vu'] = time();

    // Envoi plus lourd que la limite du serveur : PHP vide alors $_POST et $_FILES.
    // Sans ce contrôle, le message serait « La session a expiré », ce qui égare.
    if ($post && !$_POST && !$_FILES && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        flash('erreur', 'Envoi trop lourd pour le serveur (' . admin_limite('post_max_size') . ' au plus en une fois) : rien n\'a été enregistré. Ajoutez les photos en plusieurs fois.');
        $post = false;
    }

    if ($post && !csrf_check()) {
        flash('erreur', 'La session a expiré. Recommencez.');
        redirect(admin_url($chemin));
    }

    switch ($action) {
        case 'accueil':      admin_accueil(); break;
        case 'deconnexion':  if ($post) { session_destroy(); } redirect(admin_url('connexion')); // no break
        case 'demandes':     admin_demandes($segments[1] ?? '', $post); break;
        case 'media':        admin_media($segments[1] ?? '', $segments[2] ?? ''); break;
        case 'realisations': admin_realisations($segments[1] ?? '', $post); break;
        case 'textes':       admin_textes($post); break;
        case 'photos':       admin_photos($post); break;
        case 'faq':          admin_faq($post); break;
        case 'parametres':   admin_parametres($post); break;
        default:
            http_response_code(404);
            admin_render('introuvable');
    }
}

/* ---------- Accès ---------- */

function admin_installation(bool $post): void
{
    $erreur = '';
    if ($post) {
        $identifiant = trim((string) ($_POST['identifiant'] ?? ''));
        $motDePasse = (string) ($_POST['mot_de_passe'] ?? '');
        if (!csrf_check()) {
            $erreur = 'La session a expiré. Recommencez.';
        } elseif (mb_strlen($identifiant) < 3) {
            $erreur = "Choisissez un identifiant d'au moins 3 caractères.";
        } elseif (mb_strlen($motDePasse) < 10) {
            $erreur = 'Le mot de passe doit contenir au moins 10 caractères.';
        } elseif ($motDePasse !== (string) ($_POST['confirmation'] ?? '')) {
            $erreur = 'Les deux mots de passe ne sont pas identiques.';
        } else {
            $compte = ['identifiant' => $identifiant, 'hash' => password_hash($motDePasse, PASSWORD_DEFAULT), 'cree' => date('c')];
            store_write('admin', $compte);
            admin_ouvrir_session($compte);
            flash('succes', "Compte créé. Bienvenue dans l'administration du site.");
            redirect(admin_url());
        }
    }
    admin_render('acces', ['mode' => 'installation', 'erreur' => $erreur]);
}

function admin_connexion(bool $post): void
{
    $erreur = '';
    if ($post) {
        $ip = client_ip();
        $essais = array_filter(store_read('connexions', true)[$ip] ?? [], fn ($t) => $t > time() - ADMIN_ESSAIS_FENETRE);
        $compte = store_read('admin');
        if (!csrf_check()) {
            $erreur = 'La session a expiré. Recommencez.';
        } elseif (count($essais) >= ADMIN_ESSAIS) {
            $erreur = 'Trop de tentatives. Réessayez dans un quart d\'heure.';
        } elseif (hash_equals((string) ($compte['identifiant'] ?? ''), trim((string) ($_POST['identifiant'] ?? '')))
            && password_verify((string) ($_POST['mot_de_passe'] ?? ''), (string) ($compte['hash'] ?? ''))) {
            admin_ouvrir_session($compte);
            redirect(admin_url());
        } else {
            store_update('connexions', function (array $tous) use ($ip, $essais) {
                $tous = array_filter(array_map(fn ($liste) => array_values(array_filter($liste, fn ($t) => $t > time() - ADMIN_ESSAIS_FENETRE)), $tous));
                $tous[$ip] = array_merge(array_values($essais), [time()]);
                return $tous;
            });
            $erreur = 'Identifiant ou mot de passe incorrect.';
        }
    }
    admin_render('acces', ['mode' => 'connexion', 'erreur' => $erreur]);
}

/* ---------- Tableau de bord ---------- */

function admin_accueil(): void
{
    $demandes = store_read('demandes');
    $realisations = store_read('realisations');
    $emplacements = config('emplacements');
    $aFaire = [];
    $demos = count(array_filter($realisations, fn ($r) => !empty($r['demo'])));
    if ($demos > 0) {
        $aFaire[] = ["$demos projet(s) type(s) à remplacer par de vrais chantiers", admin_url('realisations')];
    }
    $photosPosees = count(array_filter(array_keys($emplacements), fn ($cle) => emplacement($cle) !== null));
    if ($photosPosees < count($emplacements)) {
        $aFaire[] = [(count($emplacements) - $photosPosees) . ' emplacement(s) du site utilisent encore un dessin à la place d\'une vraie photo', admin_url('photos')];
    }
    if (cfg('gps_lat') === '' || cfg('gps_lng') === '') {
        $aFaire[] = ['Coordonnées GPS du siège à renseigner pour une carte précise', admin_url('parametres')];
    }
    if (str_ends_with(cfg('email'), '@gmail.com')) {
        $aFaire[] = ['Adresse e-mail professionnelle à créer (contact@…) à la place de l\'adresse Gmail', admin_url('parametres')];
    }
    if (trim((string) dot_get(content_raw('fr'), 'about.gerante.message')) === '') {
        $aFaire[] = ['Mot de la gérante non renseigné (la rubrique reste masquée sur le site)', admin_url('textes') . '?section=about'];
    }

    admin_render('accueil', [
        'stats'     => stats_periode(30),
        'nouvelles' => count(array_filter($demandes, fn ($d) => ($d['statut'] ?? '') === 'nouvelle')),
        'dernieres' => array_slice($demandes, 0, 5),
        'publiees'  => count(array_filter($realisations, fn ($r) => !empty($r['publie']))),
        'aFaire'    => $aFaire,
    ]);
}

/* ---------- Demandes de devis ---------- */

function admin_demandes(string $ref, bool $post): void
{
    $demandes = store_read('demandes', true);
    if ($ref === '') {
        admin_render('demandes', ['demandes' => $demandes]);
        return;
    }
    $indice = array_search($ref, array_column($demandes, 'ref'), true);
    if ($indice === false) {
        flash('erreur', 'Demande introuvable.');
        redirect(admin_url('demandes'));
    }
    if ($post) {
        $operation = (string) ($_POST['operation'] ?? '');
        if ($operation === 'supprimer') {
            store_update('demandes', fn (array $liste) => array_values(array_filter($liste, fn ($d) => $d['ref'] !== $ref)));
            supprimer_dossier(DATA . '/demandes/' . $ref);
            flash('succes', "Demande $ref supprimée.");
            redirect(admin_url('demandes'));
        }
        if (in_array($operation, ['nouvelle', 'traitee'], true)) {
            store_update('demandes', function (array $liste) use ($ref, $operation) {
                foreach ($liste as &$d) {
                    if ($d['ref'] === $ref) {
                        $d['statut'] = $operation;
                    }
                }
                return $liste;
            });
            flash('succes', $operation === 'traitee' ? 'Demande marquée comme traitée.' : 'Demande remise en « nouvelle ».');
        }
        redirect(admin_url('demandes/' . $ref));
    }
    admin_render('demande', ['d' => $demandes[$indice]]);
}

/** Photos jointes à une demande : hors du dossier public, servies uniquement à l'administrateur connecté. */
function admin_media(string $ref, string $fichier): void
{
    $chemin = DATA . '/demandes/' . $ref . '/' . $fichier;
    if (!preg_match('/^LM-\d{6}-[0-9A-F]{4}$/', $ref) || !preg_match('/^photo-\d+\.jpg$/', $fichier) || !is_file($chemin)) {
        http_response_code(404);
        return;
    }
    header('Content-Type: image/jpeg');
    header('Content-Length: ' . filesize($chemin));
    header('Content-Disposition: inline; filename="' . $ref . '-' . $fichier . '"');
    readfile($chemin);
}

/* ---------- Réalisations ---------- */

function admin_realisations(string $id, bool $post): void
{
    $liste = store_read('realisations', true);

    if ($id === '') {
        if ($post && ($_POST['operation'] ?? '') === 'supprimer-demos') {
            store_write('realisations', array_values(array_filter($liste, fn ($r) => empty($r['demo']))));
            flash('succes', 'Les projets types ont été supprimés.');
            redirect(admin_url('realisations'));
        }
        usort($liste, fn ($a, $b) => strcmp((string) ($b['date'] ?? ''), (string) ($a['date'] ?? '')));
        admin_render('realisations', ['liste' => $liste]);
        return;
    }

    $nouvelle = $id === 'nouvelle';
    $indice = $nouvelle ? false : array_search($id, array_column($liste, 'id'), true);
    if (!$nouvelle && $indice === false) {
        flash('erreur', 'Réalisation introuvable.');
        redirect(admin_url('realisations'));
    }
    $vide = ['fr' => '', 'en' => ''];
    $r = $nouvelle
        ? ['id' => bin2hex(random_bytes(4)), 'slug' => '', 'demo' => false, 'publie' => true, 'etat' => 'termine', 'date' => date('Y-m-d'), 'pole' => 'industriel', 'secteur' => 'industrie', 'annee' => date('Y'),
           'titre' => $vide, 'client' => $vide, 'lieu' => $vide, 'intervention' => $vide, 'duree' => $vide, 'defi' => $vide, 'solution' => $vide, 'photos' => []]
        : $liste[$indice];
    $erreurs = [];

    if ($post) {
        if (($_POST['operation'] ?? '') === 'supprimer' && !$nouvelle) {
            array_splice($liste, $indice, 1);
            store_write('realisations', $liste);
            supprimer_dossier(UPLOADS . '/realisations/' . $r['id']);
            flash('succes', 'Réalisation supprimée.');
            redirect(admin_url('realisations'));
        }

        $texte = fn (string $champ, int $max) => mb_substr(trim((string) ($_POST[$champ] ?? '')), 0, $max);
        foreach (['titre' => 140, 'client' => 140, 'lieu' => 140, 'intervention' => 140, 'duree' => 140, 'defi' => 2000, 'solution' => 2000] as $champ => $max) {
            $r[$champ] = ['fr' => $texte($champ . '_fr', $max), 'en' => $texte($champ . '_en', $max)];
        }
        $r['pole'] = isset(config('poles')[$_POST['pole'] ?? '']) ? $_POST['pole'] : 'industriel';
        $r['secteur'] = isset(config('secteurs')[$_POST['secteur'] ?? '']) ? $_POST['secteur'] : 'industrie';
        $r['annee'] = preg_match('/^\d{4}$/', $texte('annee', 4)) ? $texte('annee', 4) : '';
        $r['date'] = preg_match('/^\d{4}-\d{2}-\d{2}$/', $texte('date', 10)) ? $texte('date', 10) : date('Y-m-d');
        $r['publie'] = !empty($_POST['publie']);
        $r['demo'] = !empty($r['demo']) && !empty($_POST['demo']);
        $r['etat'] = ($_POST['etat'] ?? '') === 'en-cours' ? 'en-cours' : 'termine';

        if ($r['titre']['fr'] === '') {
            $erreurs[] = 'Le titre en français est obligatoire.';
        }

        // Photos existantes : photo principale (vignette du site), étiquette, lieu, date et suppression
        $principale = (string) ($_POST['photo_principale'] ?? '');
        $conservees = [];
        foreach ($r['photos'] as $i => $photo) {
            if (!empty($_POST['photo_supprimer'][$i])) {
                if (str_starts_with($photo['fichier'], 'uploads/')) {
                    @unlink(ROOT . '/' . $photo['fichier']);
                    @unlink(ROOT . '/' . preg_replace('/\.webp$/', '-v.webp', $photo['fichier']));
                }
                continue;
            }
            $etiquette = (string) ($_POST['photo_etiquette'][$i] ?? '');
            $photo['etiquette'] = in_array($etiquette, ['avant', 'pendant', 'apres'], true) ? $etiquette : '';
            // Lieu et date de la prise de vue : vides, la visionneuse reprend le lieu du chantier et l'année des travaux.
            $photo['lieu'] = mb_substr(trim((string) ($_POST['photo_lieu'][$i] ?? '')), 0, 140);
            $date = trim((string) ($_POST['photo_date'][$i] ?? ''));
            $photo['date'] = preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $m) && checkdate((int) $m[2], (int) $m[3], (int) $m[1]) ? $date : '';
            if ((string) $i === $principale) {
                array_unshift($conservees, $photo); // la première photo sert de vignette
            } else {
                $conservees[] = $photo;
            }
        }
        $r['photos'] = $conservees;

        // Nouvelles photos. Allégées dans le navigateur avant l'envoi, elles ont perdu leur date de prise de vue :
        // admin.js la lit sur la photo d'origine et l'envoie dans photos_date[indice du fichier].
        $datesLues = (array) ($_POST['photos_date'] ?? []);
        $ajoutees = 0;
        foreach (fichiers_envoyes('photos') as $envoi) {
            if (count($r['photos']) >= PHOTOS_PAR_REALISATION) {
                $erreurs[] = 'Une réalisation accepte ' . PHOTOS_PAR_REALISATION . ' photos au maximum : les photos en trop ont été ignorées.';
                break;
            }
            if (in_array($envoi['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
                $erreurs[] = 'La photo « ' . $envoi['name'] . ' » est trop lourde (' . admin_limite('upload_max_filesize') . ' au plus) : elle n\'a pas été importée.';
                continue;
            }
            $nom = bin2hex(random_bytes(5));
            $relatif = 'uploads/realisations/' . $r['id'] . '/' . $nom . '.webp';
            if ($envoi['error'] === UPLOAD_ERR_OK && image_importer($envoi['tmp_name'], ROOT . '/' . $relatif, 1600, 80)) {
                image_importer($envoi['tmp_name'], ROOT . '/' . preg_replace('/\.webp$/', '-v.webp', $relatif), 640, 72);
                $date = date_prise_valide(trim((string) ($datesLues[$envoi['indice']] ?? ''))) ?: image_date_prise($envoi['tmp_name']);
                $r['photos'][] = ['fichier' => $relatif, 'etiquette' => '', 'lieu' => '', 'date' => $date];
                $ajoutees++;
            } else {
                $erreurs[] = 'Le fichier « ' . $envoi['name'] . " » n'a pas pu être importé (JPG, PNG ou WebP uniquement).";
            }
        }

        if ($r['titre']['fr'] !== '') {
            // Adresse de la fiche : fixée à la création, pour ne pas casser les liens déjà partagés
            if ($r['slug'] === '') {
                $slug = slugify($r['titre']['fr']);
                $pris = array_column($liste, 'slug');
                for ($n = 2, $essai = $slug; in_array($essai, $pris, true); $n++) {
                    $essai = $slug . '-' . $n;
                }
                $r['slug'] = $essai;
            }
            if ($nouvelle) {
                $liste[] = $r;
            } else {
                $liste[$indice] = $r;
            }
            store_write('realisations', $liste);
            if (!$erreurs) {
                flash('succes', 'Réalisation enregistrée' . ($ajoutees ? ' : ' . $ajoutees . ' photo(s) ajoutée(s)' : '') . '.');
                redirect(admin_url('realisations/' . $r['id']));
            }
            $nouvelle = false;
        }
    }

    admin_render('realisation', ['r' => $r, 'nouvelle' => $nouvelle, 'erreurs' => $erreurs]);
}

/* ---------- Textes du site ---------- */

function admin_textes(bool $post): void
{
    $sections = [
        'home' => "Page d'accueil", 'industriel' => 'Services industriels', 'construction' => 'Construction métallique',
        'realisations' => 'Réalisations', 'about' => 'À propos', 'contact' => 'Contact et formulaire',
        'final' => 'Appel de fin de page', 'pied' => 'Pied de page', 'assistant' => 'Assistant de questions',
        'mentions' => 'Mentions légales', 'confidentialite' => 'Confidentialité', 'meta' => 'Titres Google (SEO)',
        'nav' => 'Menu', 'ui' => 'Boutons et libellés', 'secteurs' => 'Secteurs', 'poles' => 'Noms des pôles',
        'consentement' => 'Bandeau de consentement', 'plaquette' => 'Plaquette', 'carte' => 'Carte de visite',
    ];
    $langue = ($_GET['langue'] ?? '') === 'en' ? 'en' : 'fr';
    $section = isset($sections[$_GET['section'] ?? '']) ? $_GET['section'] : 'home';
    $defauts = flatten((require APP . "/content/$langue.php")[$section] ?? [], $section);

    if ($post) {
        $modifications = store_read('textes', true);
        foreach ($defauts as $cle => $defaut) {
            $champ = str_replace('.', '__', $cle);
            if (!isset($_POST['t'][$champ])) {
                continue;
            }
            $valeur = trim(str_replace("\r\n", "\n", (string) $_POST['t'][$champ]));
            if ($valeur === '' || $valeur === trim($defaut)) {
                unset($modifications[$langue][$cle]);
            } else {
                $modifications[$langue][$cle] = $valeur;
            }
        }
        store_write('textes', $modifications);
        flash('succes', 'Textes enregistrés.');
        redirect(admin_url('textes') . '?langue=' . $langue . '&section=' . $section);
    }

    admin_render('textes', [
        'sections' => $sections, 'langue' => $langue, 'sectionTextes' => $section,
        'defauts' => $defauts, 'modifications' => store_read('textes')[$langue] ?? [],
    ]);
}

/* ---------- Photos du site ---------- */

function admin_photos(bool $post): void
{
    $emplacements = config('emplacements');
    if ($post) {
        $cle = (string) ($_POST['emplacement'] ?? '');
        if (isset($emplacements[$cle])) {
            $cible = UPLOADS . "/site/$cle.webp";
            if (($_POST['operation'] ?? '') === 'supprimer') {
                @unlink($cible);
                flash('succes', 'Photo retirée : le dessin par défaut est de nouveau affiché.');
            } else {
                $envoi = fichiers_envoyes('photo')[0] ?? null;
                if ($envoi && $envoi['error'] === UPLOAD_ERR_OK && image_importer($envoi['tmp_name'], $cible, 1400, 80)) {
                    flash('succes', 'Photo enregistrée pour « ' . $emplacements[$cle] . ' ».');
                } else {
                    flash('erreur', "La photo n'a pas pu être importée (JPG, PNG ou WebP uniquement).");
                }
            }
        }
        redirect(admin_url('photos'));
    }
    admin_render('photos', ['emplacements' => $emplacements]);
}

/* ---------- FAQ de l'assistant ---------- */

function admin_faq(bool $post): void
{
    if ($post) {
        $questions = [];
        foreach ((array) ($_POST['q_fr'] ?? []) as $i => $question) {
            $ligne = [
                'q' => ['fr' => trim((string) $question), 'en' => trim((string) ($_POST['q_en'][$i] ?? ''))],
                'r' => ['fr' => trim((string) ($_POST['r_fr'][$i] ?? '')), 'en' => trim((string) ($_POST['r_en'][$i] ?? ''))],
            ];
            if ($ligne['q']['fr'] !== '' && $ligne['r']['fr'] !== '') {
                $questions[] = $ligne;
            }
        }
        store_write('faq', $questions);
        flash('succes', 'Questions enregistrées.');
        redirect(admin_url('faq'));
    }
    admin_render('faq', ['questions' => store_read('faq', true)]);
}

/* ---------- Paramètres ---------- */

function admin_parametres(bool $post): void
{
    $defauts = config('reglages');
    if ($post) {
        $operation = (string) ($_POST['operation'] ?? 'reglages');

        if ($operation === 'mot-de-passe') {
            $compte = store_read('admin', true);
            $nouveau = (string) ($_POST['nouveau'] ?? '');
            if (!password_verify((string) ($_POST['actuel'] ?? ''), (string) $compte['hash'])) {
                flash('erreur', 'Le mot de passe actuel est incorrect.');
            } elseif (mb_strlen($nouveau) < 10) {
                flash('erreur', 'Le nouveau mot de passe doit contenir au moins 10 caractères.');
            } elseif ($nouveau !== (string) ($_POST['confirmation'] ?? '')) {
                flash('erreur', 'Les deux nouveaux mots de passe ne sont pas identiques.');
            } else {
                $compte['hash'] = password_hash($nouveau, PASSWORD_DEFAULT);
                store_write('admin', $compte);
                $_SESSION['admin_empreinte'] = admin_empreinte($compte); // cet appareil reste connecté, les autres non
                flash('succes', "Mot de passe modifié. Les autres appareils connectés à l'administration sont déconnectés.");
            }
        } elseif ($operation === 'plaquette') {
            $suffixe = ($_POST['langue'] ?? '') === 'en' ? '-en' : '';
            $cible = UPLOADS . "/site/plaquette$suffixe.pdf";
            $envoi = fichiers_envoyes('pdf')[0] ?? null;
            if (!empty($_POST['retirer'])) {
                @unlink($cible);
                flash('succes', 'Plaquette personnalisée retirée : la version générée par le site est de nouveau proposée.');
            } elseif ($envoi && $envoi['error'] === UPLOAD_ERR_OK && $envoi['size'] <= 10 * 1024 * 1024
                && (new finfo(FILEINFO_MIME_TYPE))->file($envoi['tmp_name']) === 'application/pdf') {
                if (!is_dir(dirname($cible))) {
                    mkdir(dirname($cible), 0755, true);
                }
                move_uploaded_file($envoi['tmp_name'], $cible);
                flash('succes', 'Plaquette PDF enregistrée.');
            } else {
                flash('erreur', 'Le fichier doit être un PDF de 10 Mo au plus.');
            }
        } else {
            $reglages = [];
            foreach ($defauts as $cle => $defaut) {
                $valeur = trim(str_replace("\r\n", "\n", (string) ($_POST['r'][$cle] ?? '')));
                if (in_array($cle, ['linkedin', 'facebook', 'site_url', 'hebergeur_site'], true) && $valeur !== '' && !preg_match('#^https?://#i', $valeur)) {
                    $valeur = 'https://' . $valeur;
                }
                if (in_array($cle, ['email', 'email_devis', 'email_expediteur'], true) && $valeur !== '' && !filter_var($valeur, FILTER_VALIDATE_EMAIL)) {
                    flash('erreur', "L'adresse « $valeur » n'est pas valide : elle n'a pas été enregistrée.");
                    $valeur = cfg($cle);
                }
                $reglages[$cle] = $valeur;
            }
            store_write('settings', $reglages);
            flash('succes', 'Paramètres enregistrés.');
        }
        redirect(admin_url('parametres'));
    }
    admin_render('parametres', ['defauts' => $defauts]);
}
