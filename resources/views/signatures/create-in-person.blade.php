@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto bg-white p-8 rounded shadow">

    <a href="{{ route('rooms.show', [$project, $room]) }}" class="text-sm text-blue-600 hover:underline">← Back to {{ $room->name }}</a>

    <h1 class="text-2xl font-bold text-gray-800 mt-2 mb-1">Client Signature (In Person)</h1>
    <p class="text-sm text-gray-500 mb-1">
        Stage: <span class="font-medium">{{ $stage === 'work_agreed' ? 'Work Agreed On' : 'Completed' }}</span>
    </p>
    <p class="text-sm text-gray-500 mb-6">
        Room: <span class="font-medium">{{ $room->name }}</span> — {{ $project->title }}
    </p>

    <div class="mb-4 p-3 bg-yellow-50 border border-yellow-300 rounded text-sm text-yellow-800">
        ⚠️ Please hand the device to <strong>{{ $project->client->full_name }}</strong> to confirm and sign below.
    </div>

    @if($errors->any())
        <div class="mb-4 p-3 bg-red-100 text-red-800 rounded text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('signatures.person.store', [$project, $room, $stage]) }}" id="signatureForm">
        @csrf
        <input type="hidden" name="signature_data" id="signatureData">

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Type your full name to confirm it's you: <span class="text-gray-400">({{ $project->client->full_name }})</span>
            </label>
            <input type="text" name="signer_name_confirmation" value="{{ old('signer_name_confirmation') }}" required
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <p class="text-sm text-gray-600 mb-2">Sign in the box below:</p>

        @include('signatures._canvas')

        <div class="flex gap-3">
            <button type="submit" id="submitBtn"
                class="flex-1 bg-gray-700 text-white py-2 rounded hover:bg-gray-800 font-medium">
                Confirm & Save Signature
            </button>
            <a href="{{ route('rooms.show', [$project, $room]) }}"
                class="flex-1 text-center border border-gray-300 py-2 rounded hover:bg-gray-100 text-gray-600">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
