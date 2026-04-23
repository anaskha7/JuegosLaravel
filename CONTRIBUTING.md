## Flujo de trabajo

Este proyecto se trabaja como si fuera un equipo real.

### 1. Antes de tocar código

- Crea o revisa un issue en GitHub.
- Explica en el issue qué problema hay o qué mejora se quiere hacer.
- Si el cambio afecta a integración, chat, juegos o login, deja claro qué parte se toca.

### 2. Crear una rama

- Cada cambio sale desde una rama propia.
- Usa nombres claros, por ejemplo:
  - `feature/chat-rabbitmq`
  - `fix/login-face-id`
  - `chore/github-workflow`

### 3. Hacer el cambio

- Mantén el cambio pequeño y con una responsabilidad clara.
- Si añades lógica nueva, intenta cubrirla con tests.
- Si el cambio afecta a GitHub, RabbitMQ o IA, comprueba también el flujo completo.

### 4. Abrir la pull request

- Abre una PR contra `main`.
- Usa la plantilla de PR del repositorio.
- Resume qué cambia, cómo se ha probado y si hay algo pendiente.

### 5. Revisión automática

- La CI comprueba tests y build.
- Cuando se abre o actualiza una PR, GitHub puede avisar a Laravel mediante webhook.
- Laravel manda ese evento a RabbitMQ y una tarea en cola puede lanzar una revisión automática con IA.

### 6. Cerrar el cambio

- Si la revisión está bien y la CI pasa, la PR se puede fusionar.
- El issue relacionado debe quedar enlazado en la PR para que el historial del trabajo sea claro.
