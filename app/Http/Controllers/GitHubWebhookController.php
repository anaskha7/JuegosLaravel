<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessGitHubEventJob;
use App\Models\IntegrationEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GitHubWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
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

        if ($event !== 'pull_request' || ! isset($payload['pull_request'])) {
            IntegrationEvent::query()->create([
                'source' => 'github',
                'event_name' => $event,
                'status' => 'ignored',
                'summary' => 'Evento de GitHub ignorado por no ser una pull request.',
                'payload' => $payload,
                'processed_at' => now(),
            ]);

            return response()->json([
                'status' => 'Evento ignorado.',
            ]);
        }

        $action = (string) ($payload['action'] ?? 'opened');
        $integrationEvent = IntegrationEvent::query()->create([
            'source' => 'github',
            'event_name' => 'pull_request.'.$action,
            'status' => 'queued',
            'queue_connection' => 'rabbitmq',
            'queue_name' => 'github-events',
            'external_reference' => $this->buildExternalReference($payload),
            'summary' => 'Evento de pull request recibido y enviado a RabbitMQ.',
            'payload' => $payload,
        ]);

        ProcessGitHubEventJob::dispatch($integrationEvent->id, $payload, $action)
            ->onConnection('rabbitmq')
            ->onQueue('github-events');

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

    private function buildExternalReference(array $payload): ?string
    {
        $repository = $payload['repository']['full_name'] ?? null;
        $pullRequestNumber = $payload['pull_request']['number'] ?? null;

        if (! $repository || ! $pullRequestNumber) {
            return null;
        }

        return $repository.'#'.$pullRequestNumber;
    }
}
