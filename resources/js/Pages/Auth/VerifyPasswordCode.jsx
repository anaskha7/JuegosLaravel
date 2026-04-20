import { Link, useForm, usePage } from '@inertiajs/react';
import AuthLayout from '../../Layouts/AuthLayout';

export default function VerifyPasswordCode({ email }) {
    const { flash } = usePage().props;
    const form = useForm({
        email: email ?? '',
        code: '',
    });

    const submit = (event) => {
        event.preventDefault();
        form.post('/recuperar-password/codigo');
    };

    return (
        <AuthLayout
            title="Verificar código"
            eyebrow="Acceso"
            headline="Verifica el código"
            lead="Si el código es correcto, te dejamos poner una contraseña nueva."
        >
            <span className="eyebrow">Paso 2</span>
            <h2>Confirmar código</h2>
            <p className="muted">Introduce el email y el código de 6 cifras.</p>

            {flash?.status && <div className="flash">{flash.status}</div>}
            {flash?.reset_code_hint && <div className="flash">{flash.reset_code_hint}</div>}

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
                    <label htmlFor="code">Código</label>
                    <input
                        id="code"
                        className="input"
                        inputMode="numeric"
                        maxLength={6}
                        value={form.data.code}
                        onChange={(event) => form.setData('code', event.target.value.replace(/\D/g, '').slice(0, 6))}
                    />
                    {form.errors.code && <div className="error">{form.errors.code}</div>}
                </div>

                <div className="actions">
                    <button className="button" type="submit" disabled={form.processing}>
                        Validar código
                    </button>
                    <Link className="button ghost" href="/recuperar-password">
                        Pedir otro
                    </Link>
                </div>
            </form>
        </AuthLayout>
    );
}
