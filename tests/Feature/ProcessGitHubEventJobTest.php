<?php

namespace Tests\Feature;

use App\Jobs\ProcessGitHubEventJob;
use App\Models\IntegrationEvent;
use App\Services\GitHubPullRequestService;
use App\Services\PullRequestReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ProcessGitHubEventJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_falls_back_to_basic_context_when_diff_download_fails(): void
    {
        $payload = [
            'action' => 'opened',
            'repository' => [
                'full_name' => 'anaskha7/JuegosLaravel',
            ],
            'pull_request' => [
                'number' => 101,
                'title' => 'PR sin diff remoto',
                'diff_url' => 'https://example.com/pr.diff',
            ],
        ];

        $integrationEvent = IntegrationEvent::query()->create([
            'source' => 'github',
            'event_name' => 'pull_request.opened',
            'status' => 'queued',
            'queue_connection' => 'rabbitmq',
            'queue_name' => 'github-events',
            'external_reference' => 'anaskha7/JuegosLaravel#101',
            'summary' => 'Pendiente de procesar.',
            'payload' => $payload,
        ]);

        $gitHubPullRequestService = Mockery::mock(GitHubPullRequestService::class);
        $gitHubPullRequestService->shouldReceive('fetchDiff')
            ->once()
            ->andThrow(new \RuntimeException('No se pudo descargar el diff de GitHub.'));
        $gitHubPullRequestService->shouldReceive('postReviewComment')
            ->once()
            ->with('anaskha7/JuegosLaravel', 101, Mockery::type('string'))
            ->andReturnFalse();

        $pullRequestReviewService = Mockery::mock(PullRequestReviewService::class);
        $pullRequestReviewService->shouldReceive('review')
            ->once()
            ->with(Mockery::type('string'), Mockery::type('array'))
            ->andReturn([
                'provider' => 'none',
                'body' => 'Revisión generada con contexto básico.',
                'skipped' => true,
            ]);

        $job = new ProcessGitHubEventJob($integrationEvent->id, $payload, 'opened');
        $job->handle($gitHubPullRequestService, $pullRequestReviewService);

        $integrationEvent->refresh();

        $this->assertSame('processed', $integrationEvent->status);
        $this->assertSame('Evento procesado sin diff remoto. Revisión generada con contexto básico.', $integrationEvent->summary);
        $this->assertSame(false, $integrationEvent->result['diff_downloaded']);
        $this->assertSame(false, $integrationEvent->result['comment_posted']);
        $this->assertSame('Revisión generada con contexto básico.', $integrationEvent->result['review']);
    }
}
