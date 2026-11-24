<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - Pengawas</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50">
    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex">

        {{-- Sidebar Overlay (Mobile) --}}
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-secondary-900/50 z-40 lg:hidden"
             x-cloak>
        </div>

        {{-- Sidebar --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 transform transition-all duration-300 ease-in-out lg:static lg:inset-auto flex flex-col">

            {{-- Logo --}}
            <div style="height: 64px; display: flex; align-items: center; justify-content: space-between; padding: 0 16px; border-bottom: 1px solid #334155;">
                <a href="{{ route('pengawas.dashboard') }}" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg style="width: 24px; height: 24px;" fill="none" stroke="white" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span style="font-size: 18px; font-weight: 700; color: white;">Pengawas</span>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden" style="color: #94a3b8;">
                    <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Navigation Menu --}}
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">

                {{-- Dashboard --}}
                <a href="{{ route('pengawas.dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('pengawas.dashboard') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-10"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 21h6v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4"/>
                    </svg>
                    <span class="flex-1">Home</span>
                </a>

                {{-- Absensi --}}
                <a href="{{ route('pengawas.absensi.index') }}"
                   class="sidebar-link {{ request()->routeIs('pengawas.absensi.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span class="flex-1">Absensi</span>
                </a>

                {{-- Hasil Ujian --}}
                <a href="{{ route('pengawas.hasil-ujian.index') }}"
                   class="sidebar-link {{ request()->routeIs('pengawas.hasil-ujian.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span class="flex-1">Hasil Ujian</span>
                </a>

            </nav>

            {{-- User Info + Logout --}}
            <div class="p-3 border-t border-slate-700">
                <div class="flex items-center gap-3 px-3 py-2">
                    <div class="w-10 h-10 bg-amber-500 rounded-lg flex items-center justify-center text-white text-sm font-semibold">
                        {{ substr(auth()->user()->name ?? 'P', 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name ?? 'Pengawas' }}</p>
                        <p class="text-xs text-slate-400 truncate">Pengawas</p>
                    </div>
                </div>
                <button @click="$refs.logoutForm.submit()"
                        class="w-full mt-3 px-3 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition text-left">
                    Logout
                </button>
                <form method="POST" action="{{ route('pengawas.logout') }}" x-ref="logoutForm" style="display: none;">
                    @csrf
                </form>
            </div>
        </aside>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col lg:ml-0">
            {{-- Top Nav --}}
            <header class="bg-white border-b border-secondary-200 sticky top-0 z-30">
                <div class="px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between gap-4">
                    <button @click="sidebarOpen = true" class="lg:hidden text-secondary-600 hover:text-secondary-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div class="flex-1"></div>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('pengawas.change-password.index') }}" class="text-secondary-600 hover:text-secondary-900 text-sm font-medium">
                            Ganti Password
                        </a>
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 overflow-y-auto">
                <div class="px-4 sm:px-6 lg:px-8 py-8">
                    @if ($errors->any())
                        <div class="mb-6">
                            <div class="bg-danger-50 border border-danger-200 rounded-lg p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-danger-800">Ada kesalahan</h3>
                                        <ul class="mt-2 list-disc list-inside text-sm text-danger-700">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="mb-6">
                            <div class="bg-success-50 border border-success-200 rounded-lg p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-success-800">{{ session('success') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>
</body>
</html>
