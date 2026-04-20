import { Head, Link, usePage } from '@inertiajs/react';

export default function AppLayout({ title, children }) {
    const { auth, flash, appName } = usePage().props;
    const user = auth?.user;
    const privileged = ['admin', 'manager'].includes(user?.role_name);

    return (
        <>
            <Head title={title} />
            <div className="app-shell stack">
                <nav className="nav">
                    <div className="brand">
                        <div className="brand-mark">LJ</div>
                        <div className="brand-copy">
                            <small>Arcade Suite</small>
                            <strong>{appName}</strong>
                        </div>
                    </div>

                    <div className="nav-links nav-cluster">
                        {privileged && (
                            <Link className="nav-link" href="/dashboard">
                                Dashboard
                            </Link>
                        )}
                        <Link className="nav-link" href="/catalogo">
                            Catálogo
                        </Link>
                        <Link className="nav-link" href="/seguridad/face-id">
                            Seguridad
                        </Link>
                        {privileged && (
                            <Link className="nav-link" href="/gestion/juegos">
                                Gestión
                            </Link>
                        )}
                    </div>

                    <div className="nav-actions">
                        <div className="nav-user-wrap">
                            <span className="pill nav-user">
                                {user?.name} · {user?.role_label}
                            </span>
                        </div>
                        <Link className="button secondary" href="/logout" method="post" as="button">
                            Salir
                        </Link>
                    </div>
                </nav>

                {flash?.status && <div className="flash">{flash.status}</div>}

                {children}
            </div>
        </>
    );
}
