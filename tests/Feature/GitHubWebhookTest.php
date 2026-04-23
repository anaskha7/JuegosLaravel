<?php

namespace Tests\Feature;

use App\Jobs\ProcessGitHubEventJob;
use App\Models\IntegrationEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class GitHubWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_pull_request_event_is_queued_when_signature_is_valid(): void
    {
        Queue::fake();
        config()->set('services.github.webhook_secret', 'secret-webhook');

        $payload = [
            'action' => 'opened',
            'repository' => [
                'full_name' => 'anaskha7/JuegosLaravel',
            ],
            'pull_request' => [
                'number' => 12,
                'title' => 'Añadir RabbitMQ',
                'diff_url' => 'https://example.com/pr.diff',
            ],
        ];

        $response = $this->withHeaders($this->githubHeaders($payload, 'pull_request', 'secret-webhook'))
            ->postJson('/api/github/webhook', $payload);

        $response->assertAccepted()
            ->assertJsonPath('status', 'Evento de GitHub enviado a la cola.');

        $this->assertDatabaseHas('integration_events', [
            'source' => 'github',
            'event_name' => 'pull_request.opened',
            'status' => 'queued',
            'external_reference' => 'anaskha7/JuegosLaravel#12',
        ]);

        Queue::assertPushed(ProcessGitHubEventJob::class, 1);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        config()->set('services.github.webhook_secret', 'secret-webhook');

        $payload = [
            'action' => 'opened',
            'pull_request' => [
                'number' => 7,
            ],
        ];

        $response = $this->withHeaders([
            'X-GitHub-Event' => 'pull_request',
            'X-Hub-Signature-256' => 'sha256=firma-invalida',
        ])->postJson('/api/github/webhook', $payload);

        $response->assertUnauthorized()
            ->assertJsonPath('message', 'Firma de GitHub no válida.');

        $this->assertDatabaseCount('integration_events', 0);
    }

    public function test_ping_event_is_recorded_as_processed(): void
    {
        config()->set('services.github.webhook_secret', 'secret-webhook');

        $payload = [
            'zen' => 'Keep it logically awesome.',
        ];

        $response = $this->withHeaders($this->githubHeaders($payload, 'ping', 'secret-webhook'))
            ->postJson('/api/github/webhook', $payload);

        $response->assertOk()
            ->assertJsonPath('status', 'Webhook GitHub operativo.');

        $this->assertDatabaseHas('integration_events', [
            'source' => 'github',
            'event_name' => 'ping',
            'status' => 'processed',
        ]);
    }

    private function githubHeaders(array $payload, string $event, string $secret): array
    {
        $content = json_encode($payload, JSON_THROW_ON_ERROR);

        return [
            'X-GitHub-Event' => $event,
            'X-Hub-Signature-256' => 'sha256='.hash_hmac('sha256', $content, $secret),
        ];
    }
}
