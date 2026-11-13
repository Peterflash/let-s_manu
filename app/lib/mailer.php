<?php
/**
 * Envoi d'e-mails avec pièces jointes via la fonction mail() du serveur (disponible chez Hostinger).
 * En local, rien n'est envoyé : le message est écrit dans data/mail-log/ pour vérification.
 */
declare(strict_types=1);

function mail_expediteur(): string
{
    $regle = cfg('email_expediteur');
    if ($regle !== '') {
        return $regle;
    }
    $hote = preg_replace('/^www\./', '', strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? 'localhost')[0]));
    return 'no-reply@' . $hote;
}

/**
 * @param array $piecesJointes liste de ['chemin' => fichier, 'nom' => nom affiché]
 */
function envoyer_mail(string $destinataire, string $sujet, string $texte, string $repondreA = '', array $piecesJointes = []): bool
{
    // Jamais de retour à la ligne dans une adresse ou un sujet (injection d'en-têtes).
    $nettoyer = fn (string $s) => trim(preg_replace('/[\r\n]+/', ' ', $s));
    $destinataire = $nettoyer($destinataire);
    $repondreA = $nettoyer($repondreA);
    $sujet = $nettoyer($sujet);

    if (!filter_var($destinataire, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $expediteur = mail_expediteur();
    $nom = mb_encode_mimeheader(cfg('raison_sociale'), 'UTF-8', 'B');
    $frontiere = 'lm-' . bin2hex(random_bytes(12));

    $entetes = [
        'From: ' . $nom . ' <' . $expediteur . '>',
        'MIME-Version: 1.0',
        'X-Mailer: LETS-MANU-Site',
    ];
    if ($repondreA !== '' && filter_var($repondreA, FILTER_VALIDATE_EMAIL)) {
        $entetes[] = 'Reply-To: ' . $repondreA;
    }

    $corpsTexte = chunk_split(base64_encode($texte));
    if (!$piecesJointes) {
        $entetes[] = 'Content-Type: text/plain; charset=UTF-8';
        $entetes[] = 'Content-Transfer-Encoding: base64';
        $corps = $corpsTexte;
    } else {
        $entetes[] = 'Content-Type: multipart/mixed; boundary="' . $frontiere . '"';
        $corps = "--$frontiere\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n$corpsTexte\r\n";
        foreach ($piecesJointes as $piece) {
            if (!is_file($piece['chemin'])) {
                continue;
            }
            $nomFichier = preg_replace('/[^A-Za-z0-9._-]/', '_', $piece['nom']);
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($piece['chemin']) ?: 'application/octet-stream';
            $corps .= "--$frontiere\r\nContent-Type: $mime; name=\"$nomFichier\"\r\n"
                . "Content-Transfer-Encoding: base64\r\nContent-Disposition: attachment; filename=\"$nomFichier\"\r\n\r\n"
                . chunk_split(base64_encode((string) file_get_contents($piece['chemin']))) . "\r\n";
        }
        $corps .= "--$frontiere--\r\n";
    }

    $sujetEncode = mb_encode_mimeheader($sujet, 'UTF-8', 'B');

    if (is_dev()) {
        $dossier = DATA . '/mail-log';
        if (!is_dir($dossier)) {
            mkdir($dossier, 0755, true);
        }
        $apercu = "To: $destinataire\r\nSubject: $sujet\r\n" . implode("\r\n", $entetes)
            . "\r\n\r\n--- TEXTE ---\r\n$texte\r\n--- " . count($piecesJointes) . " pièce(s) jointe(s) ---\r\n";
        return file_put_contents($dossier . '/' . date('Ymd-His') . '-' . bin2hex(random_bytes(2)) . '.txt', $apercu) !== false;
    }

    return @mail($destinataire, $sujetEncode, $corps, implode("\r\n", $entetes), '-f' . $expediteur);
}
