<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Ujian Offline - Panritta</title>
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
            <span class="text-sm text-slate-600 hidden sm:inline">{{ $pesertaOffline->nama_peserta }}</span>
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

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Selamat datang, {{ $pesertaOffline->nama_peserta }}</h1>
        <p class="text-slate-500 mt-1">Nomor Peserta: <strong>{{ $pesertaOffline->nomor_peserta }}</strong></p>
    </div>

    <section>
        <h2 class="text-lg font-semibold text-slate-700 mb-3">Ujian Anda</h2>
        
        @if($ujians->isEmpty())
            <div class="card">
                <div class="card-body text-center text-slate-500 py-8">
                    <p>Tidak ada ujian yang dialokasikan untuk Anda saat ini.</p>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4">
                @foreach($ujians as $ujian)
                    <div class="card border-l-4 {{ $ujian->myAttempt && $ujian->myAttempt->status === 'selesai' ? 'border-l-success-500' : 'border-l-primary-500' }}">
                        <div class="card-body">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-secondary-100 text-secondary-700">Offline</span>
                                        @if($ujian->token_ujian)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-700">
                                                Token Aktif
                                            </span>
                                        @endif
                                    </div>
                                    <h3 class="font-semibold text-slate-800 text-lg">{{ $ujian->nama_ujian }}</h3>
                                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-sm text-slate-600">
                                        @if($ujian->tanggal_ujian)
                                            <span class="flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                {{ $ujian->tanggal_ujian->format('d M Y, H:i') }} WIB
                                            </span>
                                        @endif
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                            {{ $ujian->jumlah_soal }} soal
                                        </span>
                                        @if($ujian->durasi_ujian)
                                            <span class="flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                {{ $ujian->durasi_ujian }} menit
                                            </span>
                                        @endif
                                    </div>

                                    @if($ujian->myAttempt)
                                        @if($ujian->myAttempt->status === 'selesai')
                                            <div class="mt-3 flex items-center gap-2 text-sm">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-700">Selesai</span>
                                                @if($ujian->myAttempt->total_nilai)
                                                    <span class="text-slate-600">Nilai: <strong>{{ $ujian->myAttempt->total_nilai }}</strong></span>
                                                @endif
                                            </div>
                                        @elseif($ujian->myAttempt->status === 'sedang_ujian')
                                            <div class="mt-3">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-700">Sedang Berlangsung</span>
                                            </div>
                                        @elseif($ujian->myAttempt->status === 'diblokir')
                                            <div class="mt-3">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-danger-100 text-danger-700">Diblokir Pengawas</span>
                                            </div>
                                        @endif
                                    @endif
                                </div>

                                <div class="flex flex-col gap-2">
                                    @if($ujian->myAttempt && $ujian->myAttempt->status === 'diblokir')
                                        <button disabled class="btn btn-secondary btn-sm opacity-50 cursor-not-allowed">Akun Diblokir</button>
                                    @elseif($ujian->myAttempt && $ujian->myAttempt->status === 'selesai')
                                        @if($ujian->tampilkan_hasil)
                                            <a href="{{ route('peserta.ujian.hasil', $ujian) }}" class="btn btn-secondary btn-sm">Lihat Hasil</a>
                                        @else
                                            <span class="text-xs text-slate-400">Hasil Disembunyikan</span>
                                        @endif
                                    @elseif(!$ujian->token_ujian)
                                        <button disabled class="btn btn-secondary btn-sm opacity-50 cursor-not-allowed">Menunggu Pengawas</button>
                                        <span class="text-xs text-slate-500 text-center">Token belum diaktifkan</span>
                                    @else
                                        @if($ujian->myAttempt && $ujian->myAttempt->status === 'sedang_ujian')
                                            <a href="{{ route('peserta.ujian.kerjakan', $ujian) }}" class="btn btn-primary btn-sm">Lanjutkan Ujian</a>
                                        @else
                                            <a href="{{ route('peserta.ujian.show', $ujian) }}" class="btn btn-primary btn-sm">Mulai Ujian</a>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
        <p class="font-medium mb-1">ℹ️ Informasi Penting:</p>
        <ul class="list-disc list-inside space-y-1 text-blue-600">
            <li>Tombol "Mulai Ujian" akan aktif setelah pengawas mengaktifkan token ujian</li>
            <li>Pastikan koneksi internet Anda stabil selama ujian berlangsung</li>
            <li>Jangan menutup browser atau keluar dari halaman saat ujian sedang berlangsung</li>
        </ul>
    </div>
</main>

</body>
</html>
