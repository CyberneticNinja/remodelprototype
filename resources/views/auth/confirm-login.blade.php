@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto mt-20 bg-white p-8 rounded shadow text-center">
    <h1 class="text-2xl font-bold text-gray-800 mb-2">Hi, {{ $user->first_name }}</h1>
    <p class="text-sm text-gray-500 mb-6">Click below to finish signing in as {{ $user->email }}.</p>

    <form method="POST" action="{{ url()->full() }}">
        @csrf
        <button type="submit"
            class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 font-medium">
            Finish signing in
        </button>
    </form>
</div>
@endsection
