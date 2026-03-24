import { Head, Link } from '@inertiajs/react';

export default function AuthLayout({ title, eyebrow, headline, lead, children, footer }) {
    return (
        <>
            <Head title={title} />
            <div className="auth-shell">
                <div className="auth-grid">
                    <aside className="auth-showcase">
                        <div className="stack">
                            <span className="eyebrow">{eyebrow}</span>
                            <h1>{headline}</h1>
                            <p className="lead">{lead}</p>
                            <div className="auth-ribbon">
                                <span>Juegos</span>
                                <span>Partidas</span>
                                <span>Ranking</span>
                                <span>Acceso</span>
                            </div>
                        </div>
                        <div className="showcase-metrics">
                            <div className="showcase-note feature">
                                <span>03</span>
                                <strong>zonas</strong>
                            </div>
                            <div className="showcase-note">
                                <span>04</span>
                                <strong>vistas</strong>
                            </div>
                            <div className="showcase-note">
                                <span>24/7</span>
                                <strong>online</strong>
                            </div>
                        </div>
                        <div className="auth-orb auth-orb-a" />
                        <div className="auth-orb auth-orb-b" />
                    </aside>

                    <section className="auth-card stack">
                        {children}
                        {footer && <div className="muted">{footer}</div>}
                        <div className="muted">
                            <Link href="/login">Login</Link> · <Link href="/register">Registro</Link>
                        </div>
                    </section>
                </div>
            </div>
        </>
    );
}
