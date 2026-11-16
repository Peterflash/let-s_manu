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
 * Date de prise de vue enregistrée par l'appareil (EXIF des photos JPEG), au format AAAA-MM-JJ, sinon ''.
 * À lire sur le fichier d'origine : le réencodage par GD efface ces métadonnées.
 */
function image_date_prise(string $fichier): string
{
    if (image_type($fichier) !== 'jpeg' || !function_exists('exif_read_data')) {
        return '';
    }
    $exif = @exif_read_data($fichier);
    $brute = (string) ($exif['DateTimeOriginal'] ?? $exif['DateTimeDigitized'] ?? '');
    return preg_match('/^(\d{4}):(\d{2}):(\d{2})/', $brute, $m) ? date_prise_valide("$m[1]-$m[2]-$m[3]") : '';
}

/** Date de prise de vue AAAA-MM-JJ plausible (date réelle, entre 1990 et aujourd'hui), sinon ''. */
function date_prise_valide(string $date): string
{
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $m) || !checkdate((int) $m[2], (int) $m[3], (int) $m[1]) || (int) $m[1] < 1990) {
        return '';
    }
    return $date <= date('Y-m-d') ? $date : '';
}

/**
 * Remet à plat le tableau $_FILES d'un champ multiple : [ ['name'=>…, 'tmp_name'=>…, 'error'=>…, 'size'=>…, 'indice'=>…], … ]
 * Les emplacements laissés vides sont ignorés. « indice » est la position du fichier dans l'envoi,
 * pour retrouver un champ qui l'accompagne (ex. photos_date[indice]).
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
            $liste[] = ['name' => $nom, 'tmp_name' => $brut['tmp_name'][$i], 'error' => $brut['error'][$i], 'size' => $brut['size'][$i], 'indice' => $i];
        }
    } else {
        $liste[] = $brut + ['indice' => 0];
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
