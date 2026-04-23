<?php

namespace App\Http\Controllers;

use App\Events\GameChatMessageSent;
use App\Jobs\ProcessPlatformEventJob;
use App\Models\Game;
use App\Models\GameChatMessage;
use App\Models\IntegrationEvent;
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

        $integrationEvent = IntegrationEvent::query()->create([
            'source' => 'app',
            'event_name' => 'game.chat.message_sent',
            'status' => 'queued',
            'queue_connection' => 'rabbitmq',
            'queue_name' => 'platform-events',
            'external_reference' => 'chat#'.$chatMessage->id,
            'summary' => 'Mensaje de chat enviado a RabbitMQ.',
            'payload' => [
                'game_id' => $game->id,
                'game_title' => $game->title,
                'chat_message_id' => $chatMessage->id,
                'user_id' => $request->user()->id,
            ],
        ]);

        ProcessPlatformEventJob::dispatch($integrationEvent->id)
            ->onConnection('rabbitmq')
            ->onQueue('platform-events');

        return response()->json([
            'message' => $chatMessage->toChatPayload(),
        ], 201);
    }
}
