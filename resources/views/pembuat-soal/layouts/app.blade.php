<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - Pembuat Soal</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            border-radius: 8px;
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.2s;
            font-size: 14px;
        }
        .sidebar-link:hover {
            background-color: #1e293b;
            color: #fff;
        }
        .sidebar-link.active {
            background-color: #3b82f6;
            color: #fff;
        }
        .sidebar-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
        .sidebar-section {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            padding: 12px 16px;
            margin-top: 16px;
        }
    </style>
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
                <a href="{{ route('pembuat-soal.dashboard') }}" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg style="width: 24px; height: 24px;" fill="none" stroke="white" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C6.5 6.253 2 10.998 2 17s4.5 10.747 10 10.747c5.5 0 10-4.998 10-10.747 0-6.002-4.5-10.747-10-10.747z"/>
                        </svg>
                    </div>
                    <span style="font-size: 18px; font-weight: 700; color: #ffffff;">Pembuat Soal</span>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden" style="color: #94a3b8;">
                    <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Navigation Menu --}}
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">

                {{-- Dashboard --}}
                <a href="{{ route('pembuat-soal.dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('pembuat-soal.dashboard') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-10"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 21h6v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4"/>
                    </svg>
                    <span class="flex-1">Home</span>
                </a>

                {{-- Section: Ujian & Peserta --}}
                <div class="sidebar-section">Ujian & Peserta</div>

                {{-- Jenis Ujian --}}
                <a href="{{ route('pembuat-soal.ujian.index') }}"
                   class="sidebar-link {{ request()->routeIs('pembuat-soal.ujian.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span class="flex-1">Ujian</span>
                </a>

                {{-- Bank Soal --}}
                <a href="{{ route('pembuat-soal.soal.index') }}"
                   class="sidebar-link {{ request()->routeIs('pembuat-soal.soal.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C6.5 6.253 2 10.998 2 17s4.5 10.747 10 10.747c5.5 0 10-4.998 10-10.747 0-6.002-4.5-10.747-10-10.747z"/>
                    </svg>
                    <span class="flex-1">Bank Soal</span>
                </a>

                {{-- Section: Pengaturan --}}
                <div class="sidebar-section">Pengaturan</div>

                {{-- Ubah Password --}}
                <a href="{{ route('pembuat-soal.change-password.index') }}"
                   class="sidebar-link {{ request()->routeIs('pembuat-soal.change-password.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span class="flex-1">Ubah Password</span>
                </a>

            </nav>

            {{-- Footer --}}
            <div style="padding: 16px; border-top: 1px solid #334155;">
                <form action="{{ route('pembuat-soal.logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="sidebar-link w-full hover:bg-danger-600/20 hover:text-danger-300">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="flex-1 text-left">Logout</span>
                    </button>
                </form>
            </div>

        </aside>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col min-h-screen">

            {{-- Top Navigation Bar --}}
            <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
                <div class="flex items-center justify-between h-16 px-6">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h1 class="text-lg font-semibold text-slate-900">@yield('title', 'Dashboard')</h1>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-slate-600">{{ auth()->user()->name }}</span>
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 overflow-auto">
                @yield('content')
            </main>

        </div>

    </div>

    @stack('scripts')
</body>
</html>
