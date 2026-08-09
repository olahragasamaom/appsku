<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ujian') - Portal Peserta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 min-h-screen">
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-5xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="{{ route('peserta.dashboard') }}" class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-primary-600 to-primary-800 flex items-center justify-center text-white font-bold text-lg shadow-sm">P</div>
                <span class="font-bold text-slate-800">Panritta<span class="text-primary-600">.</span></span>
            </a>
            @auth
                <div class="flex items-center gap-3">
                    <span class="text-sm text-slate-600 hidden sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('peserta.logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm text-slate-600">Logout</button>
                    </form>
                </div>
            @endauth
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 py-6">
        @if(session('success'))
            <x-alert type="success" class="mb-6" dismissible>{{ session('success') }}</x-alert>
        @endif
        @if(session('error'))
            <x-alert type="danger" class="mb-6" dismissible>{{ session('error') }}</x-alert>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
