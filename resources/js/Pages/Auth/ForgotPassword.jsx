import { Link, useForm, usePage } from '@inertiajs/react';
import AuthLayout from '../../Layouts/AuthLayout';

export default function ForgotPassword() {
    const { flash } = usePage().props;
    const form = useForm({
        email: '',
    });

    const submit = (event) => {
        event.preventDefault();
        form.post('/recuperar-password');
    };

    return (
        <AuthLayout
            title="Recuperar contraseña"
            eyebrow="Acceso"
            headline="Recupera tu contraseña"
            lead="Primero pedimos un código para confirmar que eres tú."
        >
            <span className="eyebrow">Paso 1</span>
            <h2>Pedir código</h2>
            <p className="muted">Escribe tu email y te mandamos un código para continuar.</p>

            {flash?.status && <div className="flash">{flash.status}</div>}

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

                <div className="actions">
                    <button className="button" type="submit" disabled={form.processing}>
                        Enviar código
                    </button>
                    <Link className="button ghost" href="/login">
                        Volver al login
                    </Link>
                </div>
            </form>
        </AuthLayout>
    );
}
