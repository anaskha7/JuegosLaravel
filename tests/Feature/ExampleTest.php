<?php

namespace Tests\Feature;

use App\Enums\GameStatus;
use App\Enums\UserRole;
use App\Models\Game;
use App\Models\GameChatMessage;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guests_are_redirected_to_login_from_root(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_users_can_open_dashboard(): void
    {
        $user = $this->createUserWithRole(
            UserRole::Manager,
            'Gestor Test',
            'gestor-test@example.com',
            'test-token'
        );

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Dashboard')->has('stats'));
    }

    public function test_players_cannot_open_dashboard(): void
    {
        $user = $this->createUserWithRole(
            UserRole::Player,
            'Jugador Test',
            'jugador-test@example.com',
            'test-token-player'
        );

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertForbidden();
    }

    public function test_internal_games_use_relative_play_urls(): void
    {
        $user = $this->createUserWithRole(
            UserRole::Player,
            'Jugador Test 2',
            'jugador-test-2@example.com',
            'test-token-player-2'
        );

        $game = Game::query()->create([
            'title' => 'Juego Test',
            'slug' => 'juego-test',
            'description' => 'Descripcion de prueba',
            'instructions' => 'Instrucciones de prueba',
            'status' => GameStatus::Published,
            'game_url' => '/games/laberinto-cosmico/index.html',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get("/catalogo/{$game->id}");

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Catalog/Show')
            ->where('game.play_url', "/games/laberinto-cosmico/index.html?game_id={$game->id}&token=test-token-player-2"));
    }

    public function test_players_can_send_messages_in_published_game_chat(): void
    {
        $user = $this->createUserWithRole(
            UserRole::Player,
            'Chat Player',
            'chat-player@example.com',
            'chat-player-token'
        );

        $game = Game::query()->create([
            'title' => 'Juego Chat',
            'slug' => 'juego-chat',
            'description' => 'Descripcion de prueba',
            'instructions' => 'Instrucciones de prueba',
            'status' => GameStatus::Published,
            'game_url' => '/games/laberinto-cosmico/index.html',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->postJson(route('games.chat.store', $game), [
            'message' => 'Hola equipo',
        ]);

        $response->assertCreated()
            ->assertJsonPath('message.message', 'Hola equipo')
            ->assertJsonPath('message.user.name', 'Chat Player');

        $this->assertDatabaseHas('game_chat_messages', [
            'game_id' => $game->id,
            'user_id' => $user->id,
            'message' => 'Hola equipo',
        ]);
    }

    public function test_players_cannot_send_messages_in_unpublished_game_chat(): void
    {
        $manager = $this->createUserWithRole(
            UserRole::Manager,
            'Gestor Chat',
            'gestor-chat@example.com',
            'manager-chat-token'
        );

        $player = $this->createUserWithRole(
            UserRole::Player,
            'Jugador Chat',
            'jugador-chat@example.com',
            'player-chat-token'
        );

        $game = Game::query()->create([
            'title' => 'Juego Oculto',
            'slug' => 'juego-oculto',
            'description' => 'Descripcion de prueba',
            'instructions' => 'Instrucciones de prueba',
            'status' => GameStatus::Draft,
            'game_url' => '/games/laberinto-cosmico/index.html',
            'created_by' => $manager->id,
        ]);

        $response = $this->actingAs($player)->postJson(route('games.chat.store', $game), [
            'message' => 'Hola desde fuera',
        ]);

        $response->assertNotFound();
        $this->assertDatabaseCount(GameChatMessage::class, 0);
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
