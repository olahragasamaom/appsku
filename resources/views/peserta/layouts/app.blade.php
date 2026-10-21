<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ujian') - Portal Peserta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.css">
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
                    {{-- Notification Bell --}}
                    @php
                        try {
                            $unreadNotifs = \Illuminate\Support\Facades\Schema::hasTable('payment_notifications')
                                ? \App\Models\PaymentNotification::where('user_id', auth()->id())->whereNull('read_at')->count()
                                : 0;
                        } catch (\Throwable $e) {
                            $unreadNotifs = 0;
                        }
                    @endphp
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="relative inline-flex items-center justify-center w-10 h-10 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @if($unreadNotifs > 0)
                                <span class="absolute -top-1 -right-1 flex items-center justify-center min-w-[20px] h-5 bg-danger-500 text-white text-xs font-bold rounded-full px-1">
                                    {{ $unreadNotifs > 9 ? '9+' : $unreadNotifs }}
                                </span>
                            @endif
                        </button>

                        {{-- Dropdown Notification --}}
                        <div x-show="open" x-cloak @click.away="open = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-slate-200 overflow-hidden z-50">
                            <div class="px-4 py-3 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                                <h3 class="font-bold text-slate-800">Notifikasi</h3>
                                @if($unreadNotifs > 0)
                                    <span class="text-xs bg-danger-100 text-danger-700 px-2 py-0.5 rounded-full font-semibold">
                                        {{ $unreadNotifs }} baru
                                    </span>
                                @endif
                            </div>

                            <div class="max-h-96 overflow-y-auto">
                                @php
                                    try {
                                        $notifications = \Illuminate\Support\Facades\Schema::hasTable('payment_notifications')
                                            ? \App\Models\PaymentNotification::where('user_id', auth()->id())
                                                ->latest()
                                                ->limit(10)
                                                ->get()
                                            : collect();
                                    } catch (\Throwable $e) {
                                        $notifications = collect();
                                    }
                                @endphp

                                @forelse($notifications as $notif)
                                    <a href="{{ route('peserta.notifications.show', $notif) }}" 
                                       class="block px-4 py-3 border-b border-slate-100 hover:bg-slate-50 transition-colors {{ $notif->isUnread() ? 'bg-primary-50/50' : '' }}">
                                        <div class="flex items-start gap-3">
                                            @if($notif->type === 'verified')
                                                <div class="w-10 h-10 rounded-full bg-success-100 text-success-600 flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                </div>
                                            @elseif($notif->type === 'rejected')
                                                <div class="w-10 h-10 rounded-full bg-danger-100 text-danger-600 flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                                </div>
                                            @else
                                                <div class="w-10 h-10 rounded-full bg-warning-100 text-warning-600 flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 11-2 0 1 1 0 012 0zm-1 4a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                                                </div>
                                            @endif
                                            <div class="flex-1 min-w-0">
                                                <p class="font-semibold text-slate-900 text-sm">{{ $notif->title }}</p>
                                                <p class="text-xs text-slate-600 mt-1 line-clamp-2">{{ $notif->message }}</p>
                                                <p class="text-xs text-slate-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                                            </div>
                                            @if($notif->isUnread())
                                                <div class="w-2 h-2 rounded-full bg-primary-600 flex-shrink-0 mt-2"></div>
                                            @endif
                                        </div>
                                    </a>
                                @empty
                                    <div class="p-8 text-center text-slate-500">
                                        <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                        </svg>
                                        <p class="text-sm">Belum ada notifikasi</p>
                                    </div>
                                @endforelse
                            </div>

                            @if($notifications->isNotEmpty())
                                <div class="px-4 py-3 border-t border-slate-200 bg-slate-50 text-center">
                                    <a href="{{ route('peserta.notifications.index') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                                        Lihat semua notifikasi
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

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
