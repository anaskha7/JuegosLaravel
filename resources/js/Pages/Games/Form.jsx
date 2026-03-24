import { Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

export default function GameForm({ game, statuses }) {
    const editing = Boolean(game);

    const form = useForm({
        title: game?.title ?? '',
        description: game?.description ?? '',
        instructions: game?.instructions ?? '',
        status: game?.status ?? 'draft',
        game_url: game?.game_url ?? '',
    });

    const submit = (event) => {
        event.preventDefault();

        if (editing) {
            form.put(`/gestion/juegos/${game.id}`);
            return;
        }

        form.post('/gestion/juegos');
    };

    return (
        <AppLayout title={editing ? 'Editar juego' : 'Crear juego'}>
            <section className="hero stack">
                <div className="hero-grid">
                    <div className="stack">
                        <span className="eyebrow">{editing ? 'Edición' : 'Alta'}</span>
                        <h1>{editing ? 'Editar juego' : 'Crear juego'}</h1>
                        <p className="lead">Completa los datos principales.</p>
                    </div>
                    <div className="hero-spotlight stack">
                        <div className="panel panel-highlight stack">
                            <div className="kicker">Modo</div>
                            <div className="feature-score">{editing ? 'Edit' : 'New'}</div>
                            <div className="muted">formulario</div>
                        </div>
                    </div>
                </div>
            </section>

            <section className="split-grid">
                <article className="card">
                    <form className="form-grid" onSubmit={submit}>
                        <div className="field">
                            <label htmlFor="title">Título</label>
                            <input
                                id="title"
                                className="input"
                                value={form.data.title}
                                onChange={(event) => form.setData('title', event.target.value)}
                            />
                            {form.errors.title && <div className="error">{form.errors.title}</div>}
                        </div>

                        <div className="field">
                            <label htmlFor="description">Descripción</label>
                            <textarea
                                id="description"
                                className="textarea"
                                value={form.data.description}
                                onChange={(event) => form.setData('description', event.target.value)}
                            />
                            {form.errors.description && <div className="error">{form.errors.description}</div>}
                        </div>

                        <div className="field">
                            <label htmlFor="instructions">Instrucciones</label>
                            <textarea
                                id="instructions"
                                className="textarea"
                                value={form.data.instructions}
                                onChange={(event) => form.setData('instructions', event.target.value)}
                            />
                            {form.errors.instructions && <div className="error">{form.errors.instructions}</div>}
                        </div>

                        <div className="field">
                            <label htmlFor="status">Estado</label>
                            <select
                                id="status"
                                className="select"
                                value={form.data.status}
                                onChange={(event) => form.setData('status', event.target.value)}
                            >
                                {statuses.map((status) => (
                                    <option key={status.value} value={status.value}>
                                        {status.label}
                                    </option>
                                ))}
                            </select>
                            {form.errors.status && <div className="error">{form.errors.status}</div>}
                        </div>

                        <div className="field">
                            <label htmlFor="game_url">Enlace del juego</label>
                            <input
                                id="game_url"
                                className="input"
                                value={form.data.game_url}
                                onChange={(event) => form.setData('game_url', event.target.value)}
                            />
                            {form.errors.game_url && <div className="error">{form.errors.game_url}</div>}
                        </div>

                        <div className="actions">
                            <button className="button success" type="submit" disabled={form.processing}>
                                {editing ? 'Actualizar juego' : 'Guardar juego'}
                            </button>
                            <Link className="button ghost" href="/gestion/juegos">
                                Cancelar
                            </Link>
                        </div>
                    </form>
                </article>

                <aside className="card stack">
                    <h2>Vista rápida</h2>
                    <p className="muted">Revisa los datos antes de guardar.</p>
                    <div className="detail-strip">
                        <div className="detail-chip">
                            <strong>Enlace</strong>
                            <span>configurado</span>
                        </div>
                        <div className="detail-chip alt">
                            <strong>Estado</strong>
                            <span>{editing ? game.status_label ?? game.status : 'draft'}</span>
                        </div>
                    </div>
                    {editing && (
                        <>
                            <strong>Vista actual</strong>
                            <div className="code-box">{game.playable_url}</div>
                        </>
                    )}
                </aside>
            </section>
        </AppLayout>
    );
}
