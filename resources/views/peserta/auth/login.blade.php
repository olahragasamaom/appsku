<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Peserta - Panritta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-slate-50 min-h-screen flex">

    {{-- Kiri: Area Gambar/Promosi --}}
    <div class="hidden lg:flex lg:w-1/2 bg-primary-900 relative overflow-hidden items-center justify-center p-12">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/stardust.png')] opacity-10"></div>
        <div class="absolute top-0 left-0 w-72 h-72 bg-primary-600 rounded-full mix-blend-screen filter blur-3xl opacity-50 -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-primary-800 rounded-full mix-blend-screen filter blur-3xl opacity-50 translate-x-1/3 translate-y-1/3"></div>
        
        <div class="relative z-10 max-w-lg text-white">
            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-10 rounded-xl bg-white text-primary-600 flex items-center justify-center font-bold text-xl shadow-lg">P</div>
                <span class="font-extrabold text-2xl tracking-tight">Panritta.</span>
            </div>
            
            <h2 class="text-4xl font-extrabold mb-6 leading-tight">Selamat Datang Kembali, Pejuang ASN!</h2>
            <p class="text-primary-100 text-lg mb-8 leading-relaxed">Lanjutkan persiapanmu. Ribuan soal tryout dan simulasi CAT BKN terbaru sudah menantimu hari ini.</p>
            
            <div class="bg-primary-800/40 backdrop-blur-sm border border-primary-700/50 rounded-2xl p-6">
                <div class="flex gap-4">
                    <div class="text-4xl">🎯</div>
                    <div>
                        <h4 class="font-bold text-white text-lg">Konsisten Adalah Kunci</h4>
                        <p class="text-primary-200 text-sm mt-1">Peserta yang rutin melakukan tryout minimal 2x seminggu memiliki peluang lulus 85% lebih tinggi.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Kanan: Area Form Login --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 overflow-y-auto">
        <div class="w-full max-w-md">
            
            <div class="lg:hidden flex items-center justify-center gap-2 mb-8">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-600 to-primary-800 flex items-center justify-center text-white font-bold text-lg shadow-sm">P</div>
                <span class="font-extrabold text-2xl tracking-tight text-slate-900">Panritta.</span>
            </div>

            <div class="mb-8">
                <h1 class="text-3xl font-bold text-slate-900">Masuk ke Portal</h1>
                <p class="text-slate-500 mt-2">Gunakan username dan password yang telah Anda daftarkan.</p>
            </div>

            @if($errors->any())
                <div class="bg-danger-50 border border-danger-200 text-danger-700 px-4 py-3 rounded-xl mb-6 text-sm flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('peserta.login') }}" class="space-y-5">
                @csrf
                
                <div>
                    <label for="username" class="block text-sm font-medium text-slate-700 mb-1.5">Username</label>
                    <input type="text" name="username" id="username" value="{{ old('username') }}"
                           class="input w-full px-4 py-2.5 rounded-xl border-slate-300 focus:ring-primary-500 focus:border-primary-500" 
                           placeholder="Masukkan username Anda" required autofocus>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                        {{-- Fitur Lupa Password (Placeholder) --}}
                        <a href="#" class="text-xs font-semibold text-primary-600 hover:text-primary-700">Lupa password?</a>
                    </div>
                    <input type="password" name="password" id="password" 
                           class="input w-full px-4 py-2.5 rounded-xl border-slate-300 focus:ring-primary-500 focus:border-primary-500" 
                           placeholder="••••••••" required>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500 w-4 h-4">
                    <label for="remember" class="ml-2 block text-sm text-slate-600">Ingat saya di perangkat ini</label>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn btn-primary w-full justify-center py-3 rounded-xl shadow-lg shadow-primary-500/30 text-base">
                        Masuk Sekarang
                    </button>
                </div>
            </form>

            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-slate-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-3 bg-slate-50 text-slate-500 font-medium">ATAU MASUK DENGAN</span>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="{{ route('peserta.auth.google') }}" class="w-full flex items-center justify-center gap-3 px-4 py-2.5 border border-slate-300 rounded-xl shadow-sm bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        Google
                    </a>
                </div>
            </div>

            <p class="mt-8 text-center text-sm text-slate-600">
                Belum punya akun? 
                <a href="{{ route('peserta.register') }}" class="font-bold text-primary-600 hover:text-primary-700">Daftar sekarang</a>
            </p>
        </div>
    </div>
</body>
</html>