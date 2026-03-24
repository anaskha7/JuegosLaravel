<?php

use App\Enums\UserRole;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GameController;
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
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/catalogo', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('/catalogo/{game}', [CatalogController::class, 'show'])->name('catalog.show');

    Route::middleware('role:admin,manager')->group(function (): void {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::patch('/gestion/juegos/{game}/status', [GameController::class, 'toggleStatus'])->name('games.toggle-status');
        Route::resource('gestion/juegos', GameController::class)
            ->parameters(['juegos' => 'game'])
            ->except('show')
            ->names('games');
    });
});
