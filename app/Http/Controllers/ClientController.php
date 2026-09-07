<?php

namespace App\Http\Controllers;

use App\Mail\ClientInviteMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

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

        $activationUrl = URL::temporarySignedRoute(
            'clients.activate',
            now()->addDays(7),
            ['user' => $client->id]
        );

        Mail::to($client->email)->send(new ClientInviteMail($client, $activationUrl));

        return redirect()->route('clients.index')
            ->with('success', "{$client->full_name} was added and invited to sign in online.");
    }

    // Contractor can resend the invite if the client hasn't activated yet
    public function resendInvite(User $client)
    {
        abort_if($client->created_by_contractor_id !== Auth::id(), 403);
        abort_if($client->hasActivatedAccount(), 400);

        $client->update(['invited_at' => now()]);

        $activationUrl = URL::temporarySignedRoute(
            'clients.activate',
            now()->addDays(7),
            ['user' => $client->id]
        );

        Mail::to($client->email)->send(new ClientInviteMail($client, $activationUrl));

        return back()->with('success', "Invite resent to {$client->full_name}.");
    }
}
