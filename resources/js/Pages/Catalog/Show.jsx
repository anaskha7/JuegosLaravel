import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

export default function CatalogShow({ game, canPreview }) {
    return (
        <AppLayout title={game.title}>
            <Head title={game.title} />
            <section className="show-grid">
                <aside className="stack">
                    <article className="card stack">
                        <span className="eyebrow">{canPreview && game.status !== 'published' ? 'Vista' : 'Juego'}</span>
                        <h1>{game.title}</h1>
                        <p className="lead compact">{game.description}</p>
                        <div className="actions">
                            <div className={`pill ${game.status === 'published' ? 'success' : 'warning'}`}>{game.status_label}</div>
                            <div className="pill">inicio 5s</div>
                        </div>
                    </article>

                    <article className="card stack">
                        <h2>Controles</h2>
                        <p className="muted">{game.instructions}</p>
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
                </aside>

                <section className="card stack">
                    <div className="frame-header">
                        <h2>Jugar</h2>
                        <span className="pill success">activo</span>
                    </div>
                    <div className="iframe-shell">
                        <iframe className="game-frame" src={game.play_url} title={game.title} />
                    </div>
                </section>
            </section>
        </AppLayout>
    );
}
