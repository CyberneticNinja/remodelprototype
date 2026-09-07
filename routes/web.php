<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientActivationController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomPhotoController;
use App\Http\Controllers\SignatureController;
use Illuminate\Support\Facades\Route;

// ── Guest routes ──────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Client account activation — reachable only via a signed, temporary email link
Route::get('/clients/{user}/activate', [ClientActivationController::class, 'show'])
    ->middleware('signed')->name('clients.activate');
Route::post('/clients/{user}/activate', [ClientActivationController::class, 'store'])
    ->middleware('signed')->name('clients.activate.store');

// ── Authenticated routes ───────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard — contractors and clients both land here (see ProjectController)
    Route::get('/', [ProjectController::class, 'index'])->name('dashboard');

    // Clients — managed by contractors only
    Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('clients', [ClientController::class, 'store'])->name('clients.store');
    Route::post('clients/{client}/resend-invite', [ClientController::class, 'resendInvite'])->name('clients.resend-invite');

    // Projects
    Route::resource('projects', ProjectController::class)->only([
        'index', 'create', 'store', 'show'
    ]);

    // Rooms — nested under projects
    Route::get('projects/{project}/rooms/create', [RoomController::class, 'create'])->name('rooms.create');
    Route::post('projects/{project}/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::get('projects/{project}/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
    Route::get('projects/{project}/rooms/{room}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
    Route::put('projects/{project}/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
    Route::post('projects/{project}/rooms/{room}/photos', [RoomPhotoController::class, 'store'])->name('rooms.photos.store');

    // Signatures — in person: contractor hands the device to the client.
    // Registered BEFORE the wildcard {role} route below, since "in-person"
    // would otherwise be swallowed by {role} there.
    Route::get('projects/{project}/rooms/{room}/signatures/{stage}/in-person', [SignatureController::class, 'createInPerson'])->name('signatures.person.create');
    Route::post('projects/{project}/rooms/{room}/signatures/{stage}/in-person', [SignatureController::class, 'storeInPerson'])->name('signatures.person.store');

    // Signatures — self-service: contractor signing themselves, or a client
    // logged in as themselves signing online.
    Route::get('projects/{project}/rooms/{room}/signatures/{stage}/{role}', [SignatureController::class, 'create'])->name('signatures.create');
    Route::post('projects/{project}/rooms/{room}/signatures/{stage}/{role}', [SignatureController::class, 'store'])->name('signatures.store');
});
