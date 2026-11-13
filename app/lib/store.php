<?php
/**
 * Stockage en fichiers JSON (dossier data/). Pas de base de données à installer.
 * Si un fichier n'existe pas encore, on lit le jeu de départ dans app/seed/.
 */
declare(strict_types=1);

function store_path(string $nom): string
{
    return DATA . '/' . $nom . '.json';
}

function store_read(string $nom, bool $frais = false): array
{
    static $cache = [];
    if (!$frais && isset($cache[$nom])) {
        return $cache[$nom];
    }
    $fichier = store_path($nom);
    if (!is_file($fichier)) {
        $fichier = APP . '/seed/' . $nom . '.json';
    }
    $donnees = [];
    if (is_file($fichier)) {
        $donnees = json_decode((string) file_get_contents($fichier), true);
        if (!is_array($donnees)) {
            $donnees = [];
        }
    }
    return $cache[$nom] = $donnees;
}

/** Écriture atomique : fichier temporaire puis renommage, pour ne jamais laisser un JSON à moitié écrit. */
function store_write(string $nom, array $donnees): bool
{
    if (!is_dir(DATA)) {
        mkdir(DATA, 0755, true);
    }
    $json = json_encode($donnees, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }
    $temporaire = store_path($nom) . '.' . bin2hex(random_bytes(4)) . '.tmp';
    if (file_put_contents($temporaire, $json, LOCK_EX) === false) {
        return false;
    }
    if (!@rename($temporaire, store_path($nom))) {
        @unlink($temporaire);
        return false;
    }
    store_read($nom, true);
    return true;
}

/** Lecture-modification-écriture sous verrou (deux envois simultanés ne s'écrasent pas). */
function store_update(string $nom, callable $modifier): array
{
    if (!is_dir(DATA)) {
        mkdir(DATA, 0755, true);
    }
    $verrou = fopen(DATA . '/.verrou', 'c');
    if ($verrou) {
        flock($verrou, LOCK_EX);
    }
    try {
        $donnees = $modifier(store_read($nom, true));
        store_write($nom, $donnees);
    } finally {
        if ($verrou) {
            flock($verrou, LOCK_UN);
            fclose($verrou);
        }
    }
    return $donnees;
}
