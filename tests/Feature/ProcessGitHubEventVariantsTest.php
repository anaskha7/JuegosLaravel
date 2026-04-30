<?php

namespace Tests\Feature;

use App\Jobs\ProcessGitHubEventJob;
use App\Models\IntegrationEvent;
use App\Services\GitHubPullRequestService;
use App\Services\PullRequestReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ProcessGitHubEventVariantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_push_event_is_processed_without_ai_review(): void
    {
        $payload = [
            'ref' => 'refs/heads/main',
            'after' => '7a1b2c3d4e5f67890123456789abcdef01234567',
            'repository' => [
                'full_name' => 'anaskha7/JuegosLaravel',
            ],
            'pusher' => [
                'name' => 'queue-bot',
            ],
            'commits' => [
                ['id' => '7a1b2c3', 'message' => 'Primer commit'],
                ['id' => '8b2c3d4', 'message' => 'Segundo commit'],
            ],
        ];

        $integrationEvent = IntegrationEvent::query()->create([
            'source' => 'github',
            'event_name' => 'commit.pushed',
            'status' => 'queued',
            'queue_connection' => 'rabbitmq',
            'queue_name' => 'github-events',
            'external_reference' => 'anaskha7/JuegosLaravel@7a1b2c3',
            'summary' => 'Pendiente de procesar.',
            'payload' => $payload,
        ]);

        $gitHubPullRequestService = Mockery::mock(GitHubPullRequestService::class);
        $gitHubPullRequestService->shouldNotReceive('fetchDiff');
        $gitHubPullRequestService->shouldNotReceive('postReviewComment');

        $pullRequestReviewService = Mockery::mock(PullRequestReviewService::class);
        $pullRequestReviewService->shouldNotReceive('review');

        $job = new ProcessGitHubEventJob($integrationEvent->id, $payload, 'pushed', 'push');
        $job->handle($gitHubPullRequestService, $pullRequestReviewService);

        $integrationEvent->refresh();

        $this->assertSame('processed', $integrationEvent->status);
        $this->assertSame('Push de commits procesado correctamente.', $integrationEvent->summary);
        $this->assertSame(2, $integrationEvent->result['commit_count']);
    }

    public function test_simulated_pull_request_opened_is_processed_without_external_calls(): void
    {
        $payload = [
            'simulated' => true,
            'action' => 'opened',
            'repository' => [
                'full_name' => 'anaskha7/JuegosLaravel',
            ],
            'pull_request' => [
                'number' => 501,
                'title' => 'PR simulada',
            ],
        ];

        $integrationEvent = IntegrationEvent::query()->create([
            'source' => 'github',
            'event_name' => 'pull_request.opened',
            'status' => 'queued',
            'queue_connection' => 'rabbitmq',
            'queue_name' => 'github-events',
            'external_reference' => 'anaskha7/JuegosLaravel#501',
            'summary' => 'Pendiente de procesar.',
            'payload' => $payload,
        ]);

        $gitHubPullRequestService = Mockery::mock(GitHubPullRequestService::class);
        $gitHubPullRequestService->shouldNotReceive('fetchDiff');
        $gitHubPullRequestService->shouldNotReceive('postReviewComment');

        $pullRequestReviewService = Mockery::mock(PullRequestReviewService::class);
        $pullRequestReviewService->shouldNotReceive('review');

        $job = new ProcessGitHubEventJob($integrationEvent->id, $payload, 'opened', 'pull_request');
        $job->handle($gitHubPullRequestService, $pullRequestReviewService);

        $integrationEvent->refresh();

        $this->assertSame('processed', $integrationEvent->status);
        $this->assertSame('Simulación de GitHub procesada correctamente en RabbitMQ.', $integrationEvent->summary);
        $this->assertTrue($integrationEvent->result['simulated']);
    }
}
