<?php

/**
 * Configuration CORS — Plateforme CSAR
 *
 * Politique restrictive : seules les origines explicitement listées peuvent
 * appeler l'API. Les routes web / admin / dg / ctc sont same-origin et n'ont
 * pas besoin de CORS.
 *
 * Référence OWASP : A05 Security Misconfiguration.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS Options
    |--------------------------------------------------------------------------
    */

    'paths' => [
        'api/*',
        'mobile-api/*',
        'sanctum/csrf-cookie',
    ],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    /**
     * Origines autorisées pour les apps mobiles CSAR (CSARSimCollect, mobile-app)
     * et éventuellement un dashboard tiers.
     *
     * En production, ne JAMAIS utiliser ['*'] — toujours lister explicitement.
     */
    'allowed_origins' => [
        env('APP_URL', 'https://csar.sn'),
        'https://csar.sn',
        'https://www.csar.sn',
        // Apps mobiles internes (à compléter avec les domaines réels)
        // 'https://app.csar.sn',
    ],

    'allowed_origins_patterns' => [
        // Autoriser les sous-domaines csar.sn uniquement.
        '#^https://([a-z0-9-]+\.)?csar\.sn$#',
    ],

    'allowed_headers' => [
        'Accept',
        'Authorization',
        'Content-Type',
        'X-Requested-With',
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
        'X-Socket-Id',
    ],

    'exposed_headers' => [],

    'max_age' => 7200, // 2h

    'supports_credentials' => true,
];
