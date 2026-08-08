<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Ujian Offline - {{ $ujian->nama_ujian }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gradient-to-br from-slate-100 to-primary-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        {{-- Header --}}
        <div class="text-center mb-6">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-600 to-primary-800 flex items-center justify-center text-white text-3xl font-extrabold mx-auto mb-4 shadow-lg shadow-primary-500/30">P</div>
            <h1 class="text-2xl font-bold text-slate-800">Login Ujian Offline</h1>
            <p class="text-slate-500 text-sm mt-1">{{ $ujian->nama_ujian }}</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
            <div class="p-8">
                @if($errors->any())
                    <div class="bg-danger-50 border border-danger-200 text-danger-700 px-4 py-3 rounded-xl mb-6 text-sm flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <p class="text-sm text-slate-500 mb-6 text-center">
                    Masukkan Nomor Peserta dan Kode Akses yang telah diberikan oleh pengawas ujian Anda.
                </p>

                <form action="{{ route('peserta.ujian.offline.login', $ujian) }}" method="POST" class="space-y-5">
                    @csrf

                    {{-- Nomor Peserta (ID) --}}
                    <div>
                        <label for="nomor_peserta" class="block text-sm font-medium text-slate-700 mb-2">Nomor Peserta (ID)</label>
                        <div class="relative">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none z-10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                            </div>
                            <input type="text" name="nomor_peserta" id="nomor_peserta" value="{{ old('nomor_peserta') }}" required autofocus
                                   class="input w-full" style="padding-left: 3rem;"
                                   placeholder="Contoh: OFF001">
                        </div>
                    </div>

                    {{-- Kode Akses (Password) --}}
                    <div x-data="{ show: false }">
                        <label for="kode_akses" class="block text-sm font-medium text-slate-700 mb-2">Kode Akses (Password)</label>
                        <div class="relative">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none z-10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <input :type="show ? 'text' : 'password'" name="kode_akses" id="kode_akses" required
                                   class="input w-full" style="padding-left: 3rem; padding-right: 3rem;"
                                   placeholder="Masukkan kode akses">
                            <button type="button" @click="show = !show"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="btn btn-primary w-full py-3.5 text-base">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        Mulai Ujian
                    </button>
                </form>
            </div>
        </div>

        <p class="mt-6 text-center text-slate-400 text-sm">
            &copy; {{ date('Y') }} Panritta CPNS
        </p>
    </div>
</body>
</html>
