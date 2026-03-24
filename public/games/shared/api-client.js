const params = new URLSearchParams(window.location.search);

const runtime = {
    gameId: Number(params.get('game_id') || 0),
    token: params.get('token') || '',
    sessionId: null,
    startedAt: null,
};

export function mountHud({ title, instructions }) {
    const hud = document.createElement('div');
    hud.className = 'hud';
    hud.innerHTML = `
        <div class="panel">
            <strong>${title}</strong>
            <p>${instructions}</p>
        </div>
        <div class="panel status">
            <strong>Estado</strong>
            <p id="status-line">Preparando sesión...</p>
        </div>
    `;

    const footer = document.createElement('div');
    footer.className = 'overlay';
    footer.id = 'score-line';
    footer.textContent = 'Puntuación: 0';

    document.body.append(hud, footer);
}

export function setStatus(text) {
    const node = document.getElementById('status-line');
    if (node) {
        node.textContent = text;
    }
}

export function setScore(value) {
    const node = document.getElementById('score-line');
    if (node) {
        node.textContent = `Puntuación: ${value}`;
    }
}

export async function runCountdown(seconds = 5) {
    const overlay = document.createElement('div');
    overlay.className = 'countdown';
    overlay.innerHTML = `
        <div class="countdown-card">
            <p class="countdown-label">La partida empieza en</p>
            <p class="countdown-number">${seconds}</p>
        </div>
    `;

    document.body.appendChild(overlay);
    setStatus(`Comenzando en ${seconds}s`);

    for (let remaining = seconds; remaining >= 1; remaining -= 1) {
        overlay.querySelector('.countdown-number').textContent = String(remaining);
        setStatus(`Comenzando en ${remaining}s`);
        await new Promise((resolve) => window.setTimeout(resolve, 1000));
    }

    overlay.remove();
}

async function apiFetch(path, options = {}) {
    if (!runtime.token) {
        throw new Error('Token API no disponible.');
    }

    const response = await fetch(path, {
        ...options,
        headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${runtime.token}`,
            ...(options.headers || {}),
        },
    });

    if (!response.ok) {
        throw new Error(`API ${response.status}`);
    }

    return response.json();
}

export async function startSession(metadata = {}) {
    if (!runtime.gameId || !runtime.token) {
        setStatus('Juego en modo demo sin backend.');
        return;
    }

    const payload = await apiFetch('/api/sessions/start', {
        method: 'POST',
        body: JSON.stringify({
            game_id: runtime.gameId,
            started_at: new Date().toISOString(),
            metadata,
        }),
    });

    runtime.sessionId = payload.data.id;
    runtime.startedAt = payload.data.started_at;
    setStatus(`Sesión #${runtime.sessionId} iniciada`);
}

export async function finishSession(score, metadata = {}) {
    if (!runtime.sessionId) {
        return;
    }

    try {
        await apiFetch(`/api/sessions/${runtime.sessionId}/finish`, {
            method: 'PATCH',
            body: JSON.stringify({
                ended_at: new Date().toISOString(),
                score,
                status: 'completed',
                metadata,
            }),
        });

        setStatus(`Partida registrada. Score ${score}`);
    } catch (error) {
        setStatus(`No se pudo cerrar la sesión: ${error.message}`);
    }
}

export async function pushEmotion(emotion, confidence, context = {}) {
    if (!runtime.sessionId) {
        return;
    }

    try {
        await apiFetch(`/api/sessions/${runtime.sessionId}/emotions`, {
            method: 'POST',
            body: JSON.stringify({
                emotion,
                confidence,
                captured_at: new Date().toISOString(),
                context,
            }),
        });
    } catch (_) {
        // Ignorado: la captura emocional es best-effort en esta fase.
    }
}
