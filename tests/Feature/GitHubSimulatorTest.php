<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Jobs\ProcessGitHubEventJob;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class GitHubSimulatorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_privileged_users_can_open_the_github_simulator(): void
    {
        $user = $this->createUserWithRole(
            UserRole::Manager,
            'Simulador Manager',
            'simulador-manager@example.com',
            'sim-manager-token'
        );

        $response = $this->actingAs($user)->get('/integraciones/github/simulador');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Integrations/GitHubSimulator')
            ->has('scenarios', 4)
            ->has('latestGitHubEvents'));
    }

    public function test_players_cannot_open_the_github_simulator(): void
    {
        $user = $this->createUserWithRole(
            UserRole::Player,
            'Simulador Player',
            'simulador-player@example.com',
            'sim-player-token'
        );

        $response = $this->actingAs($user)->get('/integraciones/github/simulador');

        $response->assertForbidden();
    }

    public function test_simulator_can_queue_all_supported_github_events(): void
    {
        Queue::fake();

        $user = $this->createUserWithRole(
            UserRole::Admin,
            'Simulador Admin',
            'simulador-admin@example.com',
            'sim-admin-token'
        );

        $response = $this->actingAs($user)->post('/integraciones/github/simulador', [
            'scenario' => 'all',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Se han enviado las 4 simulaciones de GitHub a RabbitMQ.');

        $this->assertDatabaseHas('integration_events', ['event_name' => 'commit.pushed', 'status' => 'queued']);
        $this->assertDatabaseHas('integration_events', ['event_name' => 'pull_request.opened', 'status' => 'queued']);
        $this->assertDatabaseHas('integration_events', ['event_name' => 'pull_request.merged', 'status' => 'queued']);
        $this->assertDatabaseHas('integration_events', ['event_name' => 'issue.created', 'status' => 'queued']);

        Queue::assertPushed(ProcessGitHubEventJob::class, 4);
    }

    private function createUserWithRole(
        UserRole $role,
        string $name,
        string $email,
        string $apiToken
    ): User {
        Role::query()->firstOrCreate([
            'name' => $role->value,
        ], [
            'label' => $role->label(),
        ]);

        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => 'password',
            'api_token' => $apiToken,
        ]);

        $user->syncRoles([$role]);

        return $user;
    }
}
