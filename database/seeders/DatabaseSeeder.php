<?php

namespace Database\Seeders;

use App\Enums\GameStatus;
use App\Enums\UserRole;
use App\Models\Game;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Inserta los datos iniciales de la base de datos.
     */
    public function run(): void
    {
        collect(UserRole::cases())->each(function (UserRole $role): void {
            Role::query()->updateOrCreate(
                ['name' => $role->value],
                ['label' => $role->label()]
            );
        });

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@laraveljuegos.test'],
            [
                'name' => 'Administrador',
                'password' => 'password',
                'api_token' => 'admin-demo-token',
            ]
        );
        $admin->syncRoles([UserRole::Admin]);

        $manager = User::query()->updateOrCreate(
            ['email' => 'gestor@laraveljuegos.test'],
            [
                'name' => 'Gestor',
                'password' => 'password',
                'api_token' => 'manager-demo-token',
            ]
        );
        $manager->syncRoles([UserRole::Manager]);

        $player = User::query()->updateOrCreate(
            ['email' => 'jugador@laraveljuegos.test'],
            [
                'name' => 'Jugador',
                'password' => 'password',
                'api_token' => 'player-demo-token',
            ]
        );
        $player->syncRoles([UserRole::Player]);

        Game::query()->updateOrCreate(
            ['slug' => 'laberinto-cosmico'],
            [
                'title' => 'Laberinto Cósmico',
                'description' => 'Explora una cuadrícula 3D, recoge energía y evita los bloques rojos.',
                'instructions' => 'Muévete con WASD o flechas, recoge las 6 esferas azules y llega a la meta.',
                'status' => GameStatus::Published,
                'game_url' => '/games/laberinto-cosmico/index.html',
                'created_by' => $manager->id,
            ]
        );

        Game::query()->updateOrCreate(
            ['slug' => 'reactor-orbital'],
            [
                'title' => 'Orb Rush',
                'description' => 'Deslízate por una arena luminosa y captura orbes antes de que se agote el tiempo.',
                'instructions' => 'Muévete con WASD o flechas y recoge 12 orbes morados para cerrar la partida.',
                'status' => GameStatus::Published,
                'game_url' => '/games/reactor-orbital/index.html',
                'created_by' => $manager->id,
            ]
        );

        Game::query()->updateOrCreate(
            ['slug' => 'salto-neon'],
            [
                'title' => 'Carril Neón',
                'description' => 'Runner 3D por carriles: esquiva barreras y resiste todo lo posible.',
                'instructions' => 'Muévete con A y D o flechas izquierda/derecha para cambiar de carril y evitar obstáculos.',
                'status' => GameStatus::Published,
                'game_url' => '/games/salto-neon/index.html',
                'created_by' => $admin->id,
            ]
        );

        Game::query()->updateOrCreate(
            ['slug' => 'torre-reflejos'],
            [
                'title' => 'Torre de Reflejos',
                'description' => 'Borrador de un juego de puntería aún no publicado.',
                'instructions' => 'Dispara a los objetivos y ajusta la sensibilidad desde el menú del juego.',
                'status' => GameStatus::Draft,
                'game_url' => '/games/torre-reflejos/index.html',
                'created_by' => $admin->id,
            ]
        );
    }
}
