<?php

namespace App\Http\Controllers;

use App\Models\IntegrationEvent;
use App\Services\GitHubEventDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GitHubWebhookController extends Controller
{
    public function handle(Request $request, GitHubEventDispatcher $gitHubEventDispatcher): JsonResponse
    {
        if (! $this->hasValidSignature($request)) {
            return response()->json([
                'message' => 'Firma de GitHub no válida.',
            ], 401);
        }

        $payload = json_decode($request->getContent(), true) ?? [];
        $event = (string) $request->header('x-github-event', 'unknown');

        if ($event === 'ping') {
            IntegrationEvent::query()->create([
                'source' => 'github',
                'event_name' => 'ping',
                'status' => 'processed',
                'summary' => 'GitHub ha validado correctamente el webhook.',
                'payload' => $payload,
                'processed_at' => now(),
            ]);

            return response()->json([
                'status' => 'Webhook GitHub operativo.',
            ]);
        }

        if (! $gitHubEventDispatcher->supports($event, $payload)) {
            IntegrationEvent::query()->create([
                'source' => 'github',
                'event_name' => $event,
                'status' => 'ignored',
                'summary' => 'Evento de GitHub ignorado por no estar soportado en RabbitMQ.',
                'payload' => $payload,
                'processed_at' => now(),
            ]);

            return response()->json([
                'status' => 'Evento ignorado.',
            ]);
        }

        $integrationEvent = $gitHubEventDispatcher->dispatch($event, $payload);

        return response()->json([
            'status' => 'Evento de GitHub enviado a la cola.',
            'integration_event_id' => $integrationEvent->id,
        ], 202);
    }

    private function hasValidSignature(Request $request): bool
    {
        $secret = config('services.github.webhook_secret');

        if (! filled($secret)) {
            return true;
        }

        $signature = (string) $request->header('x-hub-signature-256', '');

        if ($signature === '') {
            return false;
        }

        $expectedSignature = 'sha256='.hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expectedSignature, $signature);
    }
}
