<?php

return [
    // Meta
    'meta' => [
        'title' => 'Faire un don - CSAR',
        'description' => 'Soutenez les actions du CSAR en faisant un don. Votre contribution aide à sauver des vies.',
    ],

    // Titres
    'title' => 'Faire un don',
    'subtitle' => 'Votre contribution fait la différence',

    // Formulaire
    'form' => [
        'personal_info' => 'Informations personnelles',
        'full_name' => 'Nom complet',
        'full_name_placeholder' => 'Entrez votre nom complet',
        'email' => 'Adresse email',
        'email_placeholder' => 'votre@email.com',
        'phone' => 'Numéro de téléphone',
        'phone_placeholder' => '+221 77 123 45 67',
        'anonymous' => 'Faire ce don anonymement',
        'donation_type' => 'Type de don',
        'one_time' => 'Don ponctuel',
        'monthly' => 'Don mensuel',
        'frequency' => 'Fréquence de prélèvement',
        'amount' => 'Montant du don',
        'custom_amount' => 'Montant personnalisé',
        'min_amount' => 'Montant minimum : :min FCFA',
        'payment_method' => 'Méthode de paiement',
        'message' => 'Message (optionnel)',
        'message_placeholder' => 'Votre message pour le CSAR...',
        'submit' => 'Confirmer le don',
        'secure_payment' => 'Paiement sécurisé et chiffré',
    ],

    // Fréquences
    'frequency' => [
        'monthly' => 'Mensuel',
        'quarterly' => 'Trimestriel',
        'yearly' => 'Annuel',
    ],

    // Providers
    'providers' => [
        'paydunya' => 'Mobile (Sénégal)',
        'paypal' => 'PayPal / Carte',
    ],

    // Méthodes de paiement
    'payment_methods' => [
        'wave' => [
            'name' => 'Wave',
            'description' => 'Paiement mobile avec Wave',
        ],
        'orange_money' => [
            'name' => 'Orange Money',
            'description' => 'Paiement mobile avec Orange Money',
        ],
        'credit_card' => [
            'name' => 'Carte bancaire',
            'description' => 'Visa, Mastercard',
        ],
        'paypal_balance' => [
            'name' => 'PayPal',
            'description' => 'Payer avec votre solde PayPal',
        ],
        'paypal_card' => [
            'name' => 'Carte bancaire',
            'description' => 'Visa, Mastercard, American Express',
        ],
    ],

    // Messages de conversion
    'conversion' => [
        'paypal_notice' => 'Le montant sera converti en USD (~:amount $) pour le traitement PayPal.',
    ],

    // Traitement
    'processing' => [
        'title' => 'Traitement en cours',
        'message' => 'Veuillez patienter pendant que nous préparons votre paiement...',
    ],

    // Validation
    'validation' => [
        'amount_min' => 'Le montant minimum est de :min FCFA',
        'amount_max' => 'Le montant maximum est de :max FCFA',
        'email_required' => 'L\'adresse email est requise',
        'name_required' => 'Le nom complet est requis',
    ],

    // Messages de succès/erreur
    'success' => [
        'payment_initiated' => 'Paiement initié avec succès. Redirection...',
        'thank_you' => 'Merci pour votre générosité !',
        'confirmation_sent' => 'Un email de confirmation vous a été envoyé.',
    ],

    'errors' => [
        'payment_creation_failed' => 'Impossible de créer le paiement. Veuillez réessayer.',
        'processing_error' => 'Une erreur est survenue lors du traitement.',
        'invalid_amount' => 'Montant invalide.',
        'payment_failed' => 'Le paiement a échoué.',
    ],

    // Statuts de paiement
    'status' => [
        'pending' => 'En attente',
        'success' => 'Réussi',
        'failed' => 'Échoué',
        'cancelled' => 'Annulé',
        'refunded' => 'Remboursé',
    ],
];
