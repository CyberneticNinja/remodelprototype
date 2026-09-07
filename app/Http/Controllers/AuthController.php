<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Show registration form — contractors are the only ones who self-register
    public function showRegister()
    {
        return view('auth.register');
    }

    // Handle registration
    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'password'        => 'required|string|min:8|confirmed',
            'phone'           => 'required|string|max:20',
            'company_name'    => 'required|string|max:255',
            'company_address' => 'required|string|max:255',
            'company_phone'   => 'required|string|max:20',
        ]);

        $contractor = User::create([
            ...$validated,
            'type'     => 'contractor',
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($contractor);

        return redirect()->route('dashboard');
    }

    // Show login form
    public function showLogin()
    {
        return view('auth.login');
    }

    // Handle login — shared by contractors and activated clients
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        // A client who hasn't activated their account yet has no password
        if ($user && $user->isClient() && !$user->hasActivatedAccount()) {
            return back()->withErrors([
                'email' => 'This account hasn\'t been activated yet. Check your email for the invite link.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'These credentials do not match our records.',
        ])->onlyInput('email');
    }

    // Handle logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
