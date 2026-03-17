@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto bg-white p-8 rounded shadow">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">New Project</h1>

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('projects.store') }}">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Project Title</label>
            <input type="text" name="title" value="{{ old('title') }}" required
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Project Address</label>
            <input type="text" name="address" value="{{ old('address') }}" required
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Client</label>
            @if($clients->isEmpty())
                <p class="text-sm text-red-500">No clients yet.
                    <a href="{{ route('clients.create') }}" class="underline">Create a client first.</a>
                </p>
            @else
                <select name="client_id" required
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Select a client --</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                            {{ $client->full_name }} — {{ $client->address }}
                        </option>
                    @endforeach
                </select>
            @endif
        </div>

        <div class="flex gap-3">
            <button type="submit"
                class="flex-1 bg-blue-600 text-white py-2 rounded hover:bg-blue-700 font-medium">
                Create Project
            </button>
            <a href="{{ route('dashboard') }}"
                class="flex-1 text-center border border-gray-300 py-2 rounded hover:bg-gray-100 text-gray-600">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
