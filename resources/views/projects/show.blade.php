@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <a href="{{ route('dashboard') }}" class="text-sm text-blue-600 hover:underline">← Back to Projects</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-1">{{ $project->title }}</h1>
        <p class="text-gray-500 text-sm">{{ $project->address }}</p>
        <p class="text-gray-500 text-sm">Client: {{ $project->client->full_name }} — {{ $project->client->phone }}</p>
    </div>
    <a href="{{ route('rooms.create', $project) }}"
        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 font-medium">
        + Add Room
    </a>
</div>

@if($project->rooms->isEmpty())
    <div class="bg-white rounded shadow p-8 text-center text-gray-500">
        No rooms yet. Add your first room to get started.
    </div>
@else
    <div class="grid gap-6">
        @foreach($project->rooms as $room)
        <div class="bg-white rounded shadow p-6">

            {{-- Room header --}}
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-800">{{ $room->name }}</h2>
                <a href="{{ route('rooms.show', [$project, $room]) }}"
                    class="text-sm text-blue-600 hover:underline">View Room →</a>
            </div>

            {{-- Notes --}}
            @if($room->notes)
                <p class="text-sm text-gray-600 mb-4">{{ $room->notes }}</p>
            @endif

            {{-- Stage status --}}
            <div class="grid grid-cols-2 gap-4">

                {{-- Work Agreed --}}
                <div class="border rounded p-4 {{ $room->work_agreed_complete ? 'border-green-400 bg-green-50' : 'border-gray-200' }}">
                    <p class="text-sm font-medium text-gray-700 mb-2">Work Agreed On</p>
                    <div class="flex gap-4 text-xs">
                        @php
                            $waContractor = $room->signatures->where('stage','work_agreed')->where('role','contractor')->first();
                            $waClient     = $room->signatures->where('stage','work_agreed')->where('role','client')->first();
                        @endphp
                        <span class="{{ $waContractor ? 'text-green-600' : 'text-gray-400' }}">
                            {{ $waContractor ? '✓' : '○' }} Contractor
                        </span>
                        <span class="{{ $waClient ? 'text-green-600' : 'text-gray-400' }}">
                            {{ $waClient ? '✓' : '○' }} Client
                        </span>
                    </div>
                    @if($room->work_agreed_complete)
                        <p class="text-xs text-green-600 mt-1 font-medium">Complete</p>
                    @else
                        <p class="text-xs text-yellow-600 mt-1">Pending signatures</p>
                    @endif
                </div>

                {{-- Completed --}}
                <div class="border rounded p-4 {{ $room->is_complete ? 'border-green-400 bg-green-50' : 'border-gray-200' }}">
                    <p class="text-sm font-medium text-gray-700 mb-2">Completed</p>
                    <div class="flex gap-4 text-xs">
                        @php
                            $cContractor = $room->signatures->where('stage','completed')->where('role','contractor')->first();
                            $cClient     = $room->signatures->where('stage','completed')->where('role','client')->first();
                        @endphp
                        <span class="{{ $cContractor ? 'text-green-600' : 'text-gray-400' }}">
                            {{ $cContractor ? '✓' : '○' }} Contractor
                        </span>
                        <span class="{{ $cClient ? 'text-green-600' : 'text-gray-400' }}">
                            {{ $cClient ? '✓' : '○' }} Client
                        </span>
                    </div>
                    @if($room->is_complete)
                        <p class="text-xs text-green-600 mt-1 font-medium">Complete</p>
                    @else
                        <p class="text-xs text-yellow-600 mt-1">Pending signatures</p>
                    @endif
                </div>

            </div>

            {{-- Photo counts --}}
            <div class="mt-4 flex gap-4 text-xs text-gray-500">
                <span>📷 Before: {{ $room->beforePhotos->count() }} photo(s)</span>
                <span>📷 After: {{ $room->afterPhotos->count() }} photo(s)</span>
            </div>

        </div>
        @endforeach
    </div>
@endif
@endsection
