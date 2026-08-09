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
<body class="font-sans antialiased bg-slate-50 min-h-screen flex flex-col items-center py-12 px-4 sm:px-6 lg:px-8">

<div class="w-full max-w-3xl">
    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl mx-auto flex items-center justify-center mb-4 shadow-lg shadow-primary-500/30">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        </div>
        <h1 class="text-3xl font-bold text-slate-900">Portal Ujian Offline</h1>
        <p class="text-slate-500 mt-2 text-lg">Daftar ujian yang dijadwalkan berlangsung hari ini.</p>
    </div>

    @if($ujians->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-center py-16 px-6">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-xl font-semibold text-slate-800">Tidak Ada Ujian Hari Ini</h3>
                <p class="text-slate-500 mt-2">Belum ada jadwal ujian offline yang aktif untuk hari ini.</p>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4">
            @foreach($ujians as $ujian)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden group">
                    <div class="p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-50 text-primary-700">Offline Class</span>
                                <span class="text-sm font-medium text-slate-500 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $ujian->tanggal_ujian?->format('H:i') ?? '-' }} WIB
                                </span>
                            </div>
                            <h3 class="font-bold text-xl text-slate-900 group-hover:text-primary-600 transition-colors">{{ $ujian->nama_ujian }}</h3>
                            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 mt-3 text-sm text-slate-600">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    {{ $ujian->jumlah_soal }} Soal
                                </span>
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $ujian->durasi_ujian }} Menit
                                </span>
                            </div>
                        </div>
                        <div class="sm:flex-shrink-0">
                            <a href="{{ route('peserta.ujian.offline.login', $ujian) }}" class="inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-xl shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 w-full sm:w-auto transition-colors">
                                Masuk Kelas
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

</body>
</html>
