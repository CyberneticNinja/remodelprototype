<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Remodel Pro') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    @auth
    <nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
        <div class="flex items-center gap-6">
            <a href="{{ route('dashboard') }}" class="text-xl font-bold text-gray-800">🏠 Remodel Pro</a>
            @if(auth()->user()->isContractor())
                <a href="{{ route('clients.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Clients</a>
            @endif
        </div>
        <div class="flex items-center gap-4">
            <span class="text-gray-600">
                {{ auth()->user()->full_name }}
                <span class="text-xs text-gray-400">({{ ucfirst(auth()->user()->type) }})</span>
            </span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-red-500 hover:underline">Logout</button>
            </form>
        </div>
    </nav>
    @endauth

    <main class="container mx-auto px-4 py-8">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>

</body>
</html>
