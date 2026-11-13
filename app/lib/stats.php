<?php
/**
 * Statistiques internes, sans cookie ni service externe : pages vues et clics
 * sur Appeler / WhatsApp / Devis, fiches contact enregistrées, comptés par jour dans data/stats/AAAA-MM.json.
 * (Google Analytics 4 reste possible en complément, voir Paramètres.)
 */
declare(strict_types=1);

const STATS_EVENEMENTS = ['vues', 'appel', 'whatsapp', 'devis', 'plaquette', 'demande', 'contact'];

function est_robot(): bool
{
    $agent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
    return $agent === '' || (bool) preg_match('/bot|crawl|spider|slurp|facebookexternalhit|preview|monitor|curl|wget|headless|lighthouse/', $agent);
}

function stats_hit(string $evenement): void
{
    if (!in_array($evenement, STATS_EVENEMENTS, true) || est_robot()) {
        return;
    }
    $dossier = DATA . '/stats';
    if (!is_dir($dossier) && !@mkdir($dossier, 0755, true)) {
        return;
    }
    $fichier = fopen($dossier . '/' . date('Y-m') . '.json', 'c+');
    if (!$fichier) {
        return;
    }
    if (flock($fichier, LOCK_EX)) {
        $mois = json_decode((string) stream_get_contents($fichier), true) ?: [];
        $jour = date('d');
        $mois[$jour][$evenement] = ($mois[$jour][$evenement] ?? 0) + 1;
        ftruncate($fichier, 0);
        rewind($fichier);
        fwrite($fichier, json_encode($mois));
        fflush($fichier);
        flock($fichier, LOCK_UN);
    }
    fclose($fichier);
}

/** Totaux par événement et détail par jour sur les $jours derniers jours. */
function stats_periode(int $jours = 30): array
{
    $totaux = array_fill_keys(STATS_EVENEMENTS, 0);
    $parJour = [];
    $moisCharges = [];
    for ($i = $jours - 1; $i >= 0; $i--) {
        $ts = strtotime("-$i days");
        $cleMois = date('Y-m', $ts);
        if (!isset($moisCharges[$cleMois])) {
            $fichier = DATA . "/stats/$cleMois.json";
            $moisCharges[$cleMois] = is_file($fichier) ? (json_decode((string) file_get_contents($fichier), true) ?: []) : [];
        }
        $jour = $moisCharges[$cleMois][date('d', $ts)] ?? [];
        $parJour[date('Y-m-d', $ts)] = (int) ($jour['vues'] ?? 0);
        foreach (STATS_EVENEMENTS as $evenement) {
            $totaux[$evenement] += (int) ($jour[$evenement] ?? 0);
        }
    }
    return ['totaux' => $totaux, 'vues_par_jour' => $parJour];
}
