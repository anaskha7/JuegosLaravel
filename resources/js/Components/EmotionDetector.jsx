import React, { useEffect, useRef, useState } from 'react';
import * as faceapi from '@vladmandic/face-api';

export default function EmotionDetector({ apiToken, sessionId }) {
    const videoRef = useRef(null);
    const intervalRef = useRef(null);
    const [isModelsLoaded, setIsModelsLoaded] = useState(false);
    const [currentEmotionLabel, setCurrentEmotionLabel] = useState(null);

    useEffect(() => {
        const loadModels = async () => {
            try {
                const modelUrl = '/models';

                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(modelUrl),
                    faceapi.nets.faceExpressionNet.loadFromUri(modelUrl),
                ]);

                setIsModelsLoaded(true);
            } catch (error) {
                console.error('Error al cargar modelos de face-api:', error);
            }
        };

        loadModels();
    }, []);

    useEffect(() => {
        if (!isModelsLoaded || !sessionId) {
            setCurrentEmotionLabel(null);
            return undefined;
        }

        let stream = null;
        let isCancelled = false;

        const emotionMap = {
            neutral: { text: 'Estás neutral', emoji: '😐' },
            happy: { text: 'Estás feliz', emoji: '😄' },
            sad: { text: 'Estás triste', emoji: '😢' },
            angry: { text: 'Estás enfadado', emoji: '😠' },
            fearful: { text: 'Tienes miedo', emoji: '😨' },
            disgusted: { text: 'Estás disgustado', emoji: '🤢' },
            surprised: { text: 'Estás sorprendido', emoji: '😲' },
        };

        const startDetection = async () => {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: true });

                if (isCancelled || !videoRef.current) {
                    stream?.getTracks().forEach((track) => track.stop());
                    return;
                }

                videoRef.current.srcObject = stream;
                await videoRef.current.play();

                intervalRef.current = window.setInterval(async () => {
                    if (!videoRef.current) {
                        return;
                    }

                    const detections = await faceapi
                        .detectSingleFace(videoRef.current, new faceapi.TinyFaceDetectorOptions())
                        .withFaceExpressions();

                    if (!detections?.expressions) {
                        setCurrentEmotionLabel(null);
                        return;
                    }

                    const expressions = detections.expressions;
                    const dominantEmotion = Object.keys(expressions).reduce((left, right) => (
                        expressions[left] > expressions[right] ? left : right
                    ));
                    const confidence = expressions[dominantEmotion];

                    if (confidence <= 0.4) {
                        return;
                    }

                    setCurrentEmotionLabel(emotionMap[dominantEmotion] ?? null);

                    fetch(`/api/sessions/${sessionId}/emotions`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Authorization: `Bearer ${apiToken}`,
                        },
                        body: JSON.stringify({
                            emotion: dominantEmotion,
                            confidence,
                            context: { source: 'webcam_interval' },
                        }),
                    }).catch((error) => console.error('Error al enviar emoción:', error));
                }, 5000);
            } catch (error) {
                console.error('Error al acceder a la webcam para emociones:', error);
            }
        };

        startDetection();

        return () => {
            isCancelled = true;
            setCurrentEmotionLabel(null);

            if (intervalRef.current) {
                window.clearInterval(intervalRef.current);
                intervalRef.current = null;
            }

            if (stream) {
                stream.getTracks().forEach((track) => track.stop());
            }

            if (videoRef.current) {
                videoRef.current.srcObject = null;
            }
        };
    }, [apiToken, isModelsLoaded, sessionId]);

    return (
        <>
            <video
                ref={videoRef}
                autoPlay
                muted
                playsInline
                style={{ display: 'none' }}
            />
            {currentEmotionLabel && (
                <div
                    style={{
                        position: 'fixed',
                        bottom: '24px',
                        left: '24px',
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        backdropFilter: 'blur(8px)',
                        color: '#1f2937',
                        padding: '8px 16px',
                        borderRadius: '9999px',
                        display: 'flex',
                        alignItems: 'center',
                        gap: '8px',
                        boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
                        zIndex: 50,
                        fontWeight: 500,
                        fontSize: '14px',
                        transition: 'all 0.3s ease-in-out',
                    }}
                >
                    <span style={{ fontSize: '20px' }}>{currentEmotionLabel.emoji}</span>
                    <span>{currentEmotionLabel.text}</span>
                </div>
            )}
        </>
    );
}
