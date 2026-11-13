<?php
/**
 * Traitement des photos envoyées (formulaire de devis et administration).
 * Chaque image est vérifiée puis entièrement réencodée par GD : redimensionnée, allégée,
 * et débarrassée de tout contenu qui ne serait pas une image.
 */
declare(strict_types=1);

/** Renvoie 'jpeg', 'png' ou 'webp' si le fichier est bien une image acceptée, sinon null. */
function image_type(string $fichier): ?string
{
    if (!is_file($fichier)) {
        return null;
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($fichier);
    return ['image/jpeg' => 'jpeg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
}

/**
 * Importe une image : redimensionne à $coteMax pixels au plus et enregistre vers $destination
 * (.webp ou .jpg selon l'extension). Renvoie false si le fichier n'est pas une image valide.
 */
function image_importer(string $source, string $destination, int $coteMax = 1600, int $qualite = 80): bool
{
    $type = image_type($source);
    if ($type === null) {
        return false;
    }
    @ini_set('memory_limit', '384M');

    $image = @imagecreatefromstring((string) file_get_contents($source));
    if (!$image) {
        return false;
    }

    // Les téléphones enregistrent souvent la photo couchée, avec une consigne de rotation.
    if ($type === 'jpeg' && function_exists('exif_read_data')) {
        $exif = @exif_read_data($source);
        $rotation = [3 => 180, 6 => -90, 8 => 90][$exif['Orientation'] ?? 0] ?? 0;
        if ($rotation !== 0) {
            $tournee = imagerotate($image, $rotation, 0);
            if ($tournee) {
                imagedestroy($image);
                $image = $tournee;
            }
        }
    }

    $largeur = imagesx($image);
    $hauteur = imagesy($image);
    $ratio = min(1, $coteMax / max($largeur, $hauteur));
    $l = max(1, (int) round($largeur * $ratio));
    $h = max(1, (int) round($hauteur * $ratio));

    $sortie = imagecreatetruecolor($l, $h);
    $versJpeg = (bool) preg_match('/\.jpe?g$/i', $destination);
    if ($versJpeg) {
        imagefill($sortie, 0, 0, imagecolorallocate($sortie, 255, 255, 255));
    } else {
        imagealphablending($sortie, false);
        imagesavealpha($sortie, true);
    }
    imagecopyresampled($sortie, $image, 0, 0, 0, 0, $l, $h, $largeur, $hauteur);
    imagedestroy($image);

    if (!is_dir(dirname($destination))) {
        mkdir(dirname($destination), 0755, true);
    }
    $ok = $versJpeg ? imagejpeg($sortie, $destination, $qualite) : imagewebp($sortie, $destination, $qualite);
    imagedestroy($sortie);
    return $ok;
}

/**
 * Remet à plat le tableau $_FILES d'un champ multiple : [ ['name'=>…, 'tmp_name'=>…, 'error'=>…, 'size'=>…], … ]
 * Les emplacements laissés vides sont ignorés.
 */
function fichiers_envoyes(string $champ): array
{
    $brut = $_FILES[$champ] ?? null;
    if (!$brut) {
        return [];
    }
    $liste = [];
    if (is_array($brut['name'])) {
        foreach ($brut['name'] as $i => $nom) {
            $liste[] = ['name' => $nom, 'tmp_name' => $brut['tmp_name'][$i], 'error' => $brut['error'][$i], 'size' => $brut['size'][$i]];
        }
    } else {
        $liste[] = $brut;
    }
    return array_values(array_filter($liste, fn ($f) => $f['error'] !== UPLOAD_ERR_NO_FILE));
}

/** Supprime un dossier et son contenu (photos d'une réalisation ou d'une demande). */
function supprimer_dossier(string $dossier): void
{
    if (!is_dir($dossier)) {
        return;
    }
    foreach (scandir($dossier) as $element) {
        if ($element === '.' || $element === '..') {
            continue;
        }
        $chemin = $dossier . '/' . $element;
        is_dir($chemin) ? supprimer_dossier($chemin) : @unlink($chemin);
    }
    @rmdir($dossier);
}
