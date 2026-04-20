# Plataforma de Juegos

## Qué es este proyecto

Este proyecto es una plataforma web de juegos.

La idea es sencilla:

- una persona se registra o inicia sesión,
- entra en la plataforma,
- ve un catálogo de juegos,
- abre un juego,
- juega dentro de la propia web,
- y el sistema guarda lo que ha pasado en la partida.

Además, hay una parte de gestión para las personas que administran la plataforma.

## Qué puede hacer cada tipo de usuario

En la plataforma hay 3 tipos de usuario:

- `Administrador`: puede controlar el sistema y gestionar los juegos.
- `Gestor`: puede crear juegos, editarlos y decidir si se publican o no.
- `Jugador`: puede entrar al catálogo y jugar a los juegos publicados.

Esto significa que no todo el mundo puede entrar en todas las zonas.

Por ejemplo:

- un jugador no puede entrar en la parte de gestión,
- un gestor sí puede trabajar con los juegos,
- y un administrador tiene el control completo.

## Qué hace la plataforma

La plataforma tiene varias partes.

### 1. Inicio de sesión y registro

Una persona puede:

- crear su cuenta,
- iniciar sesión,
- cerrar sesión,
- y volver a entrar más tarde.

Todo esto funciona de verdad. No es una simulación.

### 2. Catálogo de juegos

Cuando un jugador entra, ve una lista con los juegos que están publicados.

Solo aparecen los juegos que están listos para jugar.

Desde ese catálogo puede:

- ver el juego,
- entrar en su página,
- jugar sin salir de la plataforma.

### 3. Gestión de juegos

La parte de gestión sirve para organizar el catálogo.

Desde ahí se puede:

- crear un juego nuevo,
- cambiar el título,
- cambiar la descripción,
- cambiar la ruta o enlace del juego,
- publicar un juego,
- ocultar un juego,
- editar lo que ya existe.

La idea es que el juego se pueda gestionar desde la plataforma sin tener que tocar el código cada vez.

### 4. Partidas

Cuando una persona juega, la plataforma guarda información de esa partida.

Por ejemplo:

- quién ha jugado,
- a qué juego ha jugado,
- cuándo empezó,
- cuándo terminó,
- cuánto duró,
- y el resultado.

### 5. Chat en vivo

Al lado del juego hay un chat para que los jugadores puedan escribir en directo.

Sirve para:

- hablar mientras están en la partida,
- dejar mensajes,
- ver lo que escriben otras personas sin recargar la página.

### 6. Face ID

El login también tiene una opción de `Face ID`.

Funciona así:

- en la pantalla de login escribes tu email,
- eliges entrar con Face ID,
- el sistema hace una foto con la cámara,
- si es la primera vez, esa foto se guarda como referencia,
- si ya existe una foto guardada, la nueva se compara con la anterior,
- y si coincide, entras.

La web no compara la cara por sí sola.
La web envía las imágenes a un pequeño servicio aparte y ese servicio responde si coincide o no.

### 7. Estados de ánimo

Mientras una persona juega, la web también puede detectar el estado de ánimo usando la cámara.

La idea es simple:

- el navegador mira la cara en ese momento,
- detecta si la expresión parece neutral, alegre, triste, enfadada o sorprendida,
- y guarda solo ese dato simple dentro de la partida.

Es importante entender esto:

- no se guarda el vídeo,
- no se suben fotos al servidor para esta parte,
- y lo que se guarda no es la cara, sino una lectura simple del momento.

Esto sirve para relacionar cómo estaba el jugador con lo que iba pasando en la partida.

## Qué juegos hay ahora mismo

Ahora mismo el catálogo incluye varios juegos simples hechos para la plataforma.

Están pensados para que:

- se puedan abrir dentro de la web,
- ocupen su espacio dentro de la plataforma,
- y puedan conectarse con el sistema.

Antes de empezar la partida hay una cuenta atrás de 5 segundos.

## Cómo está hecho el proyecto

Aunque por fuera se vea como una sola web, por dentro está separado en varias partes para que todo esté más ordenado.

### La parte principal

La parte principal está hecha con `Laravel`.

Esta parte se encarga de:

- controlar el acceso,
- saber quién puede entrar a cada zona,
- guardar la información en la base de datos,
- y responder a los juegos cuando necesitan enviar datos.

### La parte visual

La parte que se ve en pantalla está hecha con `React` usando `Inertia`.

Dicho de forma simple:

- Laravel manda la lógica,
- y React pinta la interfaz para que se vea mejor y sea más cómoda.

### La base de datos

La información se guarda en `PostgreSQL`.

Ahí se guardan cosas como:

- usuarios,
- roles,
- juegos,
- partidas,
- mensajes del chat.

### Los juegos

Los juegos están hechos con `Three.js`.

Eso permite crear juegos visuales dentro del navegador.

Los juegos no están metidos dentro del código principal de Laravel.
Están aparte, pero conectados con la plataforma.

### El servicio facial

La parte de reconocimiento facial está hecha en `Python` y va en un contenedor aparte.

Su trabajo es uno muy concreto:

- recibir dos imágenes,
- compararlas,
- decir si son de la misma persona o no.

### Docker

Se usa `Docker` para que sea más fácil poner en marcha todo el proyecto.

En vez de instalar cada cosa por separado, Docker levanta los servicios necesarios ya preparados.

## Qué guarda la base de datos

Las partes más importantes que guarda el sistema son estas:

- usuarios,
- roles de usuario,
- juegos,
- partidas,
- mensajes del chat,
- referencia facial para el acceso con cámara.

## Cómo se comunican los juegos con la plataforma

Los juegos pueden hablar con la plataforma para enviar información.

Por ejemplo, un juego puede avisar de:

- que una partida ha empezado,
- que una partida ha terminado,
- el resultado final,
- o datos que se quieran guardar.

Esto sirve para que el juego y la plataforma trabajen juntos.

## Cómo arrancar el proyecto

La forma más fácil es con Docker.

### 1. Preparar el archivo de configuración

Copia el archivo de ejemplo:

```bash
cp .env.example .env
```

### 2. Levantar el proyecto

```bash
docker compose up --build
```

Con eso se levantan las partes principales del proyecto.

### 3. Abrir la web

La aplicación queda en:

[http://127.0.0.1:8000](http://127.0.0.1:8000)

## Cuentas de prueba

Para entrar rápido y probar la plataforma puedes usar estas cuentas:

- `admin@laraveljuegos.test` / `password`
- `gestor@laraveljuegos.test` / `password`
- `jugador@laraveljuegos.test` / `password`
