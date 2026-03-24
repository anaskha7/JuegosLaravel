import { Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Pagination from '../../Components/Pagination';

export default function CatalogIndex({ games }) {
    return (
        <AppLayout title="Catálogo">
            <section className="hero stack">
                <div className="catalog-hero">
                    <div className="stack">
                        <span className="eyebrow">Arcade</span>
                        <h1>Elige partida y entra</h1>
                        <p className="lead">Elige un juego y entra.</p>
                        <div className="hero-tags">
                            <span className="pill success">listo</span>
                            <span className="pill">activo</span>
                            <span className="pill">ahora</span>
                        </div>
                    </div>
                    <div className="hero-spotlight stack">
                        <div className="panel panel-highlight stack">
                            <div className="kicker">Publicado</div>
                            <div className="feature-score">{games.data.length}</div>
                            <div className="muted">juegos visibles</div>
                        </div>
                        <div className="spotlight-row">
                            <div className="spotlight-chip">
                                <strong>Top</strong>
                                <span>selección</span>
                            </div>
                            <div className="spotlight-chip alt">
                                <strong>5s</strong>
                                <span>inicio</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section className="cards-grid">
                {games.data.map((game, index) => (
                    <article className="card stack game-card" key={game.id}>
                        <div className="game-card-media">
                            <span className="game-card-index">{String(index + 1).padStart(2, '0')}</span>
                            <div className="game-card-glow" />
                        </div>
                        <div className="game-card-head">
                            <div className="stack" style={{ gap: 8 }}>
                                <span className="pill success">{game.status_label}</span>
                                <h2>{game.title}</h2>
                            </div>
                            <div className="game-icon" />
                        </div>
                        <p className="lead compact">{game.description}</p>
                        <div className="card-meta">
                            <span className="muted">por {game.creator_name}</span>
                            <span className="card-dot" />
                            <span className="muted">jugar ahora</span>
                        </div>
                        <div className="actions">
                            <Link className="button" href={`/catalogo/${game.id}`}>
                                Entrar
                            </Link>
                            <Link className="button ghost" href={`/catalogo/${game.id}`}>
                                Ver ficha
                            </Link>
                        </div>
                    </article>
                ))}
            </section>

            <Pagination links={games.links} />
        </AppLayout>
    );
}
