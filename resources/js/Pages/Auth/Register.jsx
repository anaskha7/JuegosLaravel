import { Link, useForm } from '@inertiajs/react';
import AuthLayout from '../../Layouts/AuthLayout';

export default function Register() {
    const form = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (event) => {
        event.preventDefault();
        form.post('/register');
    };

    return (
        <AuthLayout
            title="Registro"
            eyebrow="Registro"
            headline="Crea tu cuenta"
            lead="Empieza en pocos pasos."
        >
            <span className="eyebrow">Alta de usuario</span>
            <h2>Nueva cuenta</h2>
            <p className="muted">Completa tus datos.</p>

            <form className="form-grid" onSubmit={submit}>
                <div className="field">
                    <label htmlFor="name">Nombre</label>
                    <input
                        id="name"
                        className="input"
                        value={form.data.name}
                        onChange={(event) => form.setData('name', event.target.value)}
                    />
                    {form.errors.name && <div className="error">{form.errors.name}</div>}
                </div>

                <div className="field">
                    <label htmlFor="email">Email</label>
                    <input
                        id="email"
                        className="input"
                        type="email"
                        value={form.data.email}
                        onChange={(event) => form.setData('email', event.target.value)}
                    />
                    {form.errors.email && <div className="error">{form.errors.email}</div>}
                </div>

                <div className="field">
                    <label htmlFor="password">Contraseña</label>
                    <input
                        id="password"
                        className="input"
                        type="password"
                        value={form.data.password}
                        onChange={(event) => form.setData('password', event.target.value)}
                    />
                    {form.errors.password && <div className="error">{form.errors.password}</div>}
                </div>

                <div className="field">
                    <label htmlFor="password_confirmation">Confirmar contraseña</label>
                    <input
                        id="password_confirmation"
                        className="input"
                        type="password"
                        value={form.data.password_confirmation}
                        onChange={(event) => form.setData('password_confirmation', event.target.value)}
                    />
                </div>

                <div className="actions">
                    <button className="button" type="submit" disabled={form.processing}>
                        Crear cuenta
                    </button>
                    <Link className="button ghost" href="/login">
                        Ya tengo acceso
                    </Link>
                </div>
            </form>

            <div className="demo-strip">
                <span className="demo-tag">player</span>
                <span className="muted">acceso inmediato</span>
            </div>
        </AuthLayout>
    );
}
