<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\LoginLinkController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomPhotoController;
use App\Http\Controllers\SignatureController;
use Illuminate\Support\Facades\Route;

// ── Guest routes ──────────────────────────────────────────────────────────────
// There is no password anywhere in this app. Everyone — contractors and any
// client who chooses to sign online — logs in by requesting a link emailed
// to them. The same /login/{token} pair also finishes a client's very first
// invite link (see ClientController), since "activate my account" and
// "log me in" are the same action once there's no password to set.
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [LoginLinkController::class, 'showRequestForm'])->name('login');
    Route::post('/login', [LoginLinkController::class, 'sendLink'])
        ->middleware('throttle:6,1')->name('login.send');

    // Public demo: one click into a shared, reset-on-a-schedule contractor
    // account (see demo:reset and User::demoContractor()). No email, no
    // verification — throttled since it hands out an authenticated session
    // to literally anyone who asks.
    Route::post('/demo', [DemoController::class, 'login'])
        ->middleware('throttle:10,1')->name('demo.login');
});

// Login link confirm/consume — reachable whether guest or already logged in
// (clicking a stale link while signed in should just work, not get redirected
// away by guest middleware). GET only shows a "click to confirm" interstitial
// and never consumes the token itself, since email clients and link scanners
// sometimes fetch a URL automatically; only the POST from that page's button
// — a deliberate action by a real person — actually consumes it.
Route::get('/login/{token}', [LoginLinkController::class, 'showConfirm'])->name('login.confirm');
Route::post('/login/{token}', [LoginLinkController::class, 'consume'])->name('login.consume');

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
