<?php

namespace App\Http\Controllers;

use App\Mail\LoginLinkMail;
use App\Models\LoginLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class LoginLinkController extends Controller
{
    // Show the "enter your email" request form
    public function showRequestForm()
    {
        return view('auth.login');
    }

    // Email a login link if the address matches an account. Always responds
    // the same way either way, so a guess can't be used to find out which
    // emails have accounts.
    public function sendLink(Request $request)
    {
        $validated = $request->validate(['email' => 'required|email']);

        $user = User::where('email', $validated['email'])->first();

        if ($user) {
            [, , $url] = LoginLink::issueFor($user);
            Mail::to($user->email)->send(new LoginLinkMail($user, $url));
        }

        return redirect()->route('login')
            ->with('status', "If an account exists for {$validated['email']}, we've emailed a login link.");
    }

    // Show a "click to finish signing in" interstitial WITHOUT consuming the
    // token — email clients and security scanners sometimes fetch links
    // automatically, which would otherwise burn a single-use token before a
    // real person ever sees the email.
    public function showConfirm(Request $request, string $token)
    {
        $user = LoginLink::peek($token);

        abort_unless($user, 404, 'This login link is invalid or has expired.');

        return view('auth.confirm-login', ['user' => $user]);
    }

    // The actual, single-use consumption — only reachable via the confirm
    // page's button, i.e. a deliberate action by a real person.
    public function consume(Request $request, string $token)
    {
        $user = LoginLink::consume($token);

        if (!$user) {
            return redirect()->route('login')
                ->withErrors(['email' => 'That login link is invalid, expired, or already used. Request a new one.']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
