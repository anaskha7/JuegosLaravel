<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Valores por defecto de autenticacion
    |--------------------------------------------------------------------------
    |
    | Esta opcion define el "guard" de autenticacion y el "broker" para
    | restablecer contrasenas por defecto. Puedes cambiarlos si lo necesitas,
    | pero son un buen punto de partida para la mayoria de aplicaciones.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Guards de autenticacion
    |--------------------------------------------------------------------------
    |
    | Aqui puedes definir cada guard de autenticacion de tu aplicacion.
    | Ya tienes una configuracion por defecto que utiliza sesiones y el
    | proveedor de usuarios basado en Eloquent.
    |
    | Todos los guards tienen un proveedor de usuarios, que define como se
    | recuperan los usuarios desde la base de datos u otro sistema de
    | almacenamiento. Normalmente se utiliza Eloquent.
    |
    | Soportado: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Proveedores de usuarios
    |--------------------------------------------------------------------------
    |
    | Todos los guards tienen un proveedor de usuarios, que define como se
    | recuperan los usuarios desde la base de datos u otro sistema de
    | almacenamiento. Normalmente se utiliza Eloquent.
    |
    | Si tienes varias tablas o modelos de usuarios, puedes configurar varios
    | proveedores para representar cada modelo o tabla. Despues podras
    | asignarlos a los guards extra que definas.
    |
    | Soportados: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Restablecimiento de contrasenas
    |--------------------------------------------------------------------------
    |
    | Estas opciones definen el comportamiento del restablecimiento de
    | contrasenas de Laravel, incluyendo la tabla donde se guardan los
    | tokens y el proveedor de usuarios que se utilizara.
    |
    | El tiempo de expiracion es el numero de minutos durante los que cada
    | token sera valido. Esta medida de seguridad hace que duren poco tiempo
    | y sean mas dificiles de adivinar. Puedes cambiarlo si lo necesitas.
    |
    | El valor de throttle indica cuantos segundos debe esperar un usuario
    | antes de generar mas tokens. Esto evita que se creen demasiados
    | tokens de restablecimiento en muy poco tiempo.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tiempo limite de confirmacion de contrasena
    |--------------------------------------------------------------------------
    |
    | Aqui puedes definir cuantos segundos pasan antes de que expire la
    | confirmacion de contrasena y el usuario tenga que volver a escribirla.
    | Por defecto, el tiempo limite es de tres horas.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
