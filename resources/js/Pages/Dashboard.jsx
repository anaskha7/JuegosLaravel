import AppLayout from '../Layouts/AppLayout';

export default function Dashboard({ stats, latestGames, latestSessions }) {
    return (
        <AppLayout title="Dashboard">
            <section className="hero stack">
                <div className="hero-grid">
                    <div className="stack">
                        <span className="eyebrow">Panel</span>
                        <h1>Vista general</h1>
                        <p className="lead">Resumen rápido de la actividad.</p>
                        <div className="hero-tags">
                            <span className="pill success">hoy</span>
                            <span className="pill">juegos</span>
                            <span className="pill">actividad</span>
                        </div>
                    </div>
                    <div className="hero-spotlight stack">
                        <div className="panel panel-highlight stack">
                            <div className="kicker">Hoy</div>
                            <div className="feature-score">{stats.active_sessions}</div>
                            <div className="muted">sesiones activas</div>
                        </div>
                        <div className="spotlight-row">
                            <div className="spotlight-chip">
                                <strong>{stats.published_games}</strong>
                                <span>publicados</span>
                            </div>
                            <div className="spotlight-chip alt">
                                <strong>{stats.sessions_played}</strong>
                                <span>histórico</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section className="metric-grid">
                <article className="metric-card tone-a">
                    <div className="metric-value">{stats.published_games}</div>
                    <div className="muted">Juegos publicados</div>
                </article>
                <article className="metric-card tone-b">
                    <div className="metric-value">{stats.managed_games}</div>
                    <div className="muted">Juegos en lista</div>
                </article>
                <article className="metric-card tone-c">
                    <div className="metric-value">{stats.sessions_played}</div>
                    <div className="muted">Partidas jugadas</div>
                </article>
                <article className="metric-card tone-d">
                    <div className="metric-value">{stats.active_sessions}</div>
                    <div className="muted">Partidas activas</div>
                </article>
            </section>

            <section className="split-grid">
                <article className="card stack">
                    <span className="eyebrow">Catálogo</span>
                    <h2>Últimos juegos</h2>
                    <div className="list">
                        {latestGames.map((game) => (
                            <div className="list-item list-item-rich" key={game.id}>
                                <div className="mini-accent" />
                                <div>
                                    <strong>{game.title}</strong>
                                    <div className="muted">
                                        {game.status_label} · {game.creator_name}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </article>

                <article className="card stack">
                    <span className="eyebrow">Actividad</span>
                    <h2>Últimas partidas</h2>
                    <div className="list">
                        {latestSessions.map((session) => (
                            <div className="list-item list-item-rich" key={session.id}>
                                <div className="mini-accent alt" />
                                <div>
                                    <strong>{session.game_title}</strong>
                                    <div className="muted">
                                        {session.status} · {session.started_at}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </article>
            </section>
        </AppLayout>
    );
}
