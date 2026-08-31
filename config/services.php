<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'team_password' => env('TEAM_PASSWORD'),

    'billing' => [
        // Lista separada por comas de las pasarelas activas para facturación directa
        // (todo lo que NO sea un convenio/Contract, que siempre es 'manual').
        // Valores válidos: wompi, stripe. Por defecto: solo Wompi.
        //   BILLING_GATEWAYS=wompi            -> solo Wompi (por defecto)
        //   BILLING_GATEWAYS=stripe           -> solo Stripe
        //   BILLING_GATEWAYS=wompi,stripe     -> ambas habilitadas a la vez
        'gateways' => array_filter(explode(',', env('BILLING_GATEWAYS', 'wompi'))),
    ],

    'wompi' => [
        'private_key' => env('WOMPI_PRIVATE_KEY'),
        'events_secret' => env('WOMPI_EVENTS_SECRET'),
    ],

];
