@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Clients</h1>
        <a href="{{ route('clients.create') }}"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 font-medium text-sm">
            + Add Client
        </a>
    </div>

    <div class="bg-white rounded shadow divide-y">
        @forelse($clients as $client)
            <div class="p-4 flex justify-between items-center">
                <div>
                    <p class="font-medium text-gray-800">{{ $client->full_name }}</p>
                    <p class="text-sm text-gray-500">{{ $client->email }} &middot; {{ $client->phone }}</p>
                    <p class="text-sm text-gray-400">{{ $client->address }}</p>
                </div>
                <div class="text-right">
                    @if($client->hasActivatedAccount())
                        <span class="text-xs text-green-600 font-medium">✓ Account active — can sign online</span>
                    @else
                        <span class="text-xs text-gray-400 block mb-1">Hasn't activated online account</span>
                        <form method="POST" action="{{ route('clients.resend-invite', $client) }}">
                            @csrf
                            <button class="text-xs text-blue-600 hover:underline">Resend invite</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <p class="p-6 text-gray-400 text-sm">No clients yet.</p>
        @endforelse
    </div>
</div>
@endsection
