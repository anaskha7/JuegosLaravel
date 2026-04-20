<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FacialRecognitionService
{
    /**
     * @return array{verified: bool, distance: float|null, threshold: float|null, model: string|null}
     */
    public function verify(string $referencePath, string $capturePath): array
    {
        $endpoint = (string) config('services.facial.url');

        if ($endpoint === '') {
            throw new RuntimeException('El servicio facial no esta configurado.');
        }

        $reference = file_get_contents($referencePath);
        $capture = file_get_contents($capturePath);

        if ($reference === false || $capture === false) {
            throw new RuntimeException('No se han podido leer las imagenes para la verificacion facial.');
        }

        try {
            $response = Http::timeout((int) config('services.facial.timeout', 60))
                ->attach('img1', $reference, basename($referencePath))
                ->attach('img2', $capture, basename($capturePath))
                ->post($endpoint);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('No se puede conectar con el microservicio facial.');
        }

        if ($response->failed()) {
            $message = $response->json('detail')
                ?? $response->json('message')
                ?? 'La comprobacion facial ha fallado.';

            throw new RuntimeException($message);
        }

        return [
            'verified' => (bool) $response->json('verified', false),
            'distance' => $response->json('distance'),
            'threshold' => $response->json('threshold'),
            'model' => $response->json('model'),
        ];
    }
}
