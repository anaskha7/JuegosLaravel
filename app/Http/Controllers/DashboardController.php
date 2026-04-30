<?php

namespace App\Http\Controllers;

use App\Enums\GameStatus;
use App\Enums\UserRole;
use App\Models\Game;
use App\Models\GameSession;
use App\Models\IntegrationEvent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $stats = [
            'published_games' => Game::query()->where('status', GameStatus::Published)->count(),
            'managed_games' => $user->hasAnyRole(UserRole::Admin, UserRole::Manager)
                ? Game::query()->count()
                : Game::query()->where('status', GameStatus::Published)->count(),
            'sessions_played' => GameSession::query()->where('user_id', $user->id)->count(),
            'active_sessions' => GameSession::query()->where('status', 'in_progress')->count(),
            'integration_events' => IntegrationEvent::query()->count(),
            'queued_integrations' => IntegrationEvent::query()->whereIn('status', ['queued', 'processing'])->count(),
        ];

        $latestSessions = GameSession::query()
            ->with('game')
            ->where('user_id', $user->id)
            ->latest('started_at')
            ->limit(5)
            ->get();

        $latestGames = Game::query()
            ->with('creator')
            ->when(
                $user->hasAnyRole(UserRole::Player),
                fn ($query) => $query->where('status', GameStatus::Published)
            )
            ->latest()
            ->limit(5)
            ->get();

        $latestIntegrationEvents = IntegrationEvent::query()
            ->latest()
            ->limit(6)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'latestSessions' => $latestSessions->map(fn (GameSession $session) => [
                'id' => $session->id,
                'status' => $session->status,
                'started_at' => optional($session->started_at)?->format('d/m/Y H:i'),
                'game_title' => $session->game?->title,
            ]),
            'latestGames' => $latestGames->map(fn (Game $game) => [
                'id' => $game->id,
                'title' => $game->title,
                'status' => $game->status->value,
                'status_label' => $game->status->label(),
                'creator_name' => $game->creator?->name,
            ]),
            'latestIntegrationEvents' => $latestIntegrationEvents->map(fn (IntegrationEvent $event) => [
                'id' => $event->id,
                'source' => strtoupper($event->source),
                'event_name' => $event->event_name,
                'status' => $event->status,
                'summary' => $event->summary,
                'external_reference' => $event->external_reference,
                'created_at' => $event->created_at?->format('d/m/Y H:i'),
            ]),
        ]);
    }
}
