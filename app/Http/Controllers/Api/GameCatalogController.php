<?php

namespace App\Http\Controllers\Api;

use App\Enums\GameStatus;
use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\JsonResponse;

class GameCatalogController extends Controller
{
    public function index(): JsonResponse
    {
        $games = Game::query()
            ->where('status', GameStatus::Published)
            ->get()
            ->map(fn (Game $game) => [
                'id' => $game->id,
                'title' => $game->title,
                'slug' => $game->slug,
                'description' => $game->description,
                'game_url' => $game->game_url,
                'playable_url' => $game->playableUrl(),
                'created_by' => $game->created_by,
            ]);

        return response()->json(['data' => $games]);
    }
}
