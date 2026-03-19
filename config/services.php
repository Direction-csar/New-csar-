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
