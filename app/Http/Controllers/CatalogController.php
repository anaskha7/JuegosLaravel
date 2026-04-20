<?php

namespace App\Http\Controllers;

use App\Enums\GameStatus;
use App\Enums\UserRole;
use App\Models\Game;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function index(): Response
    {
        $games = Game::query()
            ->with('creator')
            ->where('status', GameStatus::Published)
            ->latest()
            ->paginate(12);

        return Inertia::render('Catalog/Index', [
            'games' => $games->through(fn (Game $game) => [
                'id' => $game->id,
                'slug' => $game->slug,
                'title' => $game->title,
                'description' => $game->description,
                'status_label' => $game->status->label(),
                'creator_name' => $game->creator?->name,
            ]),
        ]);
    }

    public function show(Game $game): Response
    {
        $user = request()->user();
        $canPreview = $user?->hasAnyRole(UserRole::Admin, UserRole::Manager) ?? false;

        abort_unless($game->isAccessibleBy($user), 404);

        $messages = $game->chatMessages()
            ->with('user.roles')
            ->latest()
            ->take(30)
            ->get()
            ->reverse()
            ->values();

        return Inertia::render('Catalog/Show', [
            'game' => [
                'id' => $game->id,
                'title' => $game->title,
                'slug' => $game->slug,
                'description' => $game->description,
                'instructions' => $game->instructions,
                'status' => $game->status->value,
                'status_label' => $game->status->label(),
                'play_url' => $game->playableUrl([
                    'game_id' => $game->id,
                    'token' => $user?->api_token,
                ]),
            ],
            'chat' => [
                'channel' => 'games.'.$game->id,
                'post_url' => route('games.chat.store', $game),
                'messages' => $messages->map(
                    fn ($message) => $message->toChatPayload()
                )->all(),
            ],
            'canPreview' => $canPreview,
        ]);
    }
}
