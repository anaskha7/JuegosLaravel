<?php

namespace Tests\Feature;

use App\Enums\GameStatus;
use App\Enums\UserRole;
use App\Models\Game;
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
        $user = User::query()->create([
            'name' => 'Gestor Test',
            'email' => 'gestor-test@example.com',
            'password' => 'password',
            'api_token' => 'test-token',
        ]);
        Role::query()->create(['name' => UserRole::Manager->value, 'label' => UserRole::Manager->label()]);
        $user->syncRoles([UserRole::Manager]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Dashboard')->has('stats'));
    }

    public function test_players_cannot_open_dashboard(): void
    {
        $user = User::query()->create([
            'name' => 'Jugador Test',
            'email' => 'jugador-test@example.com',
            'password' => 'password',
            'api_token' => 'test-token-player',
        ]);
        Role::query()->create(['name' => UserRole::Player->value, 'label' => UserRole::Player->label()]);
        $user->syncRoles([UserRole::Player]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertForbidden();
    }

    public function test_internal_games_use_relative_play_urls(): void
    {
        $user = User::query()->create([
            'name' => 'Jugador Test 2',
            'email' => 'jugador-test-2@example.com',
            'password' => 'password',
            'api_token' => 'test-token-player-2',
        ]);
        Role::query()->create(['name' => UserRole::Player->value, 'label' => UserRole::Player->label()]);
        $user->syncRoles([UserRole::Player]);

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
}
