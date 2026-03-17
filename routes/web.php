<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

// ── Guest routes ──────────────────────────────────────────────────────────────
Route::middleware('guest:contractor')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// ── Authenticated routes ───────────────────────────────────────────────────────
Route::middleware('auth:contractor')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [ProjectController::class, 'index'])->name('dashboard');

    // Projects
    Route::resource('projects', ProjectController::class)->only([
        'index', 'create', 'store', 'show'
    ]);

    // Rooms — nested under projects
    Route::get('projects/{project}/rooms/create', [RoomController::class, 'create'])->name('rooms.create');
    Route::post('projects/{project}/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::get('projects/{project}/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
});
