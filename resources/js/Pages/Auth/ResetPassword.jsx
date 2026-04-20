import { Link, useForm, usePage } from '@inertiajs/react';
import AuthLayout from '../../Layouts/AuthLayout';

export default function ResetPassword({ email }) {
    const { flash } = usePage().props;
    const form = useForm({
        password: '',
        password_confirmation: '',
    });

    const submit = (event) => {
        event.preventDefault();
        form.post('/recuperar-password/nueva');
    };

    return (
        <AuthLayout
            title="Nueva contraseña"
            eyebrow="Acceso"
            headline="Pon una contraseña nueva"
            lead="Este paso solo aparece después de validar bien el código."
        >
            <span className="eyebrow">Paso 3</span>
            <h2>Cambiar contraseña</h2>
            <p className="muted">Cuenta: {email}</p>

            {flash?.status && <div className="flash">{flash.status}</div>}

            <form className="form-grid" onSubmit={submit}>
                <div className="field">
                    <label htmlFor="password">Nueva contraseña</label>
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
                    <label htmlFor="password_confirmation">Repetir contraseña</label>
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
                        Guardar contraseña
                    </button>
                    <Link className="button ghost" href="/login">
                        Cancelar
                    </Link>
                </div>
            </form>
        </AuthLayout>
    );
}
