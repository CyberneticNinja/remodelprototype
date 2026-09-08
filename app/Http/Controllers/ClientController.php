<?php

namespace App\Http\Controllers;

use App\Mail\ClientInviteMail;
use App\Models\LoginLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ClientController extends Controller
{
    // List this contractor's clients
    public function index()
    {
        $clients = Auth::user()->clients()->orderBy('first_name')->get();

        return view('clients.index', compact('clients'));
    }

    // Show "add client" form
    public function create()
    {
        return view('clients.create');
    }

    // Store a new client and email them an activation link
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'required|string|max:20',
            'address'    => 'required|string|max:255',
        ]);

        $client = User::create([
            ...$validated,
            'type'                     => 'client',
            'created_by_contractor_id' => Auth::id(),
            'invited_at'               => now(),
        ]);

        $this->sendInvite($client);

        return redirect()->route('clients.index')
            ->with('success', "{$client->full_name} was added and invited to sign in online.");
    }

    // Contractor can resend the invite if the client hasn't activated yet
    public function resendInvite(User $client)
    {
        abort_if($client->created_by_contractor_id !== Auth::id(), 403);
        abort_if($client->hasActivatedAccount(), 400);

        $client->update(['invited_at' => now()]);

        $this->sendInvite($client);

        return back()->with('success', "Invite resent to {$client->full_name}.");
    }

    // The public demo lets a stranger type in ANY email address as a
    // "client" — we must never actually deliver mail to a real inbox from
    // it. So the demo contractor gets the link flashed to the screen
    // instead of a real send (see resources/views/layouts/app.blade.php).
    private function sendInvite(User $client): void
    {
        [, , $url] = LoginLink::issueFor($client, 'invite');

        if (Auth::user()->isDemoAccount()) {
            session()->flash('demo_invite_url', $url);
            return;
        }

        Mail::to($client->email)->send(new ClientInviteMail($client, $url));
    }
}
