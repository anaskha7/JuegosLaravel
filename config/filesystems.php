<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Disco del sistema de archivos por defecto
    |--------------------------------------------------------------------------
    |
    | Aqui puedes indicar el disco del sistema de archivos que usara el
    | framework por defecto. Tienes disponible el disco "local" y tambien
    | varios discos basados en la nube para guardar archivos.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Discos del sistema de archivos
    |--------------------------------------------------------------------------
    |
    | Aqui puedes configurar tantos discos como necesites, e incluso varios
    | discos para un mismo driver. Se incluyen ejemplos de referencia para
    | la mayoria de drivers de almacenamiento soportados.
    |
    | Drivers soportados: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Enlaces simbolicos
    |--------------------------------------------------------------------------
    |
    | Aqui puedes configurar los enlaces simbolicos que se crearan al
    | ejecutar el comando `storage:link` de Artisan. Las claves del array
    | deben ser las ubicaciones del enlace y los valores sus destinos.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
