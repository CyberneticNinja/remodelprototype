@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto bg-white p-8 rounded shadow">
    <a href="{{ route('rooms.show', [$project, $room]) }}" class="text-sm text-blue-600 hover:underline">← Back to {{ $room->name }}</a>

    <h1 class="text-2xl font-bold text-gray-800 mt-2 mb-6">Edit {{ $room->name }}</h1>

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('rooms.update', [$project, $room]) }}">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea name="notes" rows="3"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes', $room->notes) }}</textarea>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Scope of Work</label>
            <textarea name="scope_description" rows="4"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('scope_description', $room->scope_description) }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estimated Cost ($)</label>
                <input type="number" step="0.01" min="0" name="estimated_cost" value="{{ old('estimated_cost', $room->estimated_cost) }}"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estimated Duration (days)</label>
                <input type="number" min="0" name="estimated_duration_days" value="{{ old('estimated_duration_days', $room->estimated_duration_days) }}"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                class="flex-1 bg-blue-600 text-white py-2 rounded hover:bg-blue-700 font-medium">
                Save Changes
            </button>
            <a href="{{ route('rooms.show', [$project, $room]) }}"
                class="flex-1 text-center border border-gray-300 py-2 rounded hover:bg-gray-100 text-gray-600">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
