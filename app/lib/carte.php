<?php
/**
 * Carte de visite numérique : page /carte-de-visite, fiche contact (.vcf) et QR code (.svg / .png).
 * Le QR code renvoie vers la page de la carte : il reste valable quand les coordonnées
 * changent dans l'admin, seule l'adresse du site compte.
 */
declare(strict_types=1);

/** Adresse absolue de la carte de visite (celle que contient le QR code). */
function carte_adresse(?string $langue = null): string
{
    return abs_url(url('carte', $langue));
}

/** Le QR code imprimé doit pointer vers le vrai site, pas vers une adresse locale. */
function carte_adresse_publique(): bool
{
    return cfg('site_url') !== '' || !is_dev();
}

/** Sert la fiche contact, le QR code en SVG ou en PNG selon l'extension demandée. */
function page_carte_fichier(string $extension): void
{
    $nom = 'lets-manu-services' . (lang() === 'en' ? '-en' : '');
    switch ($extension) {
        case 'vcf':
            stats_hit('contact');
            header('Content-Type: text/vcard; charset=UTF-8');
            // Sur iPhone, l'affichage direct ouvre la fiche « Nouveau contact » ; ailleurs, on télécharge le fichier.
            $iphone = (bool) preg_match('/iPhone|iPad|iPod/', $_SERVER['HTTP_USER_AGENT'] ?? '');
            header('Content-Disposition: ' . ($iphone ? 'inline' : 'attachment') . '; filename="' . $nom . '.vcf"');
            header('Cache-Control: no-cache');
            echo vcard_texte();
            return;

        case 'svg':
            header('Content-Type: image/svg+xml; charset=UTF-8');
            header('Content-Disposition: inline; filename="qr-code-' . $nom . '.svg"');
            header('Cache-Control: no-cache');
            echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . str_replace('<svg class="qr" ', '<svg width="1000" height="1000" ', qr_svg(carte_adresse(), (string) tx('carte.qr_alt')));
            return;

        case 'png':
            $matrice = qr_matrice(carte_adresse());
            $cote = count($matrice) + 8;
            $echelle = (int) ceil(1000 / $cote);
            $image = imagecreate($cote * $echelle, $cote * $echelle);
            imagecolorallocate($image, 255, 255, 255);
            $fonce = imagecolorallocate($image, 0x0B, 0x17, 0x25);
            foreach ($matrice as $y => $ligne) {
                foreach ($ligne as $x => $module) {
                    if ($module) {
                        imagefilledrectangle($image, ($x + 4) * $echelle, ($y + 4) * $echelle, ($x + 5) * $echelle - 1, ($y + 5) * $echelle - 1, $fonce);
                    }
                }
            }
            header('Content-Type: image/png');
            header('Content-Disposition: inline; filename="qr-code-' . $nom . '.png"');
            header('Cache-Control: no-cache');
            imagepng($image, null, 9);
            imagedestroy($image);
            return;
    }
    not_found();
}

/** Fiche contact au format vCard 3.0, reconnue par Android, iPhone, Outlook et Gmail. */
function vcard_texte(): string
{
    $echapper = fn (string $v): string => str_replace(["\\", "\r\n", "\n", ',', ';'], ["\\\\", '\n', '\n', '\,', '\;'], $v);
    $societe = $echapper(cfg('raison_sociale'));
    $note = trim(tx('carte.vcard_note') . "\n" . tx('ui.whatsapp') . ' : +225 ' . cfg('telephone') . "\n" . cfg('forme_juridique') . ' — IDU ' . cfg('idu'));

    $lignes = [
        'BEGIN:VCARD',
        'VERSION:3.0',
        'N:' . $societe . ';;;;',
        'FN:' . $societe,
        'ORG:' . $societe,
        'X-ABShowAs:COMPANY',
        'TEL;TYPE=WORK,VOICE,CELL:' . preg_replace('/[^+\d]/', '', cfg('telephone_intl')),
        'EMAIL;TYPE=INTERNET,WORK:' . $echapper(cfg('email')),
        'ADR;TYPE=WORK:;;' . $echapper(cfg_l('adresse')) . ';' . $echapper(cfg('ville')) . ';;;' . $echapper("Côte d'Ivoire"),
        'URL:' . $echapper(abs_url(url('home'))),
    ];
    if (cfg('gps_lat') !== '' && cfg('gps_lng') !== '') {
        $lignes[] = 'GEO:' . (float) cfg('gps_lat') . ';' . (float) cfg('gps_lng');
    }
    $lignes[] = 'NOTE:' . $echapper($note);
    if ($photo = vcard_photo()) {
        $lignes[] = 'PHOTO;ENCODING=b;TYPE=JPEG:' . $photo;
    }
    $lignes[] = 'REV:' . gmdate('Ymd\THis\Z');
    $lignes[] = 'END:VCARD';

    return implode("\r\n", array_map('vcard_plier', $lignes)) . "\r\n";
}

/** Emblème du logo en JPEG (la plupart des carnets d'adresses ne lisent pas le WebP), encodé en base 64. */
function vcard_photo(): string
{
    $source = ROOT . '/assets/img/logo/embleme-256.webp';
    if (!function_exists('imagecreatefromwebp') || !is_file($source) || !($embleme = @imagecreatefromwebp($source))) {
        return '';
    }
    $cote = 240;
    $photo = imagecreatetruecolor($cote, $cote);
    imagefill($photo, 0, 0, imagecolorallocate($photo, 255, 255, 255));
    $marge = 20;
    imagecopyresampled($photo, $embleme, $marge, $marge, 0, 0, $cote - 2 * $marge, $cote - 2 * $marge, imagesx($embleme), imagesy($embleme));
    ob_start();
    imagejpeg($photo, null, 85);
    $jpeg = (string) ob_get_clean();
    imagedestroy($photo);
    imagedestroy($embleme);
    return base64_encode($jpeg);
}

/** Pliage des lignes à 75 octets (norme vCard), sans couper un caractère accentué. */
function vcard_plier(string $ligne): string
{
    if (strlen($ligne) <= 75) {
        return $ligne;
    }
    $morceaux = [];
    $courant = '';
    foreach (mb_str_split($ligne) as $caractere) {
        if (strlen($courant) + strlen($caractere) > ($morceaux ? 74 : 75)) {
            $morceaux[] = $courant;
            $courant = '';
        }
        $courant .= $caractere;
    }
    $morceaux[] = $courant;
    return implode("\r\n ", $morceaux);
}
