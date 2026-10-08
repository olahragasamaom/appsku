<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - Panritta Superadmin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .superadmin-logo-text {
            font-size: 18px !important;
            font-weight: 700 !important;
            color: #ffffff !important;
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
                <a href="{{ route('superadmin.dashboard') }}" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg style="width: 24px; height: 24px;" fill="none" stroke="white" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <span class="superadmin-logo-text">Superadmin</span>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden" style="color: #94a3b8;">
                    <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Navigation Menu Tree --}}
            @php
                $sidebarUser = auth()->user();

                $moduleBadges = [
                    'security-logs' => ['count' => (function () {
                        try {
                            return \Illuminate\Support\Facades\Schema::hasTable('security_logs')
                                ? \App\Models\SecurityLog::recent(24)->where('severity', 'critical')->count() : 0;
                        } catch (\Throwable $e) { return 0; }
                    })(), 'class' => 'bg-danger-500'],
                    'security-blocked-ips' => ['count' => (function () {
                        try {
                            return \Illuminate\Support\Facades\Schema::hasTable('blocked_ips')
                                ? \App\Models\BlockedIp::active()->count() : 0;
                        } catch (\Throwable $e) { return 0; }
                    })(), 'class' => 'bg-secondary-500'],
                ];
            @endphp

            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">

                {{-- Dashboard --}}
                <a href="{{ route('superadmin.dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-10"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 21h6v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4"/>
                    </svg>
                    <span class="flex-1">Home</span>
                </a>

                {{-- Jenis Ujian (Collapsible Tree) --}}
                <div class="!mt-6 !mb-3 px-3">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Ujian & Peserta</span>
                </div>

                <div x-data="{ jenisUjianOpen: {{ request()->routeIs('superadmin.jenis-ujian.*', 'superadmin.sub-jenis-ujian.*', 'superadmin.sub-indikator.*') ? 'true' : 'false' }} }">
                    <button type="button"
                            @click="jenisUjianOpen = !jenisUjianOpen"
                            class="sidebar-link {{ request()->routeIs('superadmin.jenis-ujian.*', 'superadmin.sub-jenis-ujian.*', 'superadmin.sub-indikator.*') ? 'active' : '' }}"
                            style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                        <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                            <svg class="sidebar-icon flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span class="truncate">Jenis Ujian</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform flex-shrink-0 ml-2"
                             :class="jenisUjianOpen ? 'rotate-180' : ''"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="jenisUjianOpen"
                         x-collapse
                         style="margin-left: 1.5rem; padding-left: 0.75rem; border-left: 1px solid #334155;">
                        <a href="{{ route('superadmin.jenis-ujian.index') }}"
                           class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('superadmin.jenis-ujian.*') ? 'font-bold text-white' : 'font-normal text-slate-400 hover:text-slate-200' }}">
                            Jenis Ujian
                        </a>
                        <a href="{{ route('superadmin.sub-jenis-ujian.index') }}"
                           class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('superadmin.sub-jenis-ujian.*') ? 'font-bold text-white' : 'font-normal text-slate-400 hover:text-slate-200' }}">
                            Sub Jenis Ujian
                        </a>
                        <a href="{{ route('superadmin.sub-indikator.index') }}"
                           class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('superadmin.sub-indikator.*') ? 'font-bold text-white' : 'font-normal text-slate-400 hover:text-slate-200' }}">
                            Sub Indikator
                        </a>
                    </div>
                </div>

                {{-- Bank Soal --}}
                <a href="{{ route('superadmin.soal.index') }}"
                   class="sidebar-link {{ request()->routeIs('superadmin.soal.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                    <span class="flex-1">Bank Soal</span>
                </a>

                {{-- Ujian --}}
                <a href="{{ route('superadmin.ujian.index') }}"
                   class="sidebar-link {{ request()->routeIs('superadmin.ujian.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="flex-1">Ujian</span>
                </a>

                {{-- Absensi --}}
                <a href="{{ route('superadmin.absensi.index') }}"
                   class="sidebar-link {{ request()->routeIs('superadmin.absensi.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span class="flex-1">Absensi</span>
                </a>

                {{-- Manajemen Peserta --}}
                <a href="{{ route('superadmin.peserta.index') }}"
                   class="sidebar-link {{ request()->routeIs('superadmin.peserta.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12H9m6 0a6 6 0 11-12 0 6 6 0 0112 0z"/>
                    </svg>
                    <span class="flex-1">Manajemen Peserta</span>
                </a>

                {{-- Manajemen Paket (Collapsible Tree) --}}
                <div x-data="{ paketOpen: {{ request()->routeIs('superadmin.paket.*', 'superadmin.subscriptions.*') ? 'true' : 'false' }} }">
                    <button type="button"
                            @click="paketOpen = !paketOpen"
                            class="sidebar-link {{ request()->routeIs('superadmin.paket.*', 'superadmin.subscriptions.*') ? 'active' : '' }}"
                            style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                        <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                            <svg class="sidebar-icon flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                            </svg>
                            <span class="truncate">Manajemen Paket</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform flex-shrink-0 ml-2"
                             :class="paketOpen ? 'rotate-180' : ''"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="paketOpen"
                         x-collapse
                         style="margin-left: 1.5rem; padding-left: 0.75rem; border-left: 1px solid #334155;">
                        <a href="{{ route('superadmin.paket.index') }}"
                           class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('superadmin.paket.*') ? 'font-bold text-white' : 'font-normal text-slate-400 hover:text-slate-200' }}">
                            Paket Member
                        </a>
                        <a href="{{ route('superadmin.subscriptions.index') }}"
                           class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('superadmin.subscriptions.*') ? 'font-bold text-white' : 'font-normal text-slate-400 hover:text-slate-200' }}">
                            Subscription
                        </a>
                    </div>
                </div>

                {{-- Manajemen User --}}
                <div class="!mt-6 !mb-3 px-3">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Administrasi</span>
                </div>

                <a href="{{ route('superadmin.users.index') }}"
                   class="sidebar-link {{ request()->routeIs('superadmin.users.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM9 20H4v-2a6 6 0 0112 0v2H9z"/>
                    </svg>
                    <span class="flex-1">Manajemen User</span>
                </a>

                <a href="{{ route('superadmin.change-password.index') }}"
                   class="sidebar-link {{ request()->routeIs('superadmin.change-password.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    <span class="flex-1">Ganti Password</span>
                </a>

                {{-- Security & System --}}
                <div class="!mt-6 !mb-3 px-3">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Keamanan</span>
                </div>

                <a href="{{ route('superadmin.security.logs.index') }}"
                   class="sidebar-link {{ request()->routeIs('superadmin.security.logs.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span class="flex-1">Security Logs</span>
                    @php($badge = $moduleBadges['security-logs'] ?? null)
                    @if($badge && $badge['count'] > 0)
                        <span class="{{ $badge['class'] }} text-white text-xs font-bold px-2 py-0.5 rounded-full flex-shrink-0">{{ $badge['count'] }}</span>
                    @endif
                </a>

                <a href="{{ route('superadmin.security.blocked-ips.index') }}"
                   class="sidebar-link {{ request()->routeIs('superadmin.security.blocked-ips.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span class="flex-1">Blocked IPs</span>
                    @php($badge = $moduleBadges['security-blocked-ips'] ?? null)
                    @if($badge && $badge['count'] > 0)
                        <span class="{{ $badge['class'] }} text-white text-xs font-bold px-2 py-0.5 rounded-full flex-shrink-0">{{ $badge['count'] }}</span>
                    @endif
                </a>

            </nav>

            {{-- User Info + Logout --}}
            <div class="p-3 border-t border-slate-700">
                <div class="flex items-center gap-3 px-3 py-2">
                    <div class="w-10 h-10 bg-amber-500 rounded-lg flex items-center justify-center text-white text-sm font-semibold">
                        {{ substr(auth()->user()->name ?? 'S', 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name ?? 'Superadmin' }}</p>
                        <p class="text-xs text-slate-400 truncate">Superadmin</p>
                    </div>
                    <form method="POST" action="{{ route('superadmin.logout') }}">
                        @csrf
                        <button type="submit" class="p-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-colors" title="Logout">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col min-h-screen bg-slate-100/70">
            {{-- Top Header --}}
            <header class="h-16 bg-white/80 backdrop-blur-sm border-b border-slate-200/60 flex items-center justify-between px-4 lg:px-6 sticky top-0 z-30 shadow-sm">
                {{-- Left: Mobile menu + Breadcrumb --}}
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="lg:hidden text-slate-500 hover:text-slate-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>

                    {{-- Breadcrumb --}}
                    <nav class="hidden sm:flex items-center gap-2 text-sm">
                        <a href="{{ route('superadmin.dashboard') }}" class="text-slate-500 hover:text-primary-600">Dashboard</a>
                        @hasSection('breadcrumb')
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            @yield('breadcrumb')
                        @endif
                    </nav>
                </div>

                {{-- Right: Logout --}}
                <div class="flex items-center gap-3">
                    <form method="POST" action="{{ route('superadmin.logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span class="hidden sm:inline ml-2">Logout</span>
                        </button>
                    </form>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 p-4 lg:p-6 bg-gradient-to-br from-slate-100/50 via-white/30 to-blue-50/30">
                {{-- Page Header --}}
                @hasSection('header')
                    <div class="mb-6">
                        @yield('header')
                    </div>
                @endif

                {{-- Flash Messages --}}
                @if(session('success'))
                    <x-alert type="success" class="mb-6" dismissible>
                        {{ session('success') }}
                    </x-alert>
                @endif

                @if(session('error'))
                    <x-alert type="danger" class="mb-6" dismissible>
                        @if(is_array(session('error')))
                            <ul class="list-disc list-inside space-y-1">
                                @foreach(session('error') as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        @else
                            {{ session('error') }}
                        @endif
                    </x-alert>
                @endif

                @if(session('info'))
                    <x-alert type="info" class="mb-6" dismissible>
                        @if(is_array(session('info')))
                            <ul class="list-disc list-inside space-y-1">
                                @foreach(session('info') as $msg)
                                    <li>{{ $msg }}</li>
                                @endforeach
                            </ul>
                        @else
                            {!! nl2br(e(session('info'))) !!}
                        @endif
                    </x-alert>
                @endif

                {{-- Main Content --}}
                @yield('content')
            </main>

            {{-- Footer --}}
            <footer class="py-4 px-6 text-center text-sm text-slate-500 border-t border-slate-200 bg-white">
                &copy; {{ date('Y') }} Panritta Superadmin Panel. All rights reserved.
            </footer>
        </div>
    </div>

    {{-- Toast Container --}}
    <x-toast />

    {{-- Confirm Dialog (Global) --}}
    <x-confirm-dialog />

    @stack('scripts')
</body>
</html>
