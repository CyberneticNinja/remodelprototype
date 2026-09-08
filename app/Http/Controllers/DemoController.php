<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoController extends Controller
{
    // One click, no signup: logs the visitor straight into the shared
    // public demo contractor account. Guarded by the 'guest' middleware
    // (see routes/web.php) so a real logged-in user can't be switched into
    // it by mistake, and throttled since it creates an authenticated
    // session with no verification at all.
    public function login(Request $request)
    {
        Auth::login(User::demoContractor());
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
