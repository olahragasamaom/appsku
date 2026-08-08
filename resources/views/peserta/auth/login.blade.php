<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk Peserta - Panritta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
<div class="min-h-screen flex">
    {{-- Panel Kiri - Branding --}}
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-primary-700 via-primary-800 to-primary-900 p-12 flex-col relative overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>
        </div>

        <div class="relative z-10 flex flex-col h-full">
            {{-- Logo --}}
            <div>
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center text-white text-2xl font-extrabold">P</div>
                    <span class="text-2xl font-bold text-white">Panritta</span>
                </a>
            </div>

            {{-- Konten Tengah --}}
            <div class="flex-1 flex items-center justify-center">
                <div class="max-w-md">
                    <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm rounded-full px-4 py-2 mb-6">
                        <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span class="text-white/90 text-sm font-medium">Platform Simulasi CAT CPNS</span>
                    </div>

                    <h1 class="text-4xl lg:text-5xl font-bold text-white mb-4 leading-tight">
                        Selamat Datang<br>Kembali, Pejuang!
                    </h1>
                    <p class="text-primary-100 text-lg mb-10">
                        Lanjutkan latihanmu dan taklukkan ujian CPNS & Kedinasan dengan simulasi terbaik.
                    </p>

                    <div class="grid grid-cols-3 gap-4 mb-2">
                        <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 text-center">
                            <div class="text-3xl font-bold text-white mb-1">1000+</div>
                            <div class="text-primary-200 text-sm">Peserta</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 text-center">
                            <div class="text-3xl font-bold text-white mb-1">5000+</div>
                            <div class="text-primary-200 text-sm">Bank Soal</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 text-center">
                            <div class="text-3xl font-bold text-white mb-1">98%</div>
                            <div class="text-primary-200 text-sm">Puas</div>
                        </div>
                    </div>
                    <p class="text-primary-200 text-xs text-center mb-8">*Data ilustrasi</p>

                    <div class="space-y-4">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="text-white">Sistem CAT persis aslinya</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="text-white">Pembahasan & analitik kelulusan</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="text-white">Perankingan real-time</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-primary-300 text-sm">
                &copy; {{ date('Y') }} Panritta CPNS. All rights reserved.
            </div>
        </div>
    </div>

    {{-- Panel Kanan - Form --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 lg:p-8 bg-white">
        <div class="w-full max-w-md">
            {{-- Logo Mobile --}}
            <div class="lg:hidden mb-8 text-center">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2">
                    <div class="w-10 h-10 bg-primary-600 rounded-xl flex items-center justify-center text-white font-extrabold text-lg">P</div>
                    <span class="text-xl font-bold text-secondary-900">Panritta</span>
                </a>
            </div>

            <div class="text-center mb-8">
                <h2 class="text-2xl lg:text-3xl font-bold text-secondary-900 mb-2">Masuk ke Portal Peserta</h2>
                <p class="text-secondary-500">Masukkan username dan password untuk melanjutkan</p>
            </div>

            @if($errors->any())
                <div class="bg-danger-50 border border-danger-200 text-danger-700 px-4 py-3 rounded-xl mb-6 text-sm flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('peserta.login') }}" class="space-y-5">
                @csrf

                {{-- Username --}}
                <div>
                    <label for="username" class="block text-sm font-medium text-secondary-700 mb-2">Username</label>
                    <div class="relative">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-secondary-400 pointer-events-none z-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus
                               class="input w-full" style="padding-left: 3rem;"
                               placeholder="Masukkan username Anda">
                    </div>
                </div>

                {{-- Password --}}
                <div x-data="{ show: false }">
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-sm font-medium text-secondary-700">Password</label>
                        <a href="#" class="text-sm text-primary-600 hover:text-primary-700 font-medium">Lupa password?</a>
                    </div>
                    <div class="relative">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-secondary-400 pointer-events-none z-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <input :type="show ? 'text' : 'password'" id="password" name="password" required
                               class="input w-full" style="padding-left: 3rem; padding-right: 3rem;"
                               placeholder="Masukkan password">
                        <button type="button" @click="show = !show"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-secondary-400 hover:text-secondary-600">
                            <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Remember --}}
                <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember" class="w-4 h-4 text-primary-600 border-secondary-300 rounded focus:ring-primary-500">
                    <label for="remember" class="ml-2 text-sm text-secondary-600">Ingat saya di perangkat ini</label>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn btn-primary w-full py-3.5 text-base">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    Masuk
                </button>

                {{-- Google Login --}}
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-secondary-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white text-secondary-500">atau masuk dengan</span>
                    </div>
                </div>
                <a href="{{ route('peserta.auth.google') }}" class="w-full flex items-center justify-center gap-3 px-4 py-3.5 border-2 border-secondary-200 rounded-full hover:bg-secondary-50 hover:border-secondary-300 transition-all duration-200">
                    <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    <span class="font-medium text-secondary-700">Google</span>
                </a>
            </form>

            {{-- Register Link --}}
            <p class="text-center mt-8 text-secondary-600">
                Belum punya akun?
                <a href="{{ route('peserta.register') }}" class="text-primary-600 hover:text-primary-700 font-semibold">Daftar gratis</a>
            </p>

            {{-- Link Peserta Offline --}}
            <p class="mt-4 text-center text-secondary-400 text-sm">
                Peserta ujian offline di kelas?
                <a href="{{ route('peserta.ujian.offline.portal') }}" class="text-secondary-600 hover:text-secondary-800 font-medium">Masuk lewat portal offline</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
