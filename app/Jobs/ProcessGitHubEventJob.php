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
        public string $action
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

        $integrationEvent->markAsProcessing('Procesando pull request de GitHub en RabbitMQ.');

        Log::info('Procesando evento de GitHub', [
            'integration_event_id' => $integrationEvent->id,
            'action' => $this->action,
        ]);

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

        try {
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
