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
    @if(auth()->user()->isDemoAccount())
        <div class="bg-amber-100 text-amber-900 text-sm text-center py-2 px-4">
            You're using the live demo — this account and its data are shared with other visitors and reset regularly.
        </div>
    @endif
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

        @if(session('demo_invite_url'))
            <div class="mb-4 p-4 bg-blue-50 border border-blue-200 text-blue-900 rounded">
                <p class="font-medium mb-1">Demo mode — no real email was sent.</p>
                <p class="text-sm mb-2">Here's the invite link that would have been emailed:</p>
                <a href="{{ session('demo_invite_url') }}" class="text-sm text-blue-700 underline break-all">{{ session('demo_invite_url') }}</a>
            </div>
        @endif

        @yield('content')
    </main>

</body>
</html>
