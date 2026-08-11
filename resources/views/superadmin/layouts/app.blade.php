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

            {{-- Navigation (data-driven dari daftar modul + filter akses per level) --}}
            @php
                $sidebarUser = auth()->user();

                try {
                    $sidebarModules = \Illuminate\Support\Facades\Schema::hasTable('panritta_modules')
                        ? \App\Models\Module::where('is_active', true)->orderBy('urutan')->get()
                        : collect();
                } catch (\Throwable $e) {
                    $sidebarModules = collect();
                }

                $allowedModules = $sidebarModules->filter(fn ($m) => $sidebarUser?->canAccessModule($m->key));
                $groupedModules = $allowedModules->groupBy('grup');

                $moduleBadges = [
                    'system-queue' => ['count' => (function () {
                        try {
                            return \Illuminate\Support\Facades\Schema::hasTable('failed_jobs')
                                ? \Illuminate\Support\Facades\DB::table('failed_jobs')->count() : 0;
                        } catch (\Throwable $e) { return 0; }
                    })(), 'class' => 'bg-danger-500'],
                    'system-rate-limits' => ['count' => (function () {
                        try {
                            return \Illuminate\Support\Facades\Schema::hasTable('rate_limit_logs')
                                ? \App\Models\RateLimitLog::recent(24)->count() : 0;
                        } catch (\Throwable $e) { return 0; }
                    })(), 'class' => 'bg-warning-500'],
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
                @foreach($groupedModules as $grup => $items)
                    @if(!in_array($grup, ['Utama', '', null], true))
                        <div class="!mt-6 !mb-3 px-3">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">{{ $grup }}</span>
                        </div>
                    @endif

                    @foreach($items as $module)
                        @php($badge = $moduleBadges[$module->key] ?? null)
                        <a href="{{ $module->route_name && \Illuminate\Support\Facades\Route::has($module->route_name) ? route($module->route_name) : '#' }}"
                           class="sidebar-link {{ $module->route_pattern && request()->routeIs($module->route_pattern) ? 'active' : '' }}">
                            <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $module->icon }}"/>
                            </svg>
                            <span>{{ $module->label }}</span>
                            @if($badge && $badge['count'] > 0)
                                <span class="ml-auto {{ $badge['class'] }} text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $badge['count'] }}</span>
                            @endif
                        </a>
                    @endforeach
                @endforeach

                {{-- Latihan Tree Menu (Collapsible) - untuk belajar Laravel. Hanya untuk superadmin penuh. --}}
                @if($sidebarUser?->isSuperAdmin())
                <div class="!mt-6 !mb-3 px-3">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Latihan</span>
                </div>

                <div x-data="{ latihanOpen: {{ request()->routeIs('superadmin.latihan-*') ? 'true' : 'false' }} }">
                    <button type="button"
                            @click="latihanOpen = !latihanOpen"
                            class="sidebar-link w-full text-left {{ request()->routeIs('superadmin.latihan-*') ? 'active' : '' }}"
                            style="display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center;">
                            <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <span>Latihan</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform"
                             :class="latihanOpen ? 'rotate-180' : ''"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="latihanOpen"
                         x-collapse
                         style="margin-left: 1.5rem; padding-left: 0.75rem; border-left: 1px solid #334155;">
                        <a href="{{ route('superadmin.latihan-sederhana.index') }}"
                           class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('superadmin.latihan-sederhana.*') ? 'font-bold text-white' : 'font-normal text-slate-400 hover:text-slate-200' }}">
                            Modul Sederhana
                        </a>
                        <a href="{{ route('superadmin.latihan-detail.index') }}"
                           class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('superadmin.latihan-detail.*') ? 'font-bold text-white' : 'font-normal text-slate-400 hover:text-slate-200' }}">
                            Modul Detail
                        </a>
                    </div>
                </div>
                @endif
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
                        {{ session('error') }}
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
