<?php

use App\Enums\UserRole;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\FaceLoginController;
use App\Http\Controllers\Auth\PasswordRecoveryController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FaceReferenceController;
use App\Http\Controllers\GameChatMessageController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\GitHubSimulatorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return auth()->user()->hasAnyRole(UserRole::Admin, UserRole::Manager)
        ? redirect()->route('dashboard')
        : redirect()->route('catalog.index');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::post('/login/face-id', [FaceLoginController::class, 'store'])->name('login.face');
    Route::get('/recuperar-password', [PasswordRecoveryController::class, 'createRequest'])->name('password.request');
    Route::post('/recuperar-password', [PasswordRecoveryController::class, 'sendCode'])->name('password.email');
    Route::get('/recuperar-password/codigo', [PasswordRecoveryController::class, 'createVerify'])->name('password.verify');
    Route::post('/recuperar-password/codigo', [PasswordRecoveryController::class, 'verifyCode'])->name('password.verify.store');
    Route::get('/recuperar-password/nueva', [PasswordRecoveryController::class, 'createReset'])->name('password.reset');
    Route::post('/recuperar-password/nueva', [PasswordRecoveryController::class, 'resetPassword'])->name('password.reset.store');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/seguridad/face-id', [FaceReferenceController::class, 'edit'])->name('face-security.edit');
    Route::post('/seguridad/face-id', [FaceReferenceController::class, 'store'])->name('face-security.store');
    Route::delete('/seguridad/face-id', [FaceReferenceController::class, 'destroy'])->name('face-security.destroy');
    Route::get('/catalogo', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('/catalogo/{game}', [CatalogController::class, 'show'])->name('catalog.show');
    Route::post('/catalogo/{game}/chat/messages', [GameChatMessageController::class, 'store'])
        ->middleware('throttle:30,1')
        ->name('games.chat.store');

    Route::middleware('role:admin,manager')->group(function (): void {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::get('/integraciones/github/simulador', [GitHubSimulatorController::class, 'index'])
            ->name('github-simulator.index');
        Route::post('/integraciones/github/simulador', [GitHubSimulatorController::class, 'store'])
            ->name('github-simulator.store');
        Route::patch('/gestion/juegos/{game}/status', [GameController::class, 'toggleStatus'])->name('games.toggle-status');
        Route::resource('gestion/juegos', GameController::class)
            ->parameters(['juegos' => 'game'])
            ->except('show')
            ->names('games');
    });
});
