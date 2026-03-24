<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Nombre de la aplicacion
    |--------------------------------------------------------------------------
    |
    | Este valor es el nombre de tu aplicacion, y se usara cuando el
    | framework necesite mostrar el nombre de la aplicacion en una
    | notificacion u otros elementos de la interfaz.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Entorno de la aplicacion
    |--------------------------------------------------------------------------
    |
    | Este valor determina el "entorno" en el que se esta ejecutando la
    | aplicacion. Puede influir en como prefieres configurar los distintos
    | servicios que utiliza la aplicacion. Definelo en el archivo ".env".
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Modo debug de la aplicacion
    |--------------------------------------------------------------------------
    |
    | Cuando la aplicacion esta en modo debug, se mostraran mensajes de error
    | detallados con trazas en cada error que ocurra. Si esta desactivado,
    | se mostrara una pagina de error generica.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | URL de la aplicacion
    |--------------------------------------------------------------------------
    |
    | Esta URL la usa la consola para generar enlaces correctamente cuando
    | utilizas Artisan. Debes apuntarla a la raiz de la aplicacion para que
    | este disponible dentro de los comandos de Artisan.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Zona horaria de la aplicacion
    |--------------------------------------------------------------------------
    |
    | Aqui puedes indicar la zona horaria por defecto de la aplicacion, que
    | sera usada por las funciones de fecha y hora de PHP. Por defecto es
    | "UTC" porque suele servir para la mayoria de casos.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Configuracion regional de la aplicacion
    |--------------------------------------------------------------------------
    |
    | La configuracion regional determina el idioma por defecto que usaran
    | los metodos de traduccion y localizacion de Laravel. Puedes poner aqui
    | cualquier idioma para el que vayas a tener textos traducidos.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Clave de cifrado
    |--------------------------------------------------------------------------
    |
    | Esta clave la utilizan los servicios de cifrado de Laravel y debe ser
    | una cadena aleatoria de 32 caracteres para que los valores cifrados
    | sean seguros. Debes configurarla antes de desplegar la aplicacion.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Driver del modo mantenimiento
    |--------------------------------------------------------------------------
    |
    | Estas opciones determinan el driver usado para gestionar el estado de
    | "modo mantenimiento" de Laravel. El driver "cache" permite controlar
    | este modo entre varias maquinas.
    |
    | Drivers soportados: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
