import { Link, useForm } from '@inertiajs/react';
import AuthLayout from '../../Layouts/AuthLayout';

export default function Login() {
    const form = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (event) => {
        event.preventDefault();
        form.post('/login');
    };

    return (
        <AuthLayout
            title="Login"
            eyebrow="Acceso"
            headline="Entra y sigue"
            lead="Una entrada limpia, rápida y directa."
        >
            <span className="eyebrow">Inicio de sesión</span>
            <h2>Bienvenido de nuevo</h2>
            <p className="muted">Accede con tu cuenta.</p>

            <form className="form-grid" onSubmit={submit}>
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

                <label className="pill" style={{ width: 'fit-content' }}>
                    <input
                        type="checkbox"
                        checked={form.data.remember}
                        onChange={(event) => form.setData('remember', event.target.checked)}
                    />
                    Mantener sesión
                </label>

                <div className="actions">
                    <button className="button" type="submit" disabled={form.processing}>
                        Entrar
                    </button>
                    <Link className="button ghost" href="/register">
                        Crear cuenta
                    </Link>
                </div>
            </form>

            <div className="demo-strip">
                <span className="demo-tag">demo</span>
                <code>admin@laraveljuegos.test</code>
                <span className="muted">/</span>
                <code>password</code>
            </div>
        </AuthLayout>
    );
}
