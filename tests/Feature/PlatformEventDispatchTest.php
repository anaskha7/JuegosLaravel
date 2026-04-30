<?php

namespace Tests\Feature;

use App\Enums\GameStatus;
use App\Enums\UserRole;
use App\Jobs\ProcessPlatformEventJob;
use App\Models\Game;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PlatformEventDispatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_starting_a_session_dispatches_an_internal_event(): void
    {
        Queue::fake();

        $user = $this->createPlayer();
        $game = $this->createPublishedGame($user);

        $response = $this->withToken($user->api_token)->postJson('/api/sessions/start', [
            'game_id' => $game->id,
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('integration_events', [
            'source' => 'app',
            'event_name' => 'game.session.started',
            'status' => 'queued',
        ]);

        Queue::assertPushed(ProcessPlatformEventJob::class, 1);
    }

    public function test_finishing_a_session_dispatches_an_internal_event(): void
    {
        Queue::fake();

        $user = $this->createPlayer();
        $game = $this->createPublishedGame($user);

        $sessionId = $this->withToken($user->api_token)->postJson('/api/sessions/start', [
            'game_id' => $game->id,
        ])->json('data.id');

        $response = $this->withToken($user->api_token)->patchJson("/api/sessions/{$sessionId}/finish", [
            'score' => 45,
            'status' => 'completed',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('integration_events', [
            'source' => 'app',
            'event_name' => 'game.session.finished',
            'status' => 'queued',
        ]);

        Queue::assertPushed(ProcessPlatformEventJob::class, 2);
    }

    private function createPlayer(): User
    {
        Role::query()->firstOrCreate([
            'name' => UserRole::Player->value,
        ], [
            'label' => UserRole::Player->label(),
        ]);

        $user = User::query()->create([
            'name' => 'Jugador de prueba',
            'email' => 'jugador-prueba@example.com',
            'password' => 'password',
            'api_token' => 'player-token-test',
        ]);

        $user->syncRoles([UserRole::Player]);

        return $user;
    }

    private function createPublishedGame(User $creator): Game
    {
        return Game::query()->create([
            'title' => 'Juego de prueba',
            'slug' => 'juego-de-prueba',
            'description' => 'Juego para verificar eventos internos.',
            'instructions' => 'Pulsa iniciar y terminar.',
            'status' => GameStatus::Published,
            'game_url' => '/games/laberinto-cosmico/index.html',
            'created_by' => $creator->id,
        ]);
    }
}
