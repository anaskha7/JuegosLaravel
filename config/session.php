<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Driver de sesion por defecto
    |--------------------------------------------------------------------------
    |
    | Esta opcion determina el driver de sesion por defecto que se usa para
    | las peticiones entrantes. Laravel soporta varias opciones para guardar
    | las sesiones y la base de datos es una buena opcion por defecto.
    |
    | Soportados: "file", "cookie", "database", "memcached",
    |             "redis", "dynamodb", "array"
    |
    */

    'driver' => env('SESSION_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Duracion de la sesion
    |--------------------------------------------------------------------------
    |
    | Aqui puedes indicar cuantos minutos puede permanecer inactiva una
    | sesion antes de expirar. Si quieres que caduque al cerrar el
    | navegador, puedes indicarlo con la opcion expire_on_close.
    |
    */

    'lifetime' => (int) env('SESSION_LIFETIME', 120),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    /*
    |--------------------------------------------------------------------------
    | Cifrado de la sesion
    |--------------------------------------------------------------------------
    |
    | Esta opcion permite indicar facilmente que todos los datos de sesion
    | deben cifrarse antes de guardarse. Laravel realiza el cifrado de forma
    | automatica y puedes usar la sesion con normalidad.
    |
    */

    'encrypt' => env('SESSION_ENCRYPT', false),

    /*
    |--------------------------------------------------------------------------
    | Ubicacion de los archivos de sesion
    |--------------------------------------------------------------------------
    |
    | Cuando utilizas el driver de sesion "file", los archivos de sesion se
    | guardan en disco. Aqui se define la ubicacion por defecto, aunque
    | puedes indicar otra si lo necesitas.
    |
    */

    'files' => storage_path('framework/sessions'),

    /*
    |--------------------------------------------------------------------------
    | Conexion de base de datos para sesiones
    |--------------------------------------------------------------------------
    |
    | Cuando usas los drivers de sesion "database" o "redis", puedes indicar
    | la conexion que debe usarse para gestionar esas sesiones. Debe
    | corresponder con una conexion definida en la configuracion.
    |
    */

    'connection' => env('SESSION_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Tabla de base de datos para sesiones
    |--------------------------------------------------------------------------
    |
    | Cuando usas el driver de sesion "database", puedes indicar la tabla en
    | la que se guardaran las sesiones. Ya hay una opcion razonable por
    | defecto, pero puedes cambiarla por otra tabla.
    |
    */

    'table' => env('SESSION_TABLE', 'sessions'),

    /*
    |--------------------------------------------------------------------------
    | Store de cache para sesiones
    |--------------------------------------------------------------------------
    |
    | Cuando utilizas un backend de sesiones basado en cache, puedes definir
    | el store de cache que debe usarse para guardar los datos de sesion
    | entre peticiones. Debe coincidir con uno de tus stores definidos.
    |
    | Afecta a: "dynamodb", "memcached", "redis"
    |
    */

    'store' => env('SESSION_STORE'),

    /*
    |--------------------------------------------------------------------------
    | Loteria de limpieza de sesiones
    |--------------------------------------------------------------------------
    |
    | Algunos drivers de sesion deben limpiar manualmente su ubicacion de
    | almacenamiento para eliminar sesiones antiguas. Aqui se define la
    | probabilidad de que ocurra en una peticion. Por defecto es 2 de 100.
    |
    */

    'lottery' => [2, 100],

    /*
    |--------------------------------------------------------------------------
    | Nombre de la cookie de sesion
    |--------------------------------------------------------------------------
    |
    | Aqui puedes cambiar el nombre de la cookie de sesion que crea el
    | framework. Normalmente no hace falta modificar este valor porque no
    | aporta una mejora de seguridad significativa.
    |
    */

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug((string) env('APP_NAME', 'laravel')).'-session'
    ),

    /*
    |--------------------------------------------------------------------------
    | Ruta de la cookie de sesion
    |--------------------------------------------------------------------------
    |
    | La ruta de la cookie de sesion determina en que ruta se considerara
    | disponible la cookie. Normalmente sera la raiz de la aplicacion, pero
    | puedes cambiarla si lo necesitas.
    |
    */

    'path' => env('SESSION_PATH', '/'),

    /*
    |--------------------------------------------------------------------------
    | Dominio de la cookie de sesion
    |--------------------------------------------------------------------------
    |
    | Este valor determina en que dominio y subdominios estara disponible la
    | cookie de sesion. Por defecto se aplica al dominio raiz sin
    | subdominios. Normalmente no deberia cambiarse.
    |
    */

    'domain' => env('SESSION_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Cookies solo para HTTPS
    |--------------------------------------------------------------------------
    |
    | Si activas esta opcion, las cookies de sesion solo se enviaran al
    | servidor cuando el navegador use una conexion HTTPS. Asi se evita que
    | la cookie viaje cuando no puede hacerse de forma segura.
    |
    */

    'secure' => env('SESSION_SECURE_COOKIE'),

    /*
    |--------------------------------------------------------------------------
    | Acceso solo por HTTP
    |--------------------------------------------------------------------------
    |
    | Si este valor es true, JavaScript no podra acceder al valor de la
    | cookie y esta solo sera accesible por protocolo HTTP. Lo normal es no
    | desactivar esta opcion.
    |
    */

    'http_only' => env('SESSION_HTTP_ONLY', true),

    /*
    |--------------------------------------------------------------------------
    | Cookies Same-Site
    |--------------------------------------------------------------------------
    |
    | Esta opcion determina como se comportan tus cookies cuando hay
    | peticiones entre sitios y puede ayudar a mitigar ataques CSRF. Por
    | defecto se usa el valor "lax" para permitir peticiones seguras.
    |
    | Ver: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie#samesitesamesite-value
    |
    | Soportados: "lax", "strict", "none", null
    |
    */

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    /*
    |--------------------------------------------------------------------------
    | Cookies particionadas
    |--------------------------------------------------------------------------
    |
    | Si este valor es true, la cookie quedara ligada al sitio de nivel
    | superior en un contexto cross-site. El navegador acepta cookies
    | particionadas cuando son "secure" y Same-Site vale "none".
    |
    */

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

];
