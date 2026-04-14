<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Newsletter Service Configuration
    |--------------------------------------------------------------------------
    | Providers: mailchimp, sendgrid, brevo (sendinblue)
    */
    'newsletter' => [
        'provider' => env('NEWSLETTER_PROVIDER', 'mailchimp'),
        'api_key' => env('NEWSLETTER_API_KEY'),
        'list_id' => env('NEWSLETTER_LIST_ID'),
        'from_name' => env('NEWSLETTER_FROM_NAME', 'CSAR'),
        'reply_to' => env('NEWSLETTER_REPLY_TO', 'noreply@csar.sn'),
        'sender_id' => env('NEWSLETTER_SENDER_ID'), // Pour SendGrid
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Service Configuration
    |--------------------------------------------------------------------------
    | Providers: orange, twilio, vonage, infobip, africastalking
    | Orange SMS Sénégal API 2.0 - Client ID + Secret depuis developer.orange.com
    */
    'sms' => [
        'provider' => env('SMS_PROVIDER', 'orange'),
        'max_per_month' => env('SMS_MAX_PER_MONTH', 1000),

        'orange' => [
            'client_id' => env('ORANGE_SMS_CLIENT_ID'),
            'client_secret' => env('ORANGE_SMS_CLIENT_SECRET'),
            'sender_name' => env('ORANGE_SMS_SENDER', 'CSAR'),
            'country_code' => env('ORANGE_SMS_COUNTRY_CODE', '221'),
            'sender_number' => env('ORANGE_SMS_SENDER_NUMBER', '2210000'),
        ],

        'twilio' => [
            'account_sid' => env('TWILIO_ACCOUNT_SID'),
            'auth_token' => env('TWILIO_AUTH_TOKEN'),
            'from_number' => env('TWILIO_FROM_NUMBER'),
        ],

        'vonage' => [
            'api_key' => env('VONAGE_API_KEY'),
            'api_secret' => env('VONAGE_API_SECRET'),
            'from' => env('VONAGE_FROM', 'CSAR'),
        ],

        'infobip' => [
            'api_key' => env('INFOBIP_API_KEY'),
            'base_url' => env('INFOBIP_BASE_URL', 'https://api.infobip.com'),
            'from' => env('INFOBIP_FROM', 'CSAR'),
        ],

        'africastalking' => [
            'username' => env('AFRICASTALKING_USERNAME'),
            'api_key' => env('AFRICASTALKING_API_KEY'),
            'from' => env('AFRICASTALKING_FROM', 'CSAR'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Google OAuth (Admin Login)
    |--------------------------------------------------------------------------
    | Configuration pour la connexion admin via Gmail.
    | Créer les identifiants sur https://console.cloud.google.com/apis/credentials
    | URI de redirection autorisé : {APP_URL}/admin/login/google/callback
    */
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL', 'http://localhost:8000') . '/admin/login/google/callback'),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenWeatherMap API
    |--------------------------------------------------------------------------
    | Clé API depuis https://openweathermap.org/api
    | Plan gratuit : 60 appels/minute, 1 000 000/mois — suffisant avec cache.
    */
    'openweather' => [
        'key' => env('OPENWEATHER_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | PayPal Payment Gateway
    |--------------------------------------------------------------------------
    | Configuration pour l'intégration PayPal (paiement international)
    | https://developer.paypal.com
    |
    | Mode: 'sandbox' pour test, 'live' pour production
    */
    'paypal' => [
        'mode' => env('PAYPAL_MODE', 'sandbox'),
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        
        // URLs de callback
        'return_url' => env('PAYPAL_RETURN_URL'),
        'cancel_url' => env('PAYPAL_CANCEL_URL'),
        
        // Webhook
        'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
        
        // Configuration des montants (en USD)
        'min_amount' => env('PAYPAL_MIN_AMOUNT', 1),
        'max_amount' => env('PAYPAL_MAX_AMOUNT', 10000),
        'currency' => env('PAYPAL_CURRENCY', 'USD'),
        
        // URLs API PayPal
        'base_url_sandbox' => 'https://api-m.sandbox.paypal.com',
        'base_url_live' => 'https://api-m.paypal.com',
    ],

    /*
    |--------------------------------------------------------------------------
    | PayDunya Payment Gateway
    |--------------------------------------------------------------------------
    | Configuration pour l'intégration PayDunya (paiement mobile Sénégal)
    | https://paydunya.com
    |
    | Mode: 'test' pour sandbox, 'live' pour production
    | Clés disponibles sur le dashboard PayDunya après création d'application
    */
    'paydunya' => [
        'mode' => env('PAYDUNYA_MODE', 'test'),
        'api_key' => env('PAYDUNYA_API_KEY'),
        'private_key' => env('PAYDUNYA_PRIVATE_KEY'),
        'token' => env('PAYDUNYA_TOKEN'),
        'master_key' => env('PAYDUNYA_MASTER_KEY'),

        // URLs de callback (générées automatiquement si null)
        'callback_url' => env('PAYDUNYA_CALLBACK_URL'),
        'return_url' => env('PAYDUNYA_RETURN_URL'),
        'cancel_url' => env('PAYDUNYA_CANCEL_URL'),

        // Configuration des montants
        'min_amount' => env('PAYDUNYA_MIN_AMOUNT', 500),
        'max_amount' => env('PAYDUNYA_MAX_AMOUNT', 1000000),
        'currency' => env('PAYDUNYA_CURRENCY', 'XOF'),

        // URLs API PayDunya
        'base_url_test' => 'https://app.paydunya.com/sandbox-api/v1',
        'base_url_live' => 'https://app.paydunya.com/api/v1',
    ],

    /*
    |--------------------------------------------------------------------------
    | LinkedIn Integration
    |--------------------------------------------------------------------------
    | URL de la page entreprise LinkedIn du CSAR.
    | LINKEDIN_FEED_EMBED : code d'intégration Elfsight/EmbedSocial (optionnel).
    | Créer un widget gratuit sur https://elfsight.com/linkedin-feed-widget/
    */
    'linkedin' => [
        'company_url' => env('LINKEDIN_COMPANY_URL', 'https://www.linkedin.com/company/commissariat-%C3%A0-la-s%C3%A9curit%C3%A9-alimentaire-et-%C3%A0-la-r%C3%A9silience/posts'),
        'feed_embed_code' => env('LINKEDIN_FEED_EMBED', '<script src="https://elfsightcdn.com/platform.js" async></script><div class="elfsight-app-b8e60e2e-9795-4930-974e-fc3bb6e9c79b" data-elfsight-app-lazy></div>'),
    ],
];
