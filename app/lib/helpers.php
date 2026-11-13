<?php
/**
 * Fonctions utilitaires : échappement, réglages, textes, URL, sécurité, rendu des vues.
 */
declare(strict_types=1);

/* ---------- Échappement et environnement ---------- */

function e(mixed $valeur): string
{
    return htmlspecialchars((string) $valeur, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function is_dev(): bool
{
    $hote = strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? 'localhost')[0]);
    return PHP_SAPI === 'cli' || PHP_SAPI === 'cli-server' || in_array($hote, ['localhost', '127.0.0.1'], true);
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
}

function client_ip(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}

/* ---------- Tableaux à clés pointées ---------- */

function dot_get(array $tableau, string $cle, mixed $defaut = null): mixed
{
    foreach (explode('.', $cle) as $segment) {
        if (!is_array($tableau) || !array_key_exists($segment, $tableau)) {
            return $defaut;
        }
        $tableau = $tableau[$segment];
    }
    return $tableau;
}

function dot_set(array &$tableau, string $cle, mixed $valeur): void
{
    $ref = &$tableau;
    foreach (explode('.', $cle) as $segment) {
        if (!isset($ref[$segment]) || !is_array($ref[$segment])) {
            $ref[$segment] = [];
        }
        $ref = &$ref[$segment];
    }
    $ref = $valeur;
}

/** Aplatit un tableau imbriqué en ['a.b.c' => 'valeur']. */
function flatten(array $tableau, string $prefixe = ''): array
{
    $plat = [];
    foreach ($tableau as $cle => $valeur) {
        $chemin = $prefixe === '' ? (string) $cle : $prefixe . '.' . $cle;
        if (is_array($valeur)) {
            $plat += flatten($valeur, $chemin);
        } else {
            $plat[$chemin] = (string) $valeur;
        }
    }
    return $plat;
}

/* ---------- Configuration et réglages ---------- */

function config(string $cle, mixed $defaut = null): mixed
{
    static $config = null;
    $config ??= require APP . '/config.php';
    return dot_get($config, $cle, $defaut);
}

/** Réglage du site : valeur saisie dans l'admin, sinon valeur par défaut de config.php. */
function cfg(string $cle): string
{
    static $reglages = null;
    if ($reglages === null) {
        $reglages = array_merge(config('reglages'), store_read('settings'));
    }
    return (string) ($reglages[$cle] ?? '');
}

/** Réglage décliné par langue (ex. horaires_fr / horaires_en). */
function cfg_l(string $cle): string
{
    $valeur = cfg($cle . '_' . lang()) ?: cfg($cle . '_fr');
    return lang() === 'fr' ? typo_fr($valeur) : $valeur;
}

/* ---------- Langue et textes ---------- */

function lang(?string $nouvelle = null): string
{
    static $langue = 'fr';
    if ($nouvelle !== null) {
        $langue = $nouvelle === 'en' ? 'en' : 'fr';
    }
    return $langue;
}

/** Textes d'une langue : fichier app/content + modifications faites dans l'admin. Sans retouche typographique. */
function content_raw(string $langue): array
{
    static $cache = [];
    if (!isset($cache[$langue])) {
        $textes = require APP . "/content/$langue.php";
        foreach (store_read('textes')[$langue] ?? [] as $cle => $valeur) {
            if (!is_array(dot_get($textes, $cle))) {
                dot_set($textes, $cle, (string) $valeur);
            }
        }
        $cache[$langue] = $textes;
    }
    return $cache[$langue];
}

function content(): array
{
    static $cache = [];
    $langue = lang();
    if (!isset($cache[$langue])) {
        $textes = content_raw($langue);
        if ($langue === 'fr') {
            array_walk_recursive($textes, function (&$v) {
                if (is_string($v)) {
                    $v = typo_fr($v);
                }
            });
        }
        $cache[$langue] = $textes;
    }
    return $cache[$langue];
}

/** Texte brut (chaîne ou sous-tableau). */
function tx(string $cle, mixed $defaut = ''): mixed
{
    return dot_get(content(), $cle, $defaut);
}

/** Texte échappé, prêt à être affiché. */
function t(string $cle): string
{
    $valeur = tx($cle);
    return e(is_string($valeur) ? $valeur : '');
}

/** Liste : une chaîne multiligne devient un tableau (une ligne = un élément). */
function lines(string $cle): array
{
    $valeur = tx($cle);
    return is_string($valeur) ? lines_of($valeur) : [];
}

function lines_of(string $texte): array
{
    return array_values(array_filter(array_map('trim', preg_split('/\R/u', $texte)), 'strlen'));
}

/** Texte en paragraphes HTML (ligne vide = nouveau paragraphe). */
function paras(string $texte): string
{
    $html = '';
    foreach (preg_split('/\R{2,}/u', trim($texte)) as $bloc) {
        if (trim($bloc) !== '') {
            $html .= '<p>' . nl2br(e(trim($bloc))) . '</p>';
        }
    }
    return $html;
}

/** Champ bilingue ['fr' => …, 'en' => …] : renvoie la langue courante, à défaut le français. */
function L(mixed $champ): string
{
    if (!is_array($champ)) {
        return (string) $champ;
    }
    $valeur = (string) ($champ[lang()] ?? '');
    if ($valeur === '') {
        $valeur = (string) ($champ['fr'] ?? '');
    }
    return lang() === 'fr' ? typo_fr($valeur) : $valeur;
}

/** Espaces insécables de la typographie française (avant ? ! : ; », après «, entre nombre et unité). */
function typo_fr(string $s): string
{
    $nbsp = "\u{00A0}";
    $s = preg_replace('/ ([?!:;»])/u', $nbsp . '$1', $s);
    $s = preg_replace('/« /u', '«' . $nbsp, $s);
    $s = preg_replace('/(\d) (\d{3})(?!\d)/u', '$1' . $nbsp . '$2', $s);
    $s = preg_replace('/(\d) (h|m²|m|t|mm|cm|km|%|Mo|ans?|jours?|mois|semaines?)(?![\p{L}\d])/u', '$1' . $nbsp . '$2', $s);
    return $s;
}

function slugify(string $texte): string
{
    $texte = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texte) ?: $texte;
    $texte = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $texte));
    return trim($texte, '-') ?: 'projet';
}

function date_longue(string $iso): string
{
    $ts = strtotime($iso);
    if (!$ts) {
        return '';
    }
    $mois = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    return (int) date('j', $ts) . ' ' . $mois[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts) . ' à ' . date('H\hi', $ts);
}

/* ---------- URL ---------- */

/** Adresse d'une page du site dans la langue voulue (voir ROUTES dans routes.php). */
function url(string $page = 'home', ?string $langue = null): string
{
    $langue ??= lang();
    $slug = ROUTES[$langue][$page] ?? '';
    if ($langue === 'en') {
        return BASE . '/en' . ($slug !== '' ? '/' . $slug : '');
    }
    return BASE . '/' . $slug;
}

function site_origin(): string
{
    $regle = rtrim(cfg('site_url'), '/');
    if ($regle !== '') {
        return $regle;
    }
    return (is_https() ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
}

/** Adresse absolue (balises canoniques, plan du site, e-mails). */
function abs_url(string $chemin): string
{
    if (cfg('site_url') !== '' && BASE !== '' && str_starts_with($chemin, BASE)) {
        $chemin = substr($chemin, strlen(BASE));
    }
    return site_origin() . $chemin;
}

/** Fichier statique avec numéro de version (le cache navigateur se renouvelle à chaque modification). */
function asset(string $chemin): string
{
    $fichier = ROOT . '/assets/' . $chemin;
    $version = is_file($fichier) ? '?v=' . filemtime($fichier) : '';
    return BASE . '/assets/' . $chemin . $version;
}

function tel_href(): string
{
    return 'tel:' . preg_replace('/[^+\d]/', '', cfg('telephone_intl'));
}

function wa_href(string $message = ''): string
{
    $numero = preg_replace('/\D/', '', cfg('whatsapp'));
    return 'https://wa.me/' . $numero . ($message !== '' ? '?text=' . rawurlencode($message) : '');
}

/** Photo envoyée depuis l'admin pour un emplacement, ou null s'il n'y en a pas. */
function emplacement(string $cle): ?string
{
    $fichier = UPLOADS . "/site/$cle.webp";
    return is_file($fichier) ? BASE . "/uploads/site/$cle.webp?v=" . filemtime($fichier) : null;
}

/** Visuel d'une prestation : la vraie photo si elle existe, sinon le dessin technique. */
function visuel(string $cle, string $alt, string $classe = ''): string
{
    $photo = emplacement($cle);
    $src = $photo ?? asset("img/illustrations/$cle.svg");
    $classe = trim($classe . ($photo ? '' : ' est-dessin'));
    return '<img class="' . e($classe) . '" src="' . e($src) . '" alt="' . ($photo ? e($alt) : '') . '" width="480" height="360" loading="lazy" decoding="async">';
}

/** Adresse de la plaquette PDF : version envoyée depuis l'admin, sinon version générée, sinon page imprimable. */
function plaquette_url(): string
{
    $suffixe = lang() === 'en' ? '-en' : '';
    foreach (["/uploads/site/plaquette$suffixe.pdf", "/assets/docs/plaquette-lets-manu-services$suffixe.pdf", '/uploads/site/plaquette.pdf', '/assets/docs/plaquette-lets-manu-services.pdf'] as $chemin) {
        if (is_file(ROOT . $chemin)) {
            return BASE . $chemin . '?v=' . filemtime(ROOT . $chemin);
        }
    }
    return url('plaquette');
}

/* ---------- Sécurité des formulaires ---------- */

function csrf_token(): string
{
    start_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): bool
{
    start_session();
    $recu = (string) ($_POST['_csrf'] ?? '');
    return $recu !== '' && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $recu);
}

/** Message à afficher une seule fois après une redirection. */
function flash(string $cle, mixed $valeur = null): mixed
{
    start_session();
    if ($valeur !== null) {
        $_SESSION['flash'][$cle] = $valeur;
        return null;
    }
    $lu = $_SESSION['flash'][$cle] ?? null;
    unset($_SESSION['flash'][$cle]);
    return $lu;
}

function redirect(string $vers): never
{
    header('Location: ' . $vers, true, 303);
    exit;
}

/* ---------- Rendu ---------- */

function render(string $vue, array $vars = [], string $gabarit = 'layout'): void
{
    extract($vars, EXTR_SKIP);
    ob_start();
    require APP . "/views/$vue.php";
    $contenu = ob_get_clean();
    require APP . "/views/$gabarit.php";
}

function partial(string $nom, array $vars = []): void
{
    extract($vars, EXTR_SKIP);
    require APP . "/views/partials/$nom.php";
}

function not_found(): never
{
    http_response_code(404);
    render('pages/404', ['page' => '404', 'titre' => tx('ui.introuvable_titre'), 'description' => '', 'noindex' => true]);
    exit;
}

/* ---------- Données du site ---------- */

/** Réalisations, les plus récentes d'abord. */
function realisations(bool $publieesSeulement = true): array
{
    $liste = store_read('realisations');
    if ($publieesSeulement) {
        $liste = array_filter($liste, fn ($r) => !empty($r['publie']));
    }
    usort($liste, fn ($a, $b) => strcmp((string) ($b['date'] ?? ''), (string) ($a['date'] ?? '')));
    return array_values($liste);
}

/** Adresse d'affichage d'une photo de réalisation (fichier envoyé ou illustration de démonstration). */
function photo_url(string $chemin): string
{
    $fichier = ROOT . '/' . ltrim($chemin, '/');
    return BASE . '/' . ltrim($chemin, '/') . (is_file($fichier) ? '?v=' . filemtime($fichier) : '');
}

/** Vignette d'une photo envoyée (fichier « -v.webp » créé à l'import), sinon la photo elle-même. */
function photo_vignette(string $chemin): string
{
    $vignette = preg_replace('/\.webp$/', '-v.webp', $chemin);
    return photo_url($vignette !== $chemin && is_file(ROOT . '/' . $vignette) ? $vignette : $chemin);
}
