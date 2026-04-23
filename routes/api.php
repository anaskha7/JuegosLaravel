<?php

use App\Http\Controllers\Api\GameCatalogController;
use App\Http\Controllers\Api\GameSessionController;
use Illuminate\Support\Facades\Route;

Route::get('/games', [GameCatalogController::class, 'index']);

Route::middleware('api.token')->group(function (): void {
    Route::post('/sessions/start', [GameSessionController::class, 'start']);
    Route::patch('/sessions/{session}/finish', [GameSessionController::class, 'finish']);
    Route::post('/sessions/{session}/emotions', [GameSessionController::class, 'storeEmotion']);
});

Route::post('/github/webhook', [\App\Http\Controllers\GitHubWebhookController::class, 'handle']);
