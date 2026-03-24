import { Link, router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Pagination from '../../Components/Pagination';

export default function GamesIndex({ games }) {
    const destroyGame = (gameId) => {
        if (!window.confirm('Esta acción eliminará el juego.')) {
            return;
        }

        router.delete(`/gestion/juegos/${gameId}`);
    };

    const toggleStatus = (gameId) => {
        router.patch(`/gestion/juegos/${gameId}/status`);
    };

    return (
        <AppLayout title="Gestión de Juegos">
            <section className="hero stack">
                <div className="hero-grid">
                    <div className="stack">
                        <span className="eyebrow">Gestión</span>
                        <h1>Juegos</h1>
                        <p className="lead">Crea, edita y organiza.</p>
                        <div className="hero-tags">
                            <span className="pill success">nuevo</span>
                            <span className="pill">editar</span>
                            <span className="pill">estado</span>
                        </div>
                    </div>
                    <div className="hero-spotlight stack">
                        <div className="panel panel-highlight stack">
                            <div className="kicker">Total</div>
                            <div className="feature-score">{games.data.length}</div>
                            <div className="muted">juegos en esta vista</div>
                            <div className="actions">
                                <Link className="button" href="/gestion/juegos/create">
                                    Nuevo juego
                                </Link>
                            </div>
                        </div>
                        <div className="spotlight-row">
                            <div className="spotlight-chip">
                                <strong>Vista</strong>
                                <span>general</span>
                            </div>
                            <div className="spotlight-chip alt">
                                <strong>Acceso</strong>
                                <span>privado</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section className="table-shell">
                <table className="table">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Estado</th>
                            <th>Creador</th>
                            <th>Enlace</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {games.data.map((game) => (
                            <tr key={game.id} className="table-row">
                                <td>{game.title}</td>
                                <td>
                                    <span className={`pill ${game.status === 'published' ? 'success' : 'warning'}`}>
                                        {game.status_label}
                                    </span>
                                </td>
                                <td>{game.creator_name}</td>
                                <td className="muted">{game.game_url}</td>
                                <td>
                                    <div className="actions">
                                        <Link className="button secondary" href={`/gestion/juegos/${game.id}/edit`}>
                                            Editar
                                        </Link>
                                        <button className="button ghost" onClick={() => toggleStatus(game.id)}>
                                            {game.status === 'published' ? 'Despublicar' : 'Publicar'}
                                        </button>
                                        <Link className="button ghost" href={`/catalogo/${game.id}`}>
                                            Ver
                                        </Link>
                                        <button className="button danger" onClick={() => destroyGame(game.id)}>
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </section>

            <Pagination links={games.links} />
        </AppLayout>
    );
}
