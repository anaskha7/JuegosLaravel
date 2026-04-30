<?php

namespace App\Services;

use App\Jobs\ProcessGitHubEventJob;
use App\Models\IntegrationEvent;
use Illuminate\Support\Str;
use InvalidArgumentException;

class GitHubEventDispatcher
{
    public function supports(string $githubEvent, array $payload): bool
    {
        return $this->resolveMetadata($githubEvent, $payload) !== null;
    }

    public function dispatch(string $githubEvent, array $payload): IntegrationEvent
    {
        $metadata = $this->resolveMetadata($githubEvent, $payload);

        if ($metadata === null) {
            throw new InvalidArgumentException('El evento de GitHub no está soportado.');
        }

        $integrationEvent = IntegrationEvent::query()->create([
            'source' => 'github',
            'event_name' => $metadata['event_name'],
            'status' => 'queued',
            'queue_connection' => 'rabbitmq',
            'queue_name' => 'github-events',
            'external_reference' => $this->buildExternalReference($githubEvent, $payload),
            'summary' => $metadata['summary'],
            'payload' => $payload,
        ]);

        ProcessGitHubEventJob::dispatch(
            $integrationEvent->id,
            $payload,
            $metadata['action'],
            $githubEvent
        )->onConnection('rabbitmq')->onQueue('github-events');

        return $integrationEvent;
    }

    private function resolveMetadata(string $githubEvent, array $payload): ?array
    {
        if ($githubEvent === 'push' && isset($payload['after'])) {
            return [
                'event_name' => 'commit.pushed',
                'summary' => 'Push de commits recibido y enviado a RabbitMQ.',
                'action' => 'pushed',
            ];
        }

        if ($githubEvent === 'issues' && ($payload['action'] ?? null) === 'opened' && isset($payload['issue'])) {
            return [
                'event_name' => 'issue.created',
                'summary' => 'Issue creada y enviada a RabbitMQ.',
                'action' => 'opened',
            ];
        }

        if ($githubEvent !== 'pull_request' || ! isset($payload['pull_request'])) {
            return null;
        }

        $action = (string) ($payload['action'] ?? 'opened');

        if ($action === 'closed' && ($payload['pull_request']['merged'] ?? false)) {
            return [
                'event_name' => 'pull_request.merged',
                'summary' => 'Pull request fusionada y enviada a RabbitMQ.',
                'action' => 'closed',
            ];
        }

        if (! in_array($action, ['opened', 'reopened', 'synchronize'], true)) {
            return null;
        }

        return [
            'event_name' => 'pull_request.'.$action,
            'summary' => 'Evento de pull request recibido y enviado a RabbitMQ.',
            'action' => $action,
        ];
    }

    private function buildExternalReference(string $githubEvent, array $payload): ?string
    {
        $repository = $payload['repository']['full_name'] ?? null;

        if (! $repository) {
            return null;
        }

        return match ($githubEvent) {
            'push' => isset($payload['after'])
                ? $repository.'@'.Str::substr((string) $payload['after'], 0, 7)
                : $repository,
            'issues' => isset($payload['issue']['number'])
                ? $repository.'#'.$payload['issue']['number']
                : $repository,
            default => isset($payload['pull_request']['number'])
                ? $repository.'#'.$payload['pull_request']['number']
                : $repository,
        };
    }
}
