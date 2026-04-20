import { useForm, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import FaceCaptureField from '../../Components/FaceCaptureField';

export default function FaceSecurity({ security }) {
    const { auth } = usePage().props;
    const form = useForm({
        reference_image: '',
    });

    const saveReference = () => {
        form.post('/seguridad/face-id', {
            preserveScroll: true,
            onSuccess: () => form.reset('reference_image'),
        });
    };

    const removeReference = () => {
        form.delete('/seguridad/face-id', {
            preserveScroll: true,
        });
    };

    return (
        <AppLayout title="Seguridad">
            <section className="security-grid">
                <article className="card stack">
                    <span className="eyebrow">Seguridad</span>
                    <h1>Face ID</h1>
                    <p className="lead compact">
                        Guarda una foto base de tu cara para poder entrar con la webcam desde la pantalla de login.
                    </p>

                    <div className="detail-strip">
                        <div className="detail-chip">
                            <strong>Usuario</strong>
                            <span>{auth.user.name}</span>
                        </div>
                        <div className="detail-chip alt">
                            <strong>Estado</strong>
                            <span>{security.has_face_reference ? 'configurado' : 'pendiente'}</span>
                        </div>
                    </div>

                    <p className="muted">
                        Usa una foto frontal, con buena luz y sin otra persona en el encuadre. Si cambias de aspecto de forma notable,
                        actualiza la referencia.
                    </p>
                </article>

                <article className="card stack">
                    <FaceCaptureField
                        title="Actualizar foto base"
                        description="Puedes capturar una imagen nueva o subir una foto desde el dispositivo."
                        value={form.data.reference_image}
                        onChange={(value) => form.setData('reference_image', value)}
                    />

                    {form.errors.reference_image && <div className="error">{form.errors.reference_image}</div>}

                    <div className="actions">
                        <button
                            className="button success"
                            type="button"
                            onClick={saveReference}
                            disabled={form.processing || !form.data.reference_image}
                        >
                            Guardar Face ID
                        </button>

                        {security.has_face_reference && (
                            <button className="button ghost" type="button" onClick={removeReference} disabled={form.processing}>
                                Quitar Face ID
                            </button>
                        )}
                    </div>
                </article>
            </section>
        </AppLayout>
    );
}
