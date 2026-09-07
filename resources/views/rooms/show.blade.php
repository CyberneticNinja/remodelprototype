@extends('layouts.app')

@section('content')
@php
    $me = auth()->user();
    $isContractor = $me->isContractor();
    $isThisClient = $me->isClient() && $project->client_id === $me->id;

    $beforeCanUpload = $isContractor && $room->canUploadBeforePhotos();
    $beforeLockedMessage = $isContractor && !$beforeCanUpload
        ? 'Before photos are locked once work has been agreed on.'
        : null;

    $afterCanUpload = $isContractor && $room->canUploadAfterPhotos();
    $afterLockedMessage = null;
    if ($isContractor && !$afterCanUpload) {
        $afterLockedMessage = !$room->work_agreed_complete
            ? 'Available once both parties sign Work Agreed On.'
            : 'Locked — the room is complete.';
    }
@endphp

<div class="mb-6 flex justify-between items-start">
    <div>
        <a href="{{ route('projects.show', $project) }}" class="text-sm text-blue-600 hover:underline">← Back to {{ $project->title }}</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-1">{{ $room->name }}</h1>
        <p class="text-gray-500 text-sm">{{ $project->address }}</p>
    </div>
    @if($isContractor && !$room->isEstimateLocked())
        <a href="{{ route('rooms.edit', [$project, $room]) }}" class="text-sm text-blue-600 hover:underline">Edit estimate</a>
    @endif
</div>

{{-- Notes --}}
@if($room->notes)
<div class="bg-white rounded shadow p-6 mb-6">
    <h2 class="font-semibold text-gray-700 mb-2">Notes</h2>
    <p class="text-gray-600 text-sm">{{ $room->notes }}</p>
</div>
@endif

{{-- Scope / Estimate --}}
<div class="bg-white rounded shadow p-6 mb-6">
    <div class="flex justify-between items-start mb-2">
        <h2 class="font-semibold text-gray-700">Scope & Estimate</h2>
        @if($room->isEstimateLocked())
            <span class="text-xs text-gray-400">🔒 Locked — a signature has been collected</span>
        @elseif($isContractor)
            <span class="text-xs text-gray-400">Editable until the first signature</span>
        @endif
    </div>
    @if($room->scope_description)
        <p class="text-gray-600 text-sm mb-3">{{ $room->scope_description }}</p>
    @else
        <p class="text-gray-400 text-sm mb-3 italic">No scope of work has been described yet.</p>
    @endif
    <div class="flex gap-6 text-sm text-gray-700">
        <span>💰 {{ $room->estimated_cost ? '$'.number_format($room->estimated_cost, 2) : '—' }}</span>
        <span>🗓️ {{ $room->estimated_duration_days ? $room->estimated_duration_days.' day(s)' : '—' }}</span>
    </div>
</div>

{{-- Before Gallery --}}
<div class="bg-white rounded shadow p-6 mb-6">
    <h2 class="font-semibold text-gray-700 mb-4">Before Gallery</h2>
    @include('rooms._gallery', [
        'photos' => $room->beforePhotos,
        'type' => 'before',
        'canUpload' => $beforeCanUpload,
        'lockedMessage' => $beforeLockedMessage,
    ])
</div>

{{-- Work Agreed On --}}
<div class="bg-white rounded shadow p-6 mb-6">
    <h2 class="font-semibold text-gray-700 mb-1">Work Agreed On</h2>
    <p class="text-xs text-gray-500 mb-4">Both signatures are required before work begins.</p>

    @php
        $waContractor = $room->signatures->where('stage','work_agreed')->where('role','contractor')->first();
        $waClient     = $room->signatures->where('stage','work_agreed')->where('role','client')->first();
    @endphp

    <div class="grid grid-cols-2 gap-4">
        @include('rooms._signature-slot', ['signature' => $waContractor, 'role' => 'contractor', 'stage' => 'work_agreed'])
        @include('rooms._signature-slot', ['signature' => $waClient, 'role' => 'client', 'stage' => 'work_agreed'])
    </div>

    @if($room->work_agreed_complete)
        <p class="mt-3 text-sm text-green-600 font-medium">✓ Both parties have agreed on the work. Work may begin.</p>
    @endif
</div>

{{-- Completed --}}
<div class="bg-white rounded shadow p-6 mb-6">
    <h2 class="font-semibold text-gray-700 mb-1">Completed</h2>
    <p class="text-xs text-gray-500 mb-4">
        @if(!$room->work_agreed_complete)
            🔒 Locked until Work Agreed On is signed by both parties.
        @else
            Both signatures required to mark this room complete.
        @endif
    </p>

    <div class="mb-4">
        <h3 class="text-sm font-medium text-gray-600 mb-2">After Gallery</h3>
        @include('rooms._gallery', [
            'photos' => $room->afterPhotos,
            'type' => 'after',
            'canUpload' => $afterCanUpload,
            'lockedMessage' => $afterLockedMessage,
        ])
    </div>

    @php
        $cContractor = $room->signatures->where('stage','completed')->where('role','contractor')->first();
        $cClient     = $room->signatures->where('stage','completed')->where('role','client')->first();
    @endphp

    <div class="grid grid-cols-2 gap-4">
        @include('rooms._signature-slot', ['signature' => $cContractor, 'role' => 'contractor', 'stage' => 'completed'])
        @include('rooms._signature-slot', ['signature' => $cClient, 'role' => 'client', 'stage' => 'completed'])
    </div>

    @if($room->is_complete)
        <p class="mt-3 text-sm text-green-600 font-medium">✓ Room is fully completed and signed off.</p>
    @endif
</div>
@endsection
