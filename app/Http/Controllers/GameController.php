<?php

namespace App\Http\Controllers;

use App\Enums\GameStatus;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class GameController extends Controller
{
    public function index(): Response
    {
        $games = Game::query()->with('creator')->latest()->paginate(12);

        return Inertia::render('Games/Index', [
            'games' => $games->through(fn (Game $game) => [
                'id' => $game->id,
                'title' => $game->title,
                'slug' => $game->slug,
                'status' => $game->status->value,
                'status_label' => $game->status->label(),
                'creator_name' => $game->creator?->name,
                'game_url' => $game->game_url,
                'playable_url' => $game->playableUrl(),
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Games/Form', [
            'game' => null,
            'statuses' => collect(GameStatus::cases())->map(fn (GameStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateGame($request);
        $validated['slug'] = Str::slug($validated['title']);
        $validated['created_by'] = $request->user()->id;

        Game::query()->create($validated);

        return redirect()->route('games.index')->with('status', 'Juego creado.');
    }

    public function edit(Game $game): Response
    {
        return Inertia::render('Games/Form', [
            'game' => [
                'id' => $game->id,
                'title' => $game->title,
                'description' => $game->description,
                'instructions' => $game->instructions,
                'status' => $game->status->value,
                'game_url' => $game->game_url,
                'playable_url' => $game->playableUrl(),
            ],
            'statuses' => collect(GameStatus::cases())->map(fn (GameStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ]),
        ]);
    }

    public function update(Request $request, Game $game): RedirectResponse
    {
        $validated = $this->validateGame($request);
        $validated['slug'] = Str::slug($validated['title']);

        $game->update($validated);

        return redirect()->route('games.index')->with('status', 'Juego actualizado.');
    }

    public function destroy(Game $game): RedirectResponse
    {
        $game->delete();

        return redirect()->route('games.index')->with('status', 'Juego eliminado.');
    }

    public function toggleStatus(Game $game): RedirectResponse
    {
        $nextStatus = $game->status === GameStatus::Published
            ? GameStatus::Draft
            : GameStatus::Published;

        $game->update(['status' => $nextStatus]);

        return redirect()->route('games.index')->with(
            'status',
            $nextStatus === GameStatus::Published ? 'Juego publicado.' : 'Juego despublicado.'
        );
    }

    protected function validateGame(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'instructions' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(GameStatus::class)],
            'game_url' => ['required', 'string', 'max:2048'],
        ]);
    }
}
