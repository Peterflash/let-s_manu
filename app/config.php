<?php
/**
 * Réglages par défaut du site.
 * Tout ce qui figure sous 'reglages' peut être modifié dans l'admin (Paramètres) :
 * les valeurs saisies sont enregistrées dans data/settings.json et priment sur celles-ci.
 */
return [
    'reglages' => [
        // Identité légale
        'raison_sociale'   => "LET'S MANU SERVICES",
        'nom_court'        => "LET'S MANU",
        'forme_juridique'  => 'SARL',
        'annee_creation'   => '2022',
        'idu'              => 'CI-2022-0035901 B',
        'gerante'          => 'MELEDJE ADJIBA MARIE ANGE LAETHITHIA',

        // Contact
        'telephone'        => '07 47 31 41 39',   // affiché
        'telephone_intl'   => '+2250747314139',   // lien tel:
        'whatsapp'         => '2250747314139',    // numéro wa.me, sans + ni espaces
        'email'            => 'letsmanou@gmail.com',
        'email_devis'      => 'letsmanou@gmail.com', // reçoit les demandes de devis
        'email_expediteur' => '',                   // ex. no-reply@letsmanu.ci (vide = automatique)

        // Adresse (repère) et carte
        'ville'            => 'Abidjan',
        'adresse_fr'       => 'Koumassi SICOGI, non loin du Groupe scolaire Collectif 1-2-3-4, villa 625',
        'adresse_en'       => 'Koumassi SICOGI, near Groupe scolaire Collectif 1-2-3-4, villa 625',
        'gps_lat'          => '',
        'gps_lng'          => '',
        'carte_recherche'  => 'Koumassi SICOGI, Abidjan, Côte d\'Ivoire',

        // Horaires (à confirmer par l'entreprise)
        'horaires_fr'      => "Lundi au vendredi : 8 h – 17 h\nSamedi : 8 h – 12 h",
        'horaires_en'      => "Monday to Friday: 8 am – 5 pm\nSaturday: 8 am – 12 pm",

        // Réseaux sociaux (laisser vide pour masquer)
        'linkedin'         => '',
        'facebook'         => '',

        // Référencement et mesure d'audience
        'site_url'         => '',  // ex. https://letsmanu.ci (vide = détecté automatiquement)
        'ga4_id'           => '',  // ex. G-XXXXXXXXXX (vide = Google Analytics désactivé)

        // Anti-spam Cloudflare Turnstile (facultatif, en plus des protections intégrées)
        'turnstile_site'   => '',
        'turnstile_secret' => '',

        // Hébergeur (mentions légales) — à vérifier lors de la souscription
        'hebergeur_nom'     => 'Hostinger International Ltd',
        'hebergeur_adresse' => '61 Lordou Vironos Street, 6023 Larnaca, Chypre',
        'hebergeur_site'    => 'https://www.hostinger.com',
    ],

    // Pôles et sous-services (les textes sont dans app/content/*.php)
    'poles' => [
        'industriel'   => ['soudure', 'tuyauterie', 'maintenance', 'sablage', 'levage', 'btp', 'electricite'],
        'construction' => ['hangars', 'charpentes', 'magasins', 'ouvrages'],
    ],

    // Secteurs servis (clé => icône)
    'secteurs' => [
        'mines'           => 'pioche',
        'petrole-gaz'     => 'goutte',
        'energie'         => 'eclair',
        'electricite'     => 'prise',
        'cimenterie'      => 'silo',
        'agroalimentaire' => 'feuille',
        'industrie'       => 'usine',
        'btp'             => 'casque',
    ],

    // Emplacements de photos remplaçables depuis l'admin (clé => libellé)
    'emplacements' => [
        'accueil'     => "Bandeau de la page d'accueil",
        'soudure'     => 'Soudure et chaudronnerie',
        'tuyauterie'  => 'Tuyauterie industrielle',
        'maintenance' => 'Maintenance et réparation',
        'sablage'     => 'Sablage et traitement de surface',
        'levage'      => 'Manutention et levage',
        'btp'         => 'BTP',
        'electricite' => 'Électricité',
        'hangars'     => 'Hangars et entrepôts',
        'charpentes'  => 'Charpentes et structures',
        'magasins'    => 'Magasins et bâtiments commerciaux',
        'ouvrages'    => 'Ouvrages divers',
        'gerante'     => 'Portrait de la gérante (À propos)',
        'equipe'      => "Photo de l'équipe (À propos)",
    ],

    // Formulaire de devis
    'devis' => [
        'photos_max'      => 5,
        'photo_poids_max' => 8 * 1024 * 1024, // 8 Mo par photo
    ],
];
