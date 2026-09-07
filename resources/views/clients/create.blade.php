@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto bg-white p-8 rounded shadow">
    <h1 class="text-2xl font-bold text-gray-800 mb-2">Add Client</h1>
    <p class="text-sm text-gray-500 mb-6">
        They'll be emailed a link to set up an account, so they can view progress and sign off online.
        If they never activate it, that's fine — you can still have them sign in person on your device.
    </p>

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('clients.store') }}">
        @csrf

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                <input type="text" name="first_name" value="{{ old('first_name') }}" required
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                <input type="text" name="last_name" value="{{ old('last_name') }}" required
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
            <input type="text" name="phone" value="{{ old('phone') }}" required
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
            <input type="text" name="address" value="{{ old('address') }}" required
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="flex gap-3">
            <button type="submit"
                class="flex-1 bg-blue-600 text-white py-2 rounded hover:bg-blue-700 font-medium">
                Add & Invite Client
            </button>
            <a href="{{ route('clients.index') }}"
                class="flex-1 text-center border border-gray-300 py-2 rounded hover:bg-gray-100 text-gray-600">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
