<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Servicios de terceros
    |--------------------------------------------------------------------------
    |
    | Este archivo sirve para guardar las credenciales de servicios de
    | terceros como Mailgun, Postmark, AWS y otros. Es la ubicacion habitual
    | para este tipo de informacion y facilita que los paquetes la encuentren.
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

    'facial' => [
        'url' => env('FACIAL_SERVICE_URL', ''),
        'timeout' => env('FACIAL_SERVICE_TIMEOUT', 60),
    ],

    'github' => [
        'token' => env('GITHUB_TOKEN'),
        'webhook_secret' => env('GITHUB_WEBHOOK_SECRET'),
    ],

    'ai_review' => [
        'provider' => env('AI_REVIEW_PROVIDER', 'openai'),
        'openai_key' => env('OPENAI_API_KEY'),
        'openai_model' => env('OPENAI_REVIEW_MODEL', 'gpt-4o-mini'),
        'gemini_key' => env('GEMINI_API_KEY'),
        'gemini_model' => env('GEMINI_REVIEW_MODEL', 'gemini-2.5-flash'),
    ],

];
