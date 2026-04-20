<?php

namespace App\Http\Controllers;

use App\Events\GameChatMessageSent;
use App\Models\Game;
use App\Models\GameChatMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GameChatMessageController extends Controller
{
    public function store(Request $request, Game $game): JsonResponse
    {
        abort_unless($game->isAccessibleBy($request->user()), 404);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        $chatMessage = GameChatMessage::query()->create([
            'game_id' => $game->id,
            'user_id' => $request->user()->id,
            'message' => Str::of($validated['message'])->trim()->value(),
        ]);

        $chatMessage->load('user.roles');

        broadcast(new GameChatMessageSent($chatMessage))->toOthers();

        return response()->json([
            'message' => $chatMessage->toChatPayload(),
        ], 201);
    }
}
