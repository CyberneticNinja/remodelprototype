@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="{{ route('projects.show', $project) }}" class="text-sm text-blue-600 hover:underline">← Back to {{ $project->title }}</a>
    <h1 class="text-2xl font-bold text-gray-800 mt-1">{{ $room->name }}</h1>
    <p class="text-gray-500 text-sm">{{ $project->address }}</p>
</div>

{{-- Notes --}}
@if($room->notes)
<div class="bg-white rounded shadow p-6 mb-6">
    <h2 class="font-semibold text-gray-700 mb-2">Notes</h2>
    <p class="text-gray-600 text-sm">{{ $room->notes }}</p>
</div>
@endif

{{-- Before Gallery --}}
<div class="bg-white rounded shadow p-6 mb-6">
    <h2 class="font-semibold text-gray-700 mb-4">Before Gallery</h2>
    <p class="text-sm text-gray-400">Photo upload coming soon.</p>
</div>

{{-- Work Agreed On --}}
<div class="bg-white rounded shadow p-6 mb-6">
    <h2 class="font-semibold text-gray-700 mb-1">Work Agreed On</h2>
    <p class="text-xs text-gray-500 mb-4">Both signatures required to mark this stage complete.</p>

    @php
        $waContractor = $room->signatures->where('stage','work_agreed')->where('role','contractor')->first();
        $waClient     = $room->signatures->where('stage','work_agreed')->where('role','client')->first();
    @endphp

    <div class="grid grid-cols-2 gap-4">
        <div class="border rounded p-4 {{ $waContractor ? 'border-green-400 bg-green-50' : 'border-gray-200' }}">
            <p class="text-sm font-medium text-gray-700 mb-2">Contractor Signature</p>
            @if($waContractor)
                <img src="{{ $waContractor->signature_data }}" class="max-h-16">
                <p class="text-xs text-green-600 mt-1">Signed {{ $waContractor->signed_at->format('M d, Y') }}</p>
            @else
                <a href="{{ route('signatures.create', [$project, $room, 'work_agreed', 'contractor']) }}"
                    class="inline-block mt-1 bg-blue-600 text-white text-xs px-3 py-1 rounded hover:bg-blue-700">
                    Sign Now
                </a>
            @endif
        </div>

        <div class="border rounded p-4 {{ $waClient ? 'border-green-400 bg-green-50' : 'border-gray-200' }}">
            <p class="text-sm font-medium text-gray-700 mb-2">Client Signature</p>
            @if($waClient)
                <img src="{{ $waClient->signature_data }}" class="max-h-16">
                <p class="text-xs text-green-600 mt-1">Signed {{ $waClient->signed_at->format('M d, Y') }}</p>
            @else
                <a href="{{ route('signatures.create', [$project, $room, 'work_agreed', 'client']) }}"
                    class="inline-block mt-1 bg-gray-700 text-white text-xs px-3 py-1 rounded hover:bg-gray-800">
                    Hand to Client
                </a>
            @endif
        </div>
    </div>

    @if($room->work_agreed_complete)
        <p class="mt-3 text-sm text-green-600 font-medium">✓ Both parties have agreed on the work.</p>
    @endif
</div>

{{-- Completed --}}
<div class="bg-white rounded shadow p-6 mb-6">
    <h2 class="font-semibold text-gray-700 mb-1">Completed</h2>
    <p class="text-xs text-gray-500 mb-4">Both signatures required to mark this room complete.</p>

    {{-- After Gallery --}}
    <div class="mb-4">
        <h3 class="text-sm font-medium text-gray-600 mb-2">After Gallery</h3>
        <p class="text-sm text-gray-400">Photo upload coming soon.</p>
    </div>

    @php
        $cContractor = $room->signatures->where('stage','completed')->where('role','contractor')->first();
        $cClient     = $room->signatures->where('stage','completed')->where('role','client')->first();
    @endphp

    <div class="grid grid-cols-2 gap-4">
        <div class="border rounded p-4 {{ $cContractor ? 'border-green-400 bg-green-50' : 'border-gray-200' }}">
            <p class="text-sm font-medium text-gray-700 mb-2">Contractor Signature</p>
            @if($cContractor)
                <img src="{{ $cContractor->signature_data }}" class="max-h-16">
                <p class="text-xs text-green-600 mt-1">Signed {{ $cContractor->signed_at->format('M d, Y') }}</p>
            @else
                <a href="{{ route('signatures.create', [$project, $room, 'completed', 'contractor']) }}"
                    class="inline-block mt-1 bg-blue-600 text-white text-xs px-3 py-1 rounded hover:bg-blue-700">
                    Sign Now
                </a>
            @endif
        </div>

        <div class="border rounded p-4 {{ $cClient ? 'border-green-400 bg-green-50' : 'border-gray-200' }}">
            <p class="text-sm font-medium text-gray-700 mb-2">Client Signature</p>
            @if($cClient)
                <img src="{{ $cClient->signature_data }}" class="max-h-16">
                <p class="text-xs text-green-600 mt-1">Signed {{ $cClient->signed_at->format('M d, Y') }}</p>
            @else
                <a href="{{ route('signatures.create', [$project, $room, 'completed', 'client']) }}"
                    class="inline-block mt-1 bg-gray-700 text-white text-xs px-3 py-1 rounded hover:bg-gray-800">
                    Hand to Client
                </a>
            @endif
        </div>
    </div>

    @if($room->is_complete)
        <p class="mt-3 text-sm text-green-600 font-medium">✓ Room is fully completed and signed off.</p>
    @endif
</div>
@endsection
