# Laravel Juegos

Este proyecto es una plataforma web de juegos hecha con Laravel.

La idea principal es que la web funcione como un pequeño CRM de juegos:

- los usuarios pueden registrarse e iniciar sesión,
- según su rol pueden gestionar o jugar,
- los juegos se muestran dentro de la propia plataforma,
- Laravel guarda la información de usuarios, juegos y partidas.

## Qué tecnologías se han usado

Estas son las herramientas principales del proyecto y para qué se usan:

- `Laravel`: es la base del proyecto. Se encarga de las rutas, la autenticación, los permisos, la base de datos y la API.
- `Inertia + React`: se usan para la parte visual del CRM. Laravel sigue controlando el acceso, pero la interfaz se pinta con React.
- `PostgreSQL`: es la base de datos principal del proyecto.
- `Three.js`: se usa para los juegos del catálogo.
- `Docker`: se usa para levantar el proyecto de forma más cómoda junto con PostgreSQL.
- `Vite`: se usa para compilar los archivos del frontend.

## Cómo está organizado el proyecto

El proyecto está separado en dos partes:

- `routes/web.php`: aquí están las rutas de la web, como login, dashboard, catálogo y gestión.
- `routes/api.php`: aquí están las rutas que usan los juegos para comunicarse con Laravel.

De esta forma, una cosa es la web que ve el usuario y otra la API que usan los juegos.

## Base de datos

La base de datos del proyecto está preparada para trabajar con PostgreSQL.

En el archivo `.env` se configura la conexión con estos datos:

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=laraveljuegos
DB_USERNAME=laravel
DB_PASSWORD=secret
```

## Qué tablas principales tiene

Estas son las partes más importantes guardadas en base de datos:

- `users`: usuarios de la plataforma.
- `roles`: roles disponibles.
- `role_user`: relación entre usuarios y roles.
- `games`: catálogo de juegos.
- `game_sessions`: partidas o sesiones de juego.
- `emotion_readings`: lecturas emocionales enviadas durante una partida.

## Usuarios y roles

Hay tres roles principales:

- `admin`: puede gestionar el sistema y los juegos.
- `manager`: puede gestionar los juegos.
- `player`: puede entrar al catálogo y jugar.

Los permisos se controlan desde Laravel, no desde el frontend. Eso significa que un jugador no puede entrar en gestión aunque escriba la URL manualmente.

## Autenticación

La autenticación es real y funcional.

El sistema permite:

- registrarse,
- iniciar sesión,
- cerrar sesión,
- proteger rutas para que solo entren usuarios autorizados.

## Parte visual del CRM

La interfaz del proyecto está hecha con Inertia y React.

Las pantallas principales son:

- login,
- registro,
- dashboard,
- catálogo,
- detalle de juego,
- gestión de juegos,
- formulario para crear o editar juegos.

Laravel sigue llevando el control de las rutas, la seguridad y los permisos. React solo se usa para mostrar la interfaz y mover al usuario por la aplicación.

## Gestión de juegos

El sistema tiene un CRUD de juegos para administrador y gestor.

Cada juego guarda como mínimo:

- título,
- descripción,
- instrucciones,
- estado,
- ruta o URL del juego,
- usuario creador.

Desde gestión se puede:

- crear juegos,
- editar juegos,
- publicar o despublicar,
- eliminar,
- ver la ficha del juego.

## Experiencia del jugador

El jugador solo ve los juegos publicados.

Los juegos se cargan dentro de la plataforma, no como una web separada. Así la experiencia queda integrada con el resto del sistema.

Los juegos actuales están en `public/games` y usan Three.js.

Ahora mismo el catálogo incluye:

- `Laberinto Cósmico`
- `Orb Rush`
- `Carril Neón`

Además, antes de empezar una partida hay una cuenta atrás de 5 segundos.

## API del proyecto

La API sirve para que los juegos puedan hablar con Laravel.

Por ejemplo, permite:

- obtener la lista de juegos,
- iniciar una partida,
- terminar una partida,
- enviar lecturas emocionales.

Algunas rutas importantes son:

- `GET /api/games`
- `POST /api/sessions/start`
- `PATCH /api/sessions/{session}/finish`
- `POST /api/sessions/{session}/emotions`

## Cómo se ha hecho cada parte de la práctica

### 1. Base del proyecto

Se creó un proyecto Laravel y se organizó separando controladores, modelos, middleware, rutas web y rutas API.

### 2. Base de datos

Se prepararon migraciones para usuarios, roles, juegos, sesiones y emociones. También se configuró PostgreSQL como base principal.

### 3. Autenticación

Se implementó registro, inicio de sesión y cierre de sesión con Laravel. Las rutas privadas están protegidas.

### 4. Roles y permisos

Se añadieron los roles `admin`, `manager` y `player`, y se usa middleware para decidir quién puede entrar a cada zona.

### 5. API

La API está separada de la parte web y devuelve datos en formato JSON para que los juegos puedan conectarse con Laravel.

### 6. Gestión de juegos

Se creó un panel donde administrador y gestor pueden crear, editar, publicar y revisar juegos sin tener que tocar el código del juego.

### 7. Catálogo y juego dentro de la plataforma

El jugador entra al catálogo, ve solo juegos publicados y los abre dentro de la propia aplicación.

## Cómo arrancar el proyecto

### 1. Instalar y compilar el frontend

```bash
docker run --rm -v "$PWD":/app -w /app node:22 npm install
docker run --rm -v "$PWD":/app -w /app node:22 npm run build
```

### 2. Levantar el proyecto

```bash
docker compose up --build
```

Esto deja levantado:

- Laravel,
- PostgreSQL,
- migraciones,
- datos de prueba.

No borra la base de datos en cada arranque. Si quieres reiniciar todo desde cero, entonces sí puedes usar:

```bash
docker compose exec app php artisan migrate:fresh --seed
```

La aplicación queda disponible en:

[http://127.0.0.1:8000](http://127.0.0.1:8000)

## Credenciales de prueba

- `admin@laraveljuegos.test` / `password`
- `gestor@laraveljuegos.test` / `password`
- `jugador@laraveljuegos.test` / `password`
