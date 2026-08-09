<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ujian Belum Dimulai - {{ $ujian->nama_ujian }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-gradient-to-br from-slate-100 to-primary-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md text-center">
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
            <div class="p-8">
                <div class="w-20 h-20 rounded-full bg-warning-100 flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-slate-800 mb-2">Ujian Belum Dimulai</h1>
                <p class="text-slate-500 mb-1">{{ $ujian->nama_ujian }}</p>

                <p class="text-slate-600 mt-6 leading-relaxed">
                    Ujian ini belum diaktifkan oleh penyelenggara. Silakan tunggu instruksi dari pengawas Anda,
                    @if($ujian->tanggal_ujian)
                        atau kembali lagi pada jadwal ujian:
                        <span class="block font-semibold text-slate-800 mt-2 text-lg">
                            {{ $ujian->tanggal_ujian->translatedFormat('l, d F Y - H:i') }} WIB
                        </span>
                    @else
                        atau hubungi pengawas untuk informasi jadwal.
                    @endif
                </p>

                <a href="{{ route('peserta.ujian.offline.portal') }}" class="btn btn-secondary w-full mt-8">
                    Kembali ke Portal Ujian
                </a>
            </div>
        </div>

        <p class="mt-6 text-center text-slate-400 text-sm">
            &copy; {{ date('Y') }} Panritta CPNS
        </p>
    </div>
</body>
</html>
