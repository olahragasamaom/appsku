<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Ujian - Panritta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-slate-50 min-h-screen">

<header class="bg-white border-b border-slate-200 sticky top-0 z-30">
    <div class="max-w-5xl mx-auto px-4 h-16 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-primary-600 to-primary-800 flex items-center justify-center text-white font-bold text-lg shadow-sm">P</div>
            <span class="font-bold text-slate-800">Panritta<span class="text-primary-600">.</span></span>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-slate-600 hidden sm:inline">{{ $peserta->nama_peserta }}</span>
            <form method="POST" action="{{ route('peserta.ujian.offline.logout') }}">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm text-slate-600">Logout</button>
            </form>
        </div>
    </div>
</header>

<main class="max-w-5xl mx-auto px-4 py-6">
    @if(session('success'))
        <div class="mb-6 p-4 rounded-lg bg-success-50 border border-success-200 text-success-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-lg bg-danger-50 border border-danger-200 text-danger-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Selamat datang, {{ $peserta->nama_peserta }}</h1>
        <p class="text-slate-500 mt-1">Nomor Peserta: <strong>{{ $peserta->nomor_peserta }}</strong></p>
    </div>

    <section>
        <h2 class="text-lg font-semibold text-slate-700 mb-3">Daftar Ujian</h2>
        
        @if($ujians->isEmpty())
            <div class="card">
                <div class="card-body text-center text-slate-500 py-8">
                    <p>Tidak ada ujian offline yang tersedia saat ini.</p>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4">
                @foreach($ujians as $ujian)
                    @php
                        $kehadiran = $ujian->pesertaOfflineKehadiran->first();
                        $isHadir = $kehadiran && $kehadiran->status_kehadiran === 'hadir';
                        $isAktif = $ujian->status === 'aktif';
                        $canStart = $isHadir && $isAktif;
                    @endphp
                    
                    <div class="card border-l-4 {{ $canStart ? 'border-l-success-500' : 'border-l-slate-300' }}">
                        <div class="card-body">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-slate-800 mb-2">{{ $ujian->nama_ujian }}</h3>
                                    
                                    <div class="flex flex-wrap gap-2 mb-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-secondary-100 text-secondary-700">
                                            Offline
                                        </span>
                                        
                                        @if($isAktif)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-success-100 text-success-700">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-warning-100 text-warning-700">
                                                {{ ucfirst($ujian->status) }}
                                            </span>
                                        @endif

                                        @if($isHadir)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-success-100 text-success-700">
                                                ✓ Hadir
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600">
                                                Belum Hadir
                                            </span>
                                        @endif
                                    </div>

                                    <div class="text-sm text-slate-600 space-y-1">
                                        @if($ujian->tanggal_ujian)
                                            <p>📅 {{ $ujian->tanggal_ujian->format('d M Y, H:i') }} WIB</p>
                                        @endif
                                        @if($ujian->durasi_ujian)
                                            <p>⏱ Durasi: {{ $ujian->durasi_ujian }} menit</p>
                                        @endif
                                        <p>📝 {{ $ujian->jumlah_soal }} soal</p>
                                    </div>

                                    @if(!$canStart)
                                        <div class="mt-3 text-sm">
                                            @if(!$isAktif)
                                                <p class="text-warning-600">⚠️ Ujian belum diaktifkan oleh admin</p>
                                            @elseif(!$isHadir)
                                                <p class="text-slate-600">⚠️ Anda belum ditandai hadir oleh admin</p>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <div class="flex-shrink-0">
                                    @if($canStart)
                                        <form method="POST" action="{{ route('peserta.offline.start', $ujian->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-primary">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Ikuti Ujian
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-secondary" disabled>
                                            Belum Tersedia
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</main>

</body>
</html>
