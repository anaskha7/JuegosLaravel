import { router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

export default function GitHubSimulator({ scenarios, latestGitHubEvents }) {
    const simulate = (scenario) => {
        router.post('/integraciones/github/simulador', { scenario });
    };

    return (
        <AppLayout title="Simulador GitHub">
            <section className="hero stack">
                <div className="hero-grid">
                    <div className="stack">
                        <span className="eyebrow">Integraciones</span>
                        <h1>Simulador GitHub {'->'} RabbitMQ</h1>
                        <p className="lead">Lanza eventos de prueba para ver el flujo en la cola y en el panel.</p>
                        <div className="hero-tags">
                            <span className="pill success">RabbitMQ</span>
                            <span className="pill">GitHub</span>
                            <span className="pill">simulación</span>
                        </div>
                    </div>
                    <div className="hero-spotlight stack">
                        <div className="panel panel-highlight stack">
                            <div className="kicker">Escenarios</div>
                            <div className="feature-score">{scenarios.length}</div>
                            <div className="muted">eventos disponibles</div>
                            <div className="actions">
                                <button className="button" type="button" onClick={() => simulate('all')}>
                                    Simular todos
                                </button>
                            </div>
                        </div>
                        <div className="spotlight-row">
                            <div className="spotlight-chip">
                                <strong>Cola</strong>
                                <span>github-events</span>
                            </div>
                            <div className="spotlight-chip alt">
                                <strong>Objetivo</strong>
                                <span>ver tráfico real</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section className="split-grid">
                {scenarios.map((scenario) => (
                    <article className="card stack" key={scenario.key}>
                        <span className="eyebrow">{scenario.event_name}</span>
                        <h2>{scenario.title}</h2>
                        <p className="muted">{scenario.description}</p>
                        <div className="actions">
                            <button className="button" type="button" onClick={() => simulate(scenario.key)}>
                                Enviar a RabbitMQ
                            </button>
                        </div>
                    </article>
                ))}
            </section>

            <section className="card stack">
                <span className="eyebrow">Últimos eventos</span>
                <h2>GitHub registrado en integración</h2>
                <div className="list">
                    {latestGitHubEvents.length === 0 && (
                        <div className="list-item">
                            <div className="muted">Todavía no hay eventos de GitHub registrados.</div>
                        </div>
                    )}

                    {latestGitHubEvents.map((event) => (
                        <div className="list-item list-item-rich" key={event.id}>
                            <div className="mini-accent" />
                            <div>
                                <strong>{event.event_name}</strong>
                                <div className="muted">
                                    {event.status} · {event.external_reference || 'sin referencia'} · {event.created_at}
                                </div>
                                <div className="muted">{event.summary}</div>
                            </div>
                        </div>
                    ))}
                </div>
            </section>
        </AppLayout>
    );
}
