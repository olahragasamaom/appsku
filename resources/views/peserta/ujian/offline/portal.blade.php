<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal Ujian Offline - Panritta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-slate-50 min-h-screen flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">

<div class="w-full max-w-md">
    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl mx-auto flex items-center justify-center mb-4 shadow-lg shadow-primary-500/30">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        </div>
        <h1 class="text-3xl font-bold text-slate-900">Portal Ujian Offline</h1>
        <p class="text-slate-500 mt-2">Masukkan nomor peserta dan kode akses Anda</p>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 rounded-lg bg-success-50 border border-success-200 text-success-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 rounded-lg bg-danger-50 border border-danger-200 text-danger-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
        <div class="p-6 sm:p-8">
            <form method="POST" action="{{ route('peserta.ujian.offline.login') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="nomor_peserta" class="block text-sm font-medium text-slate-700 mb-2">Nomor Peserta</label>
                    <input type="text" 
                           name="nomor_peserta" 
                           id="nomor_peserta" 
                           class="input w-full @error('nomor_peserta') border-danger-300 @enderror" 
                           placeholder="Contoh: P001"
                           value="{{ old('nomor_peserta') }}"
                           required 
                           autofocus>
                    @error('nomor_peserta')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="kode_akses" class="block text-sm font-medium text-slate-700 mb-2">Kode Akses</label>
                    <input type="password" 
                           name="kode_akses" 
                           id="kode_akses" 
                           class="input w-full @error('kode_akses') border-danger-300 @enderror" 
                           placeholder="Masukkan kode akses"
                           required>
                    @error('kode_akses')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-full">
                    Masuk
                </button>
            </form>
        </div>
    </div>

    <p class="text-center text-sm text-slate-500 mt-6">
        Kode akses diberikan oleh pengawas ujian
    </p>
</div>

</body>
</html>
