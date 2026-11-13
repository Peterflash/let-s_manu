<?php
/**
 * Générateur de QR code (norme ISO/IEC 18004), sans dépendance.
 * Mode octet, correction d'erreur niveau M (environ 15 % du code peut être abîmé),
 * versions 1 à 10 (jusqu'à 213 caractères) : largement assez pour une adresse de page.
 * Sortie en SVG vectoriel, net à toutes les tailles, à l'écran comme à l'impression.
 */
declare(strict_types=1);

/**
 * QR code en SVG.
 * $titre : texte alternatif (balise <title>) ; vide = image décorative.
 */
function qr_svg(string $texte, string $titre = '', string $couleur = '#0B1725', string $fond = '#FFFFFF'): string
{
    $matrice = qr_matrice($texte);
    $taille = count($matrice);
    $marge = 4; // zone de silence imposée par la norme
    $cote = $taille + 2 * $marge;

    // Un rectangle par suite de modules foncés sur une ligne : fichier plus léger.
    $trace = '';
    for ($y = 0; $y < $taille; $y++) {
        for ($x = 0; $x < $taille; $x++) {
            if (!$matrice[$y][$x]) {
                continue;
            }
            $debut = $x;
            while ($x + 1 < $taille && $matrice[$y][$x + 1]) {
                $x++;
            }
            $trace .= 'M' . ($debut + $marge) . ' ' . ($y + $marge) . 'h' . ($x - $debut + 1) . 'v1H' . ($debut + $marge) . 'z';
        }
    }

    $accessibilite = $titre !== '' ? ' role="img" aria-label="' . e($titre) . '"><title>' . e($titre) . '</title>' : ' aria-hidden="true">';
    return '<svg class="qr" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $cote . ' ' . $cote . '" shape-rendering="crispEdges"' . $accessibilite
        . '<rect width="' . $cote . '" height="' . $cote . '" fill="' . e($fond) . '"/>'
        . '<path fill="' . e($couleur) . '" d="' . $trace . '"/></svg>';
}

/** Matrice du QR code : tableau [ligne][colonne] de booléens (true = module foncé). */
function qr_matrice(string $texte): array
{
    // Version : [nombre de blocs du groupe 1, octets de données par bloc, blocs du groupe 2, octets par bloc, octets de correction par bloc]
    static $blocs = [
        1  => [1, 16, 0, 0, 10],
        2  => [1, 28, 0, 0, 16],
        3  => [1, 44, 0, 0, 26],
        4  => [2, 32, 0, 0, 18],
        5  => [2, 43, 0, 0, 24],
        6  => [4, 27, 0, 0, 16],
        7  => [4, 31, 0, 0, 18],
        8  => [2, 38, 2, 39, 22],
        9  => [3, 36, 2, 37, 22],
        10 => [4, 43, 1, 44, 26],
    ];

    $octets = array_values(unpack('C*', $texte) ?: []);
    $longueur = count($octets);

    // Plus petite version qui contient le texte
    $version = 0;
    foreach ($blocs as $v => [$n1, $d1, $n2, $d2]) {
        $bitsCompteur = $v < 10 ? 8 : 16;
        if (4 + $bitsCompteur + 8 * $longueur <= 8 * ($n1 * $d1 + $n2 * $d2)) {
            $version = $v;
            break;
        }
    }
    if ($version === 0) {
        throw new InvalidArgumentException('Texte trop long pour un QR code (213 octets au plus).');
    }
    [$n1, $d1, $n2, $d2, $correction] = $blocs[$version];
    $capacite = $n1 * $d1 + $n2 * $d2;

    // Flux de bits : mode octet (0100), longueur, données, terminateur, octets de remplissage
    $bits = '0100' . str_pad(decbin($longueur), $version < 10 ? 8 : 16, '0', STR_PAD_LEFT);
    foreach ($octets as $o) {
        $bits .= str_pad(decbin($o), 8, '0', STR_PAD_LEFT);
    }
    $bits .= str_repeat('0', min(4, $capacite * 8 - strlen($bits)));
    $bits .= str_repeat('0', (8 - strlen($bits) % 8) % 8);
    $donnees = array_map('bindec', str_split($bits, 8));
    for ($i = 0; count($donnees) < $capacite; $i++) {
        $donnees[] = $i % 2 === 0 ? 0xEC : 0x11;
    }

    // Découpage en blocs, correction Reed-Solomon, entrelacement
    $diviseur = qr_rs_diviseur($correction);
    $blocsDonnees = [];
    $blocsCorrection = [];
    $position = 0;
    foreach ([[$n1, $d1], [$n2, $d2]] as [$nombre, $taille]) {
        for ($b = 0; $b < $nombre; $b++) {
            $bloc = array_slice($donnees, $position, $taille);
            $position += $taille;
            $blocsDonnees[] = $bloc;
            $blocsCorrection[] = qr_rs_reste($bloc, $diviseur);
        }
    }
    $mots = [];
    for ($i = 0; $i < max($d1, $d2); $i++) {
        foreach ($blocsDonnees as $bloc) {
            if (isset($bloc[$i])) {
                $mots[] = $bloc[$i];
            }
        }
    }
    for ($i = 0; $i < $correction; $i++) {
        foreach ($blocsCorrection as $bloc) {
            $mots[] = $bloc[$i];
        }
    }

    // Motifs fixes
    $taille = 17 + 4 * $version;
    $modules = array_fill(0, $taille, array_fill(0, $taille, false));
    $fixe = $modules;
    $poser = function (int $x, int $y, bool $fonce) use (&$modules, &$fixe): void {
        $modules[$y][$x] = $fonce;
        $fixe[$y][$x] = true;
    };

    for ($i = 0; $i < $taille; $i++) { // lignes de synchronisation
        $poser(6, $i, $i % 2 === 0);
        $poser($i, 6, $i % 2 === 0);
    }
    foreach ([[3, 3], [$taille - 4, 3], [3, $taille - 4]] as [$cx, $cy]) { // repères des coins
        for ($dy = -4; $dy <= 4; $dy++) {
            for ($dx = -4; $dx <= 4; $dx++) {
                $x = $cx + $dx;
                $y = $cy + $dy;
                if ($x >= 0 && $x < $taille && $y >= 0 && $y < $taille) {
                    $distance = max(abs($dx), abs($dy));
                    $poser($x, $y, $distance !== 2 && $distance !== 4);
                }
            }
        }
    }
    $alignements = qr_alignements($version);
    $dernier = count($alignements) - 1;
    foreach ($alignements as $i => $ax) { // motifs d'alignement
        foreach ($alignements as $j => $ay) {
            if (($i === 0 && $j === 0) || ($i === 0 && $j === $dernier) || ($i === $dernier && $j === 0)) {
                continue;
            }
            for ($dy = -2; $dy <= 2; $dy++) {
                for ($dx = -2; $dx <= 2; $dx++) {
                    $poser($ax + $dx, $ay + $dy, max(abs($dx), abs($dy)) !== 1);
                }
            }
        }
    }
    qr_format($poser, $taille, 0); // réserve les zones de format (valeur provisoire)
    if ($version >= 7) {
        $reste = $version;
        for ($i = 0; $i < 12; $i++) {
            $reste = ($reste << 1) ^ (($reste >> 11) * 0x1F25);
        }
        $infos = ($version << 12) | $reste;
        for ($i = 0; $i < 18; $i++) {
            $bit = (($infos >> $i) & 1) === 1;
            $a = $taille - 11 + $i % 3;
            $b = intdiv($i, 3);
            $poser($a, $b, $bit);
            $poser($b, $a, $bit);
        }
    }

    // Placement des données en zigzag, de droite à gauche par colonnes de deux
    $nbBits = count($mots) * 8;
    $i = 0;
    for ($droite = $taille - 1; $droite >= 1; $droite -= 2) {
        if ($droite === 6) {
            $droite = 5;
        }
        for ($vert = 0; $vert < $taille; $vert++) {
            for ($j = 0; $j < 2; $j++) {
                $x = $droite - $j;
                $montant = (($droite + 1) & 2) === 0;
                $y = $montant ? $taille - 1 - $vert : $vert;
                if (!$fixe[$y][$x] && $i < $nbBits) {
                    $modules[$y][$x] = (($mots[$i >> 3] >> (7 - ($i & 7))) & 1) === 1;
                    $i++;
                }
            }
        }
    }

    // Choix du masque qui donne le code le plus facile à lire
    $meilleur = null;
    $meilleurScore = PHP_INT_MAX;
    for ($masque = 0; $masque < 8; $masque++) {
        $essai = $modules;
        for ($y = 0; $y < $taille; $y++) {
            for ($x = 0; $x < $taille; $x++) {
                if (!$fixe[$y][$x] && qr_masque($masque, $x, $y)) {
                    $essai[$y][$x] = !$essai[$y][$x];
                }
            }
        }
        $poserEssai = function (int $x, int $y, bool $fonce) use (&$essai): void {
            $essai[$y][$x] = $fonce;
        };
        qr_format($poserEssai, $taille, $masque);
        $score = qr_penalite($essai);
        if ($score < $meilleurScore) {
            $meilleurScore = $score;
            $meilleur = $essai;
        }
    }
    return $meilleur;
}

/** Informations de format (niveau M + numéro de masque), en deux exemplaires, et module foncé fixe. */
function qr_format(callable $poser, int $taille, int $masque): void
{
    $donnee = (0b00 << 3) | $masque; // niveau M = 00
    $reste = $donnee;
    for ($i = 0; $i < 10; $i++) {
        $reste = ($reste << 1) ^ (($reste >> 9) * 0x537);
    }
    $bits = (($donnee << 10) | $reste) ^ 0x5412;
    $bit = fn (int $i): bool => (($bits >> $i) & 1) === 1;

    for ($i = 0; $i <= 5; $i++) {
        $poser(8, $i, $bit($i));
    }
    $poser(8, 7, $bit(6));
    $poser(8, 8, $bit(7));
    $poser(7, 8, $bit(8));
    for ($i = 9; $i < 15; $i++) {
        $poser(14 - $i, 8, $bit($i));
    }
    for ($i = 0; $i < 8; $i++) {
        $poser($taille - 1 - $i, 8, $bit($i));
    }
    for ($i = 8; $i < 15; $i++) {
        $poser(8, $taille - 15 + $i, $bit($i));
    }
    $poser(8, $taille - 8, true);
}

function qr_alignements(int $version): array
{
    static $positions = [
        1 => [], 2 => [6, 18], 3 => [6, 22], 4 => [6, 26], 5 => [6, 30],
        6 => [6, 34], 7 => [6, 22, 38], 8 => [6, 24, 42], 9 => [6, 26, 46], 10 => [6, 28, 50],
    ];
    return $positions[$version];
}

function qr_masque(int $masque, int $x, int $y): bool
{
    return match ($masque) {
        0 => ($x + $y) % 2 === 0,
        1 => $y % 2 === 0,
        2 => $x % 3 === 0,
        3 => ($x + $y) % 3 === 0,
        4 => (intdiv($x, 3) + intdiv($y, 2)) % 2 === 0,
        5 => ($x * $y) % 2 + ($x * $y) % 3 === 0,
        6 => (($x * $y) % 2 + ($x * $y) % 3) % 2 === 0,
        7 => (($x + $y) % 2 + ($x * $y) % 3) % 2 === 0,
    };
}

/** Pénalité de la norme : longues suites, blocs 2 × 2, faux repères, déséquilibre clair / foncé. */
function qr_penalite(array $m): int
{
    $taille = count($m);
    $score = 0;
    $fonces = 0;
    $motifs = ['10111010000', '00001011101'];

    for ($passe = 0; $passe < 2; $passe++) { // lignes puis colonnes
        for ($a = 0; $a < $taille; $a++) {
            $ligne = '';
            for ($b = 0; $b < $taille; $b++) {
                $ligne .= ($passe === 0 ? $m[$a][$b] : $m[$b][$a]) ? '1' : '0';
            }
            preg_match_all('/0{5,}|1{5,}/', $ligne, $suites);
            foreach ($suites[0] as $suite) {
                $score += 3 + strlen($suite) - 5;
            }
            $bordee = '0000' . $ligne . '0000';
            foreach ($motifs as $motif) {
                $score += 40 * substr_count($bordee, $motif);
            }
        }
    }
    for ($y = 0; $y < $taille; $y++) {
        for ($x = 0; $x < $taille; $x++) {
            if ($m[$y][$x]) {
                $fonces++;
            }
            if ($x < $taille - 1 && $y < $taille - 1 && $m[$y][$x] === $m[$y][$x + 1] && $m[$y][$x] === $m[$y + 1][$x] && $m[$y][$x] === $m[$y + 1][$x + 1]) {
                $score += 3;
            }
        }
    }
    $score += 10 * intdiv(abs($fonces * 20 - $taille * $taille * 10), $taille * $taille);
    return $score;
}

/* ---------- Reed-Solomon sur GF(256), polynôme 0x11D ---------- */

function qr_gf_mult(int $x, int $y): int
{
    $z = 0;
    for ($i = 7; $i >= 0; $i--) {
        $z = ($z << 1) ^ (($z >> 7) * 0x11D);
        $z ^= (($y >> $i) & 1) * $x;
    }
    return $z;
}

function qr_rs_diviseur(int $degre): array
{
    $resultat = array_fill(0, $degre, 0);
    $resultat[$degre - 1] = 1;
    $racine = 1;
    for ($i = 0; $i < $degre; $i++) {
        for ($j = 0; $j < $degre; $j++) {
            $resultat[$j] = qr_gf_mult($resultat[$j], $racine);
            if ($j + 1 < $degre) {
                $resultat[$j] ^= $resultat[$j + 1];
            }
        }
        $racine = qr_gf_mult($racine, 0x02);
    }
    return $resultat;
}

function qr_rs_reste(array $donnees, array $diviseur): array
{
    $resultat = array_fill(0, count($diviseur), 0);
    foreach ($donnees as $octet) {
        $facteur = $octet ^ array_shift($resultat);
        $resultat[] = 0;
        foreach ($diviseur as $i => $coef) {
            $resultat[$i] ^= qr_gf_mult($coef, $facteur);
        }
    }
    return $resultat;
}
