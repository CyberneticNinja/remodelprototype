<?php

namespace App\Http\Controllers;

use App\Mail\LoginLinkMail;
use App\Models\LoginLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    // Show registration form — contractors are the only ones who self-register
    public function showRegister()
    {
        return view('auth.register');
    }

    // Handle registration. No password is collected — like every other
    // login in this app, the contractor proves they own the email address
    // by clicking the link we send them, rather than by having typed a
    // password at signup.
    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'phone'           => 'required|string|max:20',
            'company_name'    => 'required|string|max:255',
            'company_address' => 'required|string|max:255',
            'company_phone'   => 'required|string|max:20',
        ]);

        $contractor = User::create([
            ...$validated,
            'type' => 'contractor',
        ]);

        [, , $url] = LoginLink::issueFor($contractor);
        Mail::to($contractor->email)->send(new LoginLinkMail($contractor, $url));

        return redirect()->route('login')
            ->with('status', 'Account created — check your email for a link to sign in.');
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
