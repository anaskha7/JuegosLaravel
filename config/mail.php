<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mailer por defecto
    |--------------------------------------------------------------------------
    |
    | Esta opcion controla el mailer por defecto que se usa para enviar todos
    | los correos, salvo que se indique otro de forma explicita al mandar el
    | mensaje. Los mailers adicionales se configuran en el array "mailers".
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Configuraciones de mailer
    |--------------------------------------------------------------------------
    |
    | Aqui puedes configurar todos los mailers usados por la aplicacion y
    | sus ajustes. Se incluyen varios ejemplos y puedes anadir los tuyos
    | propios segun lo necesite tu proyecto.
    |
    | Laravel soporta distintos drivers de transporte de correo que pueden
    | usarse al enviar emails. Aqui puedes indicar cual usa cada mailer y
    | tambien anadir mailers extra si los necesitas.
    |
    | Soportados: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |             "postmark", "resend", "log", "array",
    |             "failover", "roundrobin"
    |
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
            'retry_after' => 60,
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
            'retry_after' => 60,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Direccion global de remitente
    |--------------------------------------------------------------------------
    |
    | Puede que quieras que todos los correos enviados por tu aplicacion
    | salgan desde la misma direccion. Aqui puedes indicar el nombre y la
    | direccion que se usaran globalmente en todos los envios.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],

];
