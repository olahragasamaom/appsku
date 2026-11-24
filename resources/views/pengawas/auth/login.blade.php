<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Masuk - Pengawas</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gradient-to-br from-slate-900 to-slate-800 flex items-center justify-center px-4">
        <div class="w-full max-w-md">
            <!-- Card -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <!-- Logo -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12H9m6 0a6 6 0 11-12 0 6 6 0 0112 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-secondary-900 mb-1">Masuk Pengawas</h2>
                    <p class="text-secondary-500 text-sm">Kelola absensi dan hasil ujian</p>
                </div>

                <!-- Login Form -->
                <form method="POST" action="{{ route('pengawas.login') }}" class="space-y-5">
                    @csrf

                    <!-- Email/Username -->
                    <div>
                        <label for="login" class="block text-sm font-medium text-secondary-700 mb-2">Email atau Username</label>
                        <div class="relative">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-secondary-400 pointer-events-none z-10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                                   class="form-input w-full @error('login') border-danger-500 @enderror"
                                   style="padding-left: 3rem;"
                                   placeholder="nama@perusahaan.com atau username">
                        </div>
                        @error('login')
                            <p class="mt-1 text-sm text-danger-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div x-data="{ show: false }">
                        <label for="password" class="block text-sm font-medium text-secondary-700 mb-2">Password</label>
                        <div class="relative">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-secondary-400 pointer-events-none z-10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input :type="show ? 'text' : 'password'" id="password" name="password" required
                                   class="form-input w-full @error('password') border-danger-500 @enderror"
                                   style="padding-left: 3rem; padding-right: 3rem;"
                                   placeholder="Masukkan password">
                            <button type="button" @click="show = !show"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-secondary-400 hover:text-secondary-600">
                                <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-sm text-danger-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember -->
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 text-primary-600 border-secondary-300 rounded focus:ring-primary-500">
                            <span class="text-sm text-secondary-600">Ingat saya</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary w-full">
                        Masuk
                    </button>
                </form>

                <!-- Back to Home -->
                <div class="mt-6 pt-6 border-t border-secondary-200 text-center">
                    <a href="{{ route('home') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                        Kembali ke halaman utama
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
