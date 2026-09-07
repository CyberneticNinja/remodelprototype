@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto bg-white p-8 rounded shadow">

    <a href="{{ route('rooms.show', [$project, $room]) }}" class="text-sm text-blue-600 hover:underline">← Back to {{ $room->name }}</a>

    <h1 class="text-2xl font-bold text-gray-800 mt-2 mb-1">
        {{ $role === 'contractor' ? 'Contractor' : 'Your' }} Signature
    </h1>
    <p class="text-sm text-gray-500 mb-1">
        Stage: <span class="font-medium">{{ $stage === 'work_agreed' ? 'Work Agreed On' : 'Completed' }}</span>
    </p>
    <p class="text-sm text-gray-500 mb-6">
        Room: <span class="font-medium">{{ $room->name }}</span> — {{ $project->title }}
    </p>

    <p class="text-sm text-gray-600 mb-2">Sign in the box below:</p>

    <form method="POST" action="{{ route('signatures.store', [$project, $room, $stage, $role]) }}" id="signatureForm">
        @csrf
        <input type="hidden" name="signature_data" id="signatureData">

        @include('signatures._canvas')

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="flex gap-3">
            <button type="submit" id="submitBtn"
                class="flex-1 bg-blue-600 text-white py-2 rounded hover:bg-blue-700 font-medium">
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
