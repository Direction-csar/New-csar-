<?php

return [
    /*
    | Portail public des demandes — règles de validation configurables.
    | Les expressions régulières sont ajustables via l'environnement sans
    | toucher au code, conformément à la consigne « validation configurable ».
    */

    // Téléphone sénégalais : +221 suivi de 9 chiffres.
    // Mobiles : 70, 75, 76, 77, 78 — Fixes : 33.
    'phone_regex' => env('DEMANDE_PHONE_REGEX', '/^\+221(33|70|75|76|77|78)[0-9]{7}$/'),

    // CNI — particuliers uniquement. Format volontairement permissif :
    // aucun algorithme de clé n'est imposé tant que la règle officielle
    // n'est pas confirmée institutionnellement.
    'cni_regex' => env('DEMANDE_CNI_REGEX', '/^[0-9]+$/'),
    'cni_required' => env('DEMANDE_CNI_REQUIRED', false),

    // NINEA / identifiant d'organisation — permissif, configurable.
    'ninea_regex' => env('DEMANDE_NINEA_REGEX', '/^[0-9A-Za-z]{4,20}$/'),

    'upload' => [
        'letter_max_kb' => (int) env('DEMANDE_LETTER_MAX_KB', 5120),
        'attachment_max_kb' => (int) env('DEMANDE_ATTACHMENT_MAX_KB', 5120),
        'max_attachments' => (int) env('DEMANDE_MAX_ATTACHMENTS', 5),
        // Extensions autorisées — le type MIME réel est contrôlé côté serveur.
        'allowed_extensions' => ['pdf', 'docx', 'jpg', 'jpeg', 'png'],
        'allowed_mimes' => [
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/png',
        ],
        // Disque privé : jamais public/storage.
        'disk' => 'local',
        'letter_dir' => 'demandes/lettres',
        'attachments_dir' => 'demandes/justificatifs',
    ],

    'types' => [
        'aide_alimentaire' => 'Aide alimentaire',
        'demande_audience' => "Demande d'audience",
        'information_generale' => 'Information générale',
        'autre' => 'Autre demande',
    ],

    // Type de demandeur — aucune distinction par religion.
    'demandeur_types' => [
        'particulier' => 'Un particulier',
        'association' => 'Une association ou organisation communautaire',
        'organisation_religieuse' => 'Une organisation religieuse (dahira, mosquée, paroisse, diocèse, comité religieux…)',
        'comite_evenement' => "Un comité d'organisation d'événement",
        'entreprise' => 'Une entreprise',
        'organisme_public' => 'Un organisme public ou parapublic',
        'autre' => 'Autre structure',
    ],

    // Profils pouvant renseigner les détails d'un événement (facultatifs).
    'event_profiles' => ['organisation_religieuse', 'comite_evenement'],
];
