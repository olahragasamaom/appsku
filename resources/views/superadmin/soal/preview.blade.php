<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Simulasi Soal - Panritta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-slate-50 min-h-screen">
    {{-- Header Tiruan --}}
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-5xl mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-2 py-1 rounded bg-warning-100 text-warning-700 text-xs font-bold uppercase tracking-wider">
                    Mode Simulasi
                </span>
                <span class="font-bold text-slate-800 hidden sm:inline">Portal Peserta (Tiruan)</span>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="window.close()" class="btn btn-secondary btn-sm">Tutup Simulasi</button>
            </div>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 py-6">
        
        {{-- Header sticky ujian: timer tiruan + tombol --}}
        <div class="flex items-center justify-between bg-white border border-slate-200 rounded-xl px-4 py-3 mb-6 sticky top-20 z-20 shadow-sm">
            <div>
                <h1 class="font-semibold text-slate-800">{{ $soal->subIndikator?->subJenisUjian?->nama_sub_jenis_ujian ?? 'Ujian Simulasi' }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">Sistem Penilaian: {{ $soal->subIndikator?->subJenisUjian?->sistem_penilaian === 'benar_salah' ? 'Benar-Salah' : 'Poin per Jawaban' }}</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <span class="block text-xs text-slate-400 uppercase">Sisa Waktu</span>
                    <span class="text-lg font-bold text-slate-800">59:59</span>
                </div>
                <button type="button" class="btn btn-primary btn-sm opacity-50 cursor-not-allowed">Selesai</button>
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-6 items-start">
            {{-- Bagian Kiri: Area Soal --}}
            <div class="flex-1 w-full space-y-6">
                <div class="card">
                    <div class="card-body">
                        <div class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-100 text-primary-700 font-semibold flex items-center justify-center text-sm">1</span>
                            <div class="flex-1 min-w-0">
                                <div class="mb-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600">
                                        {{ $soal->subIndikator?->nama_sub_indikator ?? 'Tanpa Kategori' }}
                                    </span>
                                </div>
                                <div class="prose prose-sm max-w-none text-slate-800">{!! $soal->soal !!}</div>
                                
                                @if($soal->gambar_soal)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($soal->gambar_soal) }}" alt="Gambar soal" class="mt-3 max-h-64 rounded-lg border border-slate-200 shadow-sm">
                                @endif

                                <div class="mt-5 space-y-2" x-data="{ terpilih: '' }">
                                    @foreach(['A', 'B', 'C', 'D', 'E'] as $opsi)
                                        @php 
                                            $opsiText = $soal->{'opsi_'.strtolower($opsi)}; 
                                            $gambarOpsi = $soal->{'gambar_opsi_'.strtolower($opsi)};
                                        @endphp
                                        
                                        @if($opsiText !== null && $opsiText !== '')
                                            <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer transition-colors"
                                                   :class="terpilih === '{{ $opsi }}' ? 'border-primary-500 bg-primary-50' : 'border-slate-200 hover:bg-slate-50'">
                                                <input type="radio" name="simulasi_opsi" value="{{ $opsi }}" x-model="terpilih" class="mt-0.5 w-4 h-4 text-primary-600 border-slate-300 focus:ring-primary-600">
                                                <div class="flex-1">
                                                    <span class="text-sm text-slate-700"><strong class="mr-1">{{ $opsi }}.</strong> {{ $opsiText }}</span>
                                                    
                                                    @if($gambarOpsi)
                                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($gambarOpsi) }}" alt="Opsi {{ $opsi }}" class="mt-2 max-h-40 rounded-lg border border-slate-200">
                                                    @endif
                                                </div>
                                            </label>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bagian Kanan: Navigasi Nomor Soal Tiruan --}}
            <div class="w-full md:w-64 flex-shrink-0 sticky top-40">
                <div class="card">
                    <div class="card-header bg-slate-50 py-3 px-4 border-b border-slate-200 rounded-t-xl">
                        <h3 class="font-semibold text-slate-800 text-sm">Navigasi Soal</h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="grid grid-cols-5 gap-2">
                            {{-- Nomor 1: Current Soal --}}
                            <button type="button" class="w-8 h-8 flex items-center justify-center rounded bg-primary-100 text-primary-700 border-2 border-primary-600 font-semibold text-xs transition-colors">
                                1
                            </button>

                            {{-- Tiruan nomor lainnya --}}
                            @for($i = 2; $i <= 10; $i++)
                                <button type="button" class="w-8 h-8 flex items-center justify-center rounded bg-white text-slate-600 border border-slate-300 hover:bg-slate-50 font-medium text-xs transition-colors opacity-50 cursor-not-allowed">
                                    {{ $i }}
                                </button>
                            @endfor
                        </div>

                        <div class="mt-5 space-y-2 border-t border-slate-100 pt-4">
                            <div class="flex items-center gap-2 text-xs text-slate-600">
                                <span class="w-3 h-3 rounded bg-primary-100 border-2 border-primary-600 inline-block"></span> Posisi Saat Ini
                            </div>
                            <div class="flex items-center gap-2 text-xs text-slate-600">
                                <span class="w-3 h-3 rounded bg-white border border-slate-300 inline-block"></span> Belum Dijawab
                            </div>
                            <div class="flex items-center gap-2 text-xs text-slate-600">
                                <span class="w-3 h-3 rounded bg-primary-50 border border-primary-300 inline-block"></span> Sudah Dijawab
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>

    {{-- Script Alpine.js bawaan Vite agar x-data berjalan --}}
</body>
</html>
