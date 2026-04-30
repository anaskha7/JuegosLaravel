<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessPlatformEventJob;
use App\Models\EmotionReading;
use App\Models\Game;
use App\Models\GameSession;
use App\Models\IntegrationEvent;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GameSessionController extends Controller
{
    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'game_id' => ['required', 'exists:games,id'],
            'started_at' => ['nullable', 'date'],
            'metadata' => ['nullable', 'array'],
        ]);

        $game = Game::query()->findOrFail($validated['game_id']);

        $session = GameSession::query()->create([
            'user_id' => $request->user()->id,
            'game_id' => $game->id,
            'started_at' => $validated['started_at'] ?? now(),
            'status' => 'in_progress',
            'metadata' => $validated['metadata'] ?? null,
        ]);

        $integrationEvent = IntegrationEvent::query()->create([
            'source' => 'app',
            'event_name' => 'game.session.started',
            'status' => 'queued',
            'queue_connection' => 'rabbitmq',
            'queue_name' => 'platform-events',
            'external_reference' => 'session#'.$session->id,
            'summary' => 'Inicio de partida enviado a RabbitMQ.',
            'payload' => [
                'game_id' => $game->id,
                'game_title' => $game->title,
                'session_id' => $session->id,
                'user_id' => $request->user()->id,
            ],
        ]);

        ProcessPlatformEventJob::dispatch($integrationEvent->id)
            ->onConnection('rabbitmq')
            ->onQueue('platform-events');

        return response()->json([
            'message' => 'Sesión iniciada.',
            'data' => $session,
        ], Response::HTTP_CREATED);
    }

    public function finish(Request $request, GameSession $session): JsonResponse
    {
        abort_unless($session->user_id === $request->user()->id, Response::HTTP_FORBIDDEN);

        $validated = $request->validate([
            'ended_at' => ['nullable', 'date'],
            'score' => ['nullable', 'numeric'],
            'status' => ['nullable', 'in:completed,abandoned'],
            'metadata' => ['nullable', 'array'],
        ]);

        $endedAt = isset($validated['ended_at']) ? Carbon::parse($validated['ended_at']) : now();
        $startedAt = $session->started_at ?? now();

        $session->update([
            'ended_at' => $endedAt,
            'duration_seconds' => $startedAt->diffInSeconds($endedAt),
            'score' => $validated['score'] ?? $session->score,
            'status' => $validated['status'] ?? 'completed',
            'metadata' => [
                ...($session->metadata ?? []),
                ...($validated['metadata'] ?? []),
            ],
        ]);

        $integrationEvent = IntegrationEvent::query()->create([
            'source' => 'app',
            'event_name' => 'game.session.finished',
            'status' => 'queued',
            'queue_connection' => 'rabbitmq',
            'queue_name' => 'platform-events',
            'external_reference' => 'session#'.$session->id,
            'summary' => 'Fin de partida enviado a RabbitMQ.',
            'payload' => [
                'game_id' => $session->game_id,
                'session_id' => $session->id,
                'user_id' => $request->user()->id,
                'score' => $session->score,
                'duration_seconds' => $session->duration_seconds,
            ],
        ]);

        ProcessPlatformEventJob::dispatch($integrationEvent->id)
            ->onConnection('rabbitmq')
            ->onQueue('platform-events');

        return response()->json([
            'message' => 'Sesión finalizada.',
            'data' => $session->fresh(),
        ]);
    }

    public function storeEmotion(Request $request, GameSession $session): JsonResponse
    {
        abort_unless($session->user_id === $request->user()->id, Response::HTTP_FORBIDDEN);

        $validated = $request->validate([
            'emotion' => ['required', 'string', 'max:100'],
            'confidence' => ['required', 'numeric', 'between:0,1'],
            'captured_at' => ['nullable', 'date'],
            'context' => ['nullable', 'array'],
        ]);

        $reading = EmotionReading::query()->create([
            'game_session_id' => $session->id,
            'emotion' => $validated['emotion'],
            'confidence' => $validated['confidence'],
            'captured_at' => $validated['captured_at'] ?? now(),
            'context' => $validated['context'] ?? null,
        ]);

        return response()->json([
            'message' => 'Lectura emocional registrada.',
            'data' => $reading,
        ], Response::HTTP_CREATED);
    }
}
