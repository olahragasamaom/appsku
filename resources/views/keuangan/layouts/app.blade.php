<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - Keuangan</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

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
        .sidebar-link:hover { background-color: #1e293b; color: #fff; }
        .sidebar-link.active { background-color: #16a34a; color: #fff; }
        .sidebar-icon { width: 20px; height: 20px; flex-shrink: 0; }
        .sidebar-section {
            font-size: 12px; font-weight: 600; color: #64748b;
            text-transform: uppercase; padding: 12px 16px; margin-top: 16px;
        }
    </style>
</head>
<body class="font-sans antialiased bg-slate-50">
    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex">

        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-secondary-900/50 z-40 lg:hidden" x-cloak></div>

        {{-- Sidebar --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 transform transition-all duration-300 ease-in-out lg:static lg:inset-auto flex flex-col">

            <div style="height: 64px; display: flex; align-items: center; justify-content: space-between; padding: 0 16px; border-bottom: 1px solid #334155;">
                <a href="{{ route('keuangan.dashboard') }}" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg style="width: 24px; height: 24px;" fill="none" stroke="white" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span style="font-size: 18px; font-weight: 700; color: #ffffff;">Keuangan</span>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden" style="color: #94a3b8;">
                    <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">

                <a href="{{ route('keuangan.dashboard') }}" class="sidebar-link {{ request()->routeIs('keuangan.dashboard') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-10"/>
                    </svg>
                    <span class="flex-1">Home</span>
                </a>

                <div class="sidebar-section">Keuangan & Peserta</div>

                <a href="{{ route('keuangan.peserta.index') }}" class="sidebar-link {{ request()->routeIs('keuangan.peserta.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM9 20H4v-2a6 6 0 0112 0v2H9z"/>
                    </svg>
                    <span class="flex-1">Manajemen Peserta</span>
                </a>

                <a href="{{ route('keuangan.paket.index') }}" class="sidebar-link {{ request()->routeIs('keuangan.paket.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span class="flex-1">Manajemen Paket</span>
                </a>

                <a href="{{ route('keuangan.pembayaran.index') }}" class="sidebar-link {{ request()->routeIs('keuangan.pembayaran.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <span class="flex-1">Verifikasi Pembayaran</span>
                </a>

                <div class="sidebar-section">Pengaturan</div>

                <a href="{{ route('keuangan.change-password.index') }}" class="sidebar-link {{ request()->routeIs('keuangan.change-password.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span class="flex-1">Ubah Password</span>
                </a>

            </nav>

            <div style="padding: 16px; border-top: 1px solid #334155;">
                <form action="{{ route('keuangan.logout') }}" method="POST" style="margin: 0;">
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

        <div class="flex-1 flex flex-col min-h-screen">

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

            <main class="flex-1 overflow-auto">
                @yield('content')
            </main>

        </div>

    </div>

    @stack('scripts')
</body>
</html>
