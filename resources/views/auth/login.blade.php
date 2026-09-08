@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto mt-20 bg-white p-8 rounded shadow">
    <h1 class="text-2xl font-bold text-gray-800 mb-2">Log In</h1>
    <p class="text-sm text-gray-500 mb-6">No password needed — we'll email you a link to sign in.</p>

    @if(session('status'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.send') }}">
        @csrf

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <button type="submit"
            class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 font-medium">
            Email me a login link
        </button>
    </form>

    <p class="mt-4 text-sm text-center text-gray-600">
        Don't have an account?
        <a href="{{ route('register') }}" class="text-blue-600 hover:underline">Register</a>
    </p>

    <div class="mt-6 pt-6 border-t border-gray-200 text-center">
        <form method="POST" action="{{ route('demo.login') }}">
            @csrf
            <button type="submit" class="text-sm text-gray-500 hover:text-gray-800 underline">
                Or try the live demo — no signup needed
            </button>
        </form>
    </div>
</div>
@endsection
