import { useEffect, useId, useRef, useState } from 'react';

export default function FaceCaptureField({
    value,
    onChange,
    onCaptureComplete,
    title = 'Captura facial',
    description = 'Abre la cámara, encuadra la cara y guarda una foto frontal con buena luz.',
    disabled = false,
}) {
    const inputId = useId();
    const videoRef = useRef(null);
    const fileInputRef = useRef(null);
    const streamRef = useRef(null);
    const [cameraOpen, setCameraOpen] = useState(false);
    const [error, setError] = useState('');

    useEffect(() => {
        return () => {
            stopCamera();
        };
    }, []);

    useEffect(() => {
        if (!cameraOpen || !videoRef.current || !streamRef.current) {
            return;
        }

        videoRef.current.srcObject = streamRef.current;
        void videoRef.current.play();
    }, [cameraOpen]);

    async function openCamera() {
        if (!navigator.mediaDevices?.getUserMedia) {
            setError('Este navegador no permite acceder a la webcam.');
            return;
        }

        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                audio: false,
                video: {
                    facingMode: 'user',
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                },
            });

            streamRef.current = stream;
            setCameraOpen(true);
            setError('');
        } catch (_) {
            setError('No se ha podido abrir la cámara. Revisa los permisos del navegador.');
        }
    }

    function stopCamera() {
        streamRef.current?.getTracks().forEach((track) => track.stop());
        streamRef.current = null;
        setCameraOpen(false);

        if (videoRef.current) {
            videoRef.current.srcObject = null;
        }
    }

    function captureFrame() {
        const video = videoRef.current;

        if (!video || !video.videoWidth || !video.videoHeight) {
            setError('Todavía no hay imagen de cámara disponible.');
            return;
        }

        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        const context = canvas.getContext('2d');
        context?.drawImage(video, 0, 0, canvas.width, canvas.height);

        const image = canvas.toDataURL('image/jpeg', 0.92);

        onChange(image);
        onCaptureComplete?.(image);
        setError('');
        stopCamera();
    }

    function clearCapture() {
        onChange('');
        setError('');
    }

    function handleFileSelection(event) {
        const file = event.target.files?.[0];

        if (!file) {
            return;
        }

        const reader = new FileReader();
        reader.onload = () => {
            if (typeof reader.result === 'string') {
                onChange(reader.result);
                onCaptureComplete?.(reader.result);
                setError('');
                stopCamera();
            }
        };
        reader.readAsDataURL(file);
    }

    return (
        <section className="face-capture stack">
            <div className="frame-header">
                <div className="stack compact-gap">
                    <h3>{title}</h3>
                    <p className="muted small-copy">{description}</p>
                </div>
                {value && <span className="pill success">lista</span>}
            </div>

            <div className="face-preview">
                {cameraOpen ? (
                    <video ref={videoRef} className="face-media" autoPlay muted playsInline />
                ) : value ? (
                    <img className="face-media" src={value} alt="Captura facial" />
                ) : (
                    <div className="face-placeholder">
                        <strong>Sin imagen</strong>
                        <span>Haz una captura frontal o sube una foto nítida.</span>
                    </div>
                )}
            </div>

            {error && <div className="error">{error}</div>}

            <div className="actions">
                {!cameraOpen ? (
                    <button className="button" type="button" onClick={openCamera} disabled={disabled}>
                        Abrir cámara
                    </button>
                ) : (
                    <button className="button success" type="button" onClick={captureFrame} disabled={disabled}>
                        Capturar
                    </button>
                )}

                {cameraOpen && (
                    <button className="button ghost" type="button" onClick={stopCamera} disabled={disabled}>
                        Cerrar cámara
                    </button>
                )}

                <button
                    className="button ghost"
                    type="button"
                    onClick={() => fileInputRef.current?.click()}
                    disabled={disabled}
                >
                    Subir foto
                </button>

                {value && (
                    <button className="button secondary" type="button" onClick={clearCapture} disabled={disabled}>
                        Quitar
                    </button>
                )}
            </div>

            <input
                id={inputId}
                ref={fileInputRef}
                type="file"
                accept="image/png,image/jpeg,image/jpg"
                className="sr-only"
                disabled={disabled}
                onChange={handleFileSelection}
            />
        </section>
    );
}
