import { Link, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AuthLayout from '../../Layouts/AuthLayout';
import FaceCaptureField from '../../Components/FaceCaptureField';

export default function Login() {
    const { flash } = usePage().props;
    const [faceSubmitting, setFaceSubmitting] = useState(false);
    const form = useForm({
        email: '',
        password: '',
        remember: false,
        capture_image: '',
    });

    const submit = (event) => {
        event.preventDefault();
        form.post('/login');
    };

    const submitFaceLogin = async (captureImage = form.data.capture_image) => {
        form.clearErrors();

        if (!form.data.email) {
            form.setError('email', 'Escribe tu email antes de usar Face ID.');
            return;
        }

        if (!captureImage) {
            form.setError('capture_image', 'Haz una foto o sube una imagen para usar Face ID.');
            return;
        }

        setFaceSubmitting(true);

        try {
            const response = await window.axios.post('/login/face-id', {
                email: form.data.email,
                capture_image: captureImage,
                remember: form.data.remember,
            });

            if (response.data.redirect) {
                window.location.href = response.data.redirect;
                return;
            }

        } catch (error) {
            const errors = error.response?.data?.errors;
            const message = error.response?.data?.message;

            if (errors) {
                Object.entries(errors).forEach(([field, value]) => {
                    form.setError(field, Array.isArray(value) ? value[0] : value);
                });

                return;
            }

            form.setError('capture_image', message ?? 'No se ha podido completar el acceso facial.');
        } finally {
            setFaceSubmitting(false);
        }
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

                <div className="muted auth-links-row">
                    <Link href="/recuperar-password">He olvidado la contraseña</Link>
                </div>
            </form>

            <div className="divider-row">
                <span />
                <strong>o entra con Face ID</strong>
                <span />
            </div>

            <div className="card inset-card stack">
                <div className="field">
                    <label htmlFor="face-email">Email para Face ID</label>
                    <input
                        id="face-email"
                        className="input"
                        type="email"
                        placeholder="tuemail@ejemplo.com"
                        value={form.data.email}
                        onChange={(event) => form.setData('email', event.target.value)}
                    />
                    {form.errors.email && <div className="error">{form.errors.email}</div>}
                </div>

                <FaceCaptureField
                    title="Acceso facial"
                    description="Usa Face ID solo si ya lo has configurado dentro de tu cuenta. Aquí solo se comprueba la foto para entrar."
                    value={form.data.capture_image}
                    onChange={(value) => form.setData('capture_image', value)}
                    onCaptureComplete={submitFaceLogin}
                    disabled={faceSubmitting}
                />

                {form.errors.capture_image && <div className="error">{form.errors.capture_image}</div>}

                <div className="actions">
                    <button
                        className="button success"
                        type="button"
                        onClick={submitFaceLogin}
                        disabled={faceSubmitting || !form.data.email || !form.data.capture_image}
                    >
                        {faceSubmitting ? 'Comprobando...' : 'Entrar con esta foto'}
                    </button>
                </div>

                <p className="muted small-copy">
                    Si todavía no tienes Face ID, entra con tu contraseña y configúralo en la zona de seguridad.
                </p>
            </div>

            <div className="demo-strip">
                <span className="demo-tag">demo</span>
                <code>admin@laraveljuegos.test</code>
                <span className="muted">/</span>
                <code>password</code>
            </div>
        </AuthLayout>
    );
}
