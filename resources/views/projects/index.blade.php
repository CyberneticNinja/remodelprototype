@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Projects</h1>
    <a href="{{ route('projects.create') }}"
        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 font-medium">
        + New Project
    </a>
</div>

{{-- Search --}}
<form method="GET" action="{{ route('dashboard') }}" class="mb-6">
    <div class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
            placeholder="Search by project, address or client..."
            class="flex-1 border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="submit"
            class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800">
            Search
        </button>
        @if(request('search'))
            <a href="{{ route('dashboard') }}"
                class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100 text-gray-600">
                Clear
            </a>
        @endif
    </div>
</form>

{{-- Projects list --}}
@if($projects->isEmpty())
    <div class="bg-white rounded shadow p-8 text-center text-gray-500">
        No projects found.
        <a href="{{ route('projects.create') }}" class="text-blue-600 hover:underline ml-1">Create your first project.</a>
    </div>
@else
    <div class="grid gap-4">
        @foreach($projects as $project)
        <a href="{{ route('projects.show', $project) }}"
            class="bg-white rounded shadow p-6 hover:shadow-md transition flex justify-between items-center">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">{{ $project->title }}</h2>
                <p class="text-sm text-gray-500">{{ $project->address }}</p>
                <p class="text-sm text-gray-500">Client: {{ $project->client->full_name }}</p>
            </div>
            <div class="text-sm text-gray-400">
                {{ $project->rooms->count() }} room(s)
            </div>
        </a>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $projects->links() }}
    </div>
@endif
@endsection
