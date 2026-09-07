<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ClientActivationController extends Controller
{
    // Show the "set your password" form (only reachable via a valid signed link)
    public function show(Request $request, User $user)
    {
        abort_unless($request->hasValidSignature(), 403, 'This invite link is invalid or has expired.');
        abort_if($user->hasActivatedAccount(), 400, 'This account has already been activated — please log in.');

        return view('auth.invite-accept', ['client' => $user]);
    }

    // Set the password, activate, and log the client in
    public function store(Request $request, User $user)
    {
        abort_unless($request->hasValidSignature(), 403, 'This invite link is invalid or has expired.');
        abort_if($user->hasActivatedAccount(), 400, 'This account has already been activated — please log in.');

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password'     => Hash::make($validated['password']),
            'activated_at' => now(),
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Your account is set up — welcome!');
    }
}
