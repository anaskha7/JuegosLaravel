import { Head, Link, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import GameChatPanel from '../../Components/GameChatPanel';
import EmotionDetector from '../../Components/EmotionDetector';

export default function CatalogShow({ game, chat, canPreview }) {
    const { auth } = usePage().props;
    const [activeSessionId, setActiveSessionId] = useState(null);

    useEffect(() => {
        setActiveSessionId(null);

        const handleMessage = (event) => {
            if (event.origin !== window.location.origin) {
                return;
            }

            if (event.data?.source !== 'laraveljuegos-game' || event.data?.gameId !== game.id) {
                return;
            }

            if (event.data.type === 'session-started') {
                setActiveSessionId(event.data.sessionId ?? null);
            }

            if (event.data.type === 'session-finished') {
                setActiveSessionId((currentSessionId) => (
                    currentSessionId === event.data.sessionId ? null : currentSessionId
                ));
            }
        };

        window.addEventListener('message', handleMessage);

        return () => {
            window.removeEventListener('message', handleMessage);
        };
    }, [game.id]);

    return (
        <AppLayout title={game.title}>
            <Head title={game.title} />
            <EmotionDetector apiToken={auth.user.api_token} sessionId={activeSessionId} />
            <section className="game-page-grid">
                <section className="game-main-stack">
                    <section className="card stack game-stage-card">
                        <div className="frame-header">
                            <h2>Jugar</h2>
                            <span className="pill success">activo</span>
                        </div>
                        <div className="iframe-shell game-stage-shell">
                            <iframe className="game-frame" src={game.play_url} title={game.title} />
                        </div>
                    </section>

                    <section className="card stack game-info-card">
                        <div className="game-info-grid">
                            <article className="stack">
                                <span className="eyebrow">{canPreview && game.status !== 'published' ? 'Vista' : 'Juego'}</span>
                                <h1 className="game-title">{game.title}</h1>
                                <p className="lead compact">{game.description}</p>
                                <div className="actions">
                                    <div className={`pill ${game.status === 'published' ? 'success' : 'warning'}`}>
                                        {game.status_label}
                                    </div>
                                    <div className="pill">inicio 5s</div>
                                </div>
                            </article>

                            <article className="stack controls-block">
                                <div className="stack compact-gap">
                                    <h2>Controles</h2>
                                    <p className="muted">{game.instructions}</p>
                                </div>

                                <div className="detail-strip">
                                    <div className="detail-chip">
                                        <strong>Modo</strong>
                                        <span>directo</span>
                                    </div>
                                    <div className="detail-chip alt">
                                        <strong>Estado</strong>
                                        <span>listo</span>
                                    </div>
                                </div>

                                <div className="actions">
                                    <Link className="button ghost" href="/catalogo">
                                        Volver
                                    </Link>
                                    <a className="button" href={game.play_url} target="_blank" rel="noreferrer">
                                        Abrir en pestaña
                                    </a>
                                </div>
                            </article>
                        </div>
                    </section>
                </section>

                <GameChatPanel chat={chat} gameTitle={game.title} />
            </section>
        </AppLayout>
    );
}
