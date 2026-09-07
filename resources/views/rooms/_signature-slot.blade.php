@php
    $label = $role === 'contractor' ? 'Contractor Signature' : 'Client Signature';
    $stageLocked = $stage === 'completed' && !$room->work_agreed_complete;
@endphp
<div class="border rounded p-4 {{ $signature ? 'border-green-400 bg-green-50' : 'border-gray-200' }}">
    <p class="text-sm font-medium text-gray-700 mb-2">{{ $label }}</p>

    @if($signature)
        <img src="{{ $signature->signature_data }}" class="max-h-16">
        <p class="text-xs text-green-600 mt-1">
            Signed {{ $signature->signed_at->format('M d, Y') }}
            <span class="text-gray-400">&middot; {{ $signature->method === 'online' ? 'Signed online' : 'Signed in person' }}</span>
        </p>
    @elseif($stageLocked)
        <p class="text-xs text-gray-400">🔒 Available once both parties sign Work Agreed On</p>
    @else
        @if($role === 'contractor')
            @if($isContractor)
                <a href="{{ route('signatures.create', [$project, $room, $stage, 'contractor']) }}"
                    class="inline-block mt-1 bg-blue-600 text-white text-xs px-3 py-1 rounded hover:bg-blue-700">
                    Sign Now
                </a>
            @else
                <p class="text-xs text-gray-400">Waiting on contractor</p>
            @endif
        @else
            @if($isThisClient)
                <a href="{{ route('signatures.create', [$project, $room, $stage, 'client']) }}"
                    class="inline-block mt-1 bg-blue-600 text-white text-xs px-3 py-1 rounded hover:bg-blue-700">
                    Sign Now
                </a>
            @elseif($isContractor)
                <div class="flex flex-col gap-1 items-start">
                    <a href="{{ route('signatures.person.create', [$project, $room, $stage]) }}"
                        class="inline-block bg-gray-700 text-white text-xs px-3 py-1 rounded hover:bg-gray-800">
                        Client Signs Now (In Person)
                    </a>
                    @if($project->client->hasActivatedAccount())
                        <span class="text-xs text-gray-400">Or the client can sign online anytime from their own account.</span>
                    @else
                        <span class="text-xs text-gray-400">Client hasn't activated an online account yet.</span>
                    @endif
                </div>
            @else
                <p class="text-xs text-gray-400">Waiting on client</p>
            @endif
        @endif
    @endif
</div>
