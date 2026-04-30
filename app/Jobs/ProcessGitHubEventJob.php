<?php

namespace App\Jobs;

use App\Models\IntegrationEvent;
use App\Services\GitHubPullRequestService;
use App\Services\PullRequestReviewService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessGitHubEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $integrationEventId,
        public array $payload,
        public string $action,
        public string $event = 'pull_request'
    )
    {
        $this->onConnection('rabbitmq');
        $this->onQueue('github-events');
    }

    public function handle(
        GitHubPullRequestService $gitHubPullRequestService,
        PullRequestReviewService $pullRequestReviewService
    ): void
    {
        $integrationEvent = IntegrationEvent::query()->find($this->integrationEventId);

        if (! $integrationEvent) {
            return;
        }

        $integrationEvent->markAsProcessing($this->processingSummary());

        Log::info('Procesando evento de GitHub', [
            'integration_event_id' => $integrationEvent->id,
            'event' => $this->event,
            'action' => $this->action,
        ]);

        try {
            if ($this->isSimulated()) {
                $this->handleSimulatedEvent($integrationEvent);

                return;
            }

            match ($this->event) {
                'push' => $this->handlePushEvent($integrationEvent),
                'issues' => $this->handleIssueEvent($integrationEvent),
                'pull_request' => $this->handlePullRequestEvent(
                    $integrationEvent,
                    $gitHubPullRequestService,
                    $pullRequestReviewService
                ),
                default => $integrationEvent->markAsIgnored('El evento de GitHub no requiere procesamiento.'),
            };
        } catch (\Throwable $throwable) {
            Log::error('Fallo en el procesamiento del evento de GitHub', [
                'integration_event_id' => $this->integrationEventId,
                'error' => $throwable->getMessage(),
            ]);

            $integrationEvent->markAsFailed(
                $throwable->getMessage(),
                'La revisión automática de GitHub no pudo completarse.'
            );
        }
    }

    private function handleSimulatedEvent(IntegrationEvent $integrationEvent): void
    {
        $integrationEvent->markAsProcessed([
            'simulated' => true,
            'event' => $this->event,
            'action' => $this->action,
            'handled_by' => 'queue-worker',
        ], 'Simulación de GitHub procesada correctamente en RabbitMQ.');
    }

    private function handlePushEvent(IntegrationEvent $integrationEvent): void
    {
        $integrationEvent->markAsProcessed([
            'branch' => $this->payload['ref'] ?? null,
            'after' => $this->payload['after'] ?? null,
            'commit_count' => count($this->payload['commits'] ?? []),
            'pusher' => $this->payload['pusher']['name'] ?? null,
        ], 'Push de commits procesado correctamente.');
    }

    private function handleIssueEvent(IntegrationEvent $integrationEvent): void
    {
        if ($this->action !== 'opened' || ! isset($this->payload['issue'])) {
            $integrationEvent->markAsIgnored('La acción del issue no requiere procesamiento.');

            return;
        }

        $issue = $this->payload['issue'];

        $integrationEvent->markAsProcessed([
            'issue_number' => $issue['number'] ?? null,
            'title' => $issue['title'] ?? 'Sin título',
            'author' => $issue['user']['login'] ?? null,
        ], 'Issue de GitHub procesada correctamente.');
    }

    private function handlePullRequestEvent(
        IntegrationEvent $integrationEvent,
        GitHubPullRequestService $gitHubPullRequestService,
        PullRequestReviewService $pullRequestReviewService
    ): void {
        if ($this->isMergedPullRequest()) {
            $pullRequest = $this->payload['pull_request'] ?? [];

            $integrationEvent->markAsProcessed([
                'pull_request_number' => $pullRequest['number'] ?? null,
                'title' => $pullRequest['title'] ?? 'Sin título',
                'merged_by' => $pullRequest['merged_by']['login'] ?? null,
            ], 'Pull request fusionada procesada correctamente.');

            return;
        }

        if (! in_array($this->action, ['opened', 'reopened', 'synchronize'], true)) {
            $integrationEvent->markAsIgnored('La acción de GitHub no requiere revisión automática.');

            return;
        }

        $repository = $this->payload['repository']['full_name'] ?? null;
        $pullRequestNumber = $this->payload['pull_request']['number'] ?? null;
        $pullRequestTitle = $this->payload['pull_request']['title'] ?? 'Sin título';

        if (! $repository || ! $pullRequestNumber) {
            $integrationEvent->markAsFailed(
                'Falta el repositorio o el número de pull request en el payload.',
                'No se ha podido identificar la pull request de GitHub.'
            );

            return;
        }

        $diffDownloaded = true;
        $diffError = null;

        try {
            $diff = $gitHubPullRequestService->fetchDiff($this->payload);
        } catch (\Throwable $throwable) {
            $diffDownloaded = false;
            $diffError = $throwable->getMessage();
            $diff = $this->buildFallbackDiff($repository, (int) $pullRequestNumber, $pullRequestTitle, $diffError);
        }

        $review = $pullRequestReviewService->review($diff, [
            'repository' => $repository,
            'pull_request_number' => $pullRequestNumber,
            'title' => $pullRequestTitle,
        ]);

        $commentBody = "## Revisión automática\n\n".$review['body'];
        $commentPosted = $gitHubPullRequestService->postReviewComment(
            $repository,
            (int) $pullRequestNumber,
            $commentBody
        );

        $integrationEvent->markAsProcessed([
            'provider' => $review['provider'],
            'review' => $review['body'],
            'diff_downloaded' => $diffDownloaded,
            'diff_error' => $diffError,
            'comment_posted' => $commentPosted,
        ], $commentPosted
            ? 'Revisión automática publicada en la pull request.'
            : ($diffDownloaded
                ? 'Revisión generada. No se publicó comentario en GitHub.'
                : 'Evento procesado sin diff remoto. Revisión generada con contexto básico.'));
    }

    private function isSimulated(): bool
    {
        return (bool) ($this->payload['simulated'] ?? false);
    }

    private function isMergedPullRequest(): bool
    {
        return $this->action === 'closed' && (bool) ($this->payload['pull_request']['merged'] ?? false);
    }

    private function processingSummary(): string
    {
        if ($this->isSimulated()) {
            return 'Procesando simulación de GitHub en RabbitMQ.';
        }

        return match ($this->event) {
            'push' => 'Procesando push de GitHub en RabbitMQ.',
            'issues' => 'Procesando issue de GitHub en RabbitMQ.',
            default => $this->isMergedPullRequest()
                ? 'Procesando merge de pull request en RabbitMQ.'
                : 'Procesando pull request de GitHub en RabbitMQ.',
        };
    }

    private function buildFallbackDiff(
        string $repository,
        int $pullRequestNumber,
        string $pullRequestTitle,
        string $error
    ): string {
        return <<<TEXT
No se pudo descargar el diff remoto de la pull request.

Repositorio: {$repository}
Pull request: #{$pullRequestNumber}
Título: {$pullRequestTitle}
Error al recuperar el diff: {$error}

Genera una revisión básica a partir de este contexto y deja claro que falta el diff real.
TEXT;
    }
}
