<?php

use App\Models\Game;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('games.{gameId}', function ($user, int $gameId) {
    $game = Game::query()->find($gameId);

    if (! $game) {
        return false;
    }

    return $game->isAccessibleBy($user);
});
