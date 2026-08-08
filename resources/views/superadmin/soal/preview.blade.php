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

    <main class="max-w-6xl mx-auto px-4 py-6">
        
        {{-- Header sticky ujian: timer tiruan + tombol --}}
        <div class="flex items-center justify-between bg-white shadow-sm border border-slate-200 rounded-2xl px-5 py-4 mb-6 sticky top-2 z-30 transition-all">
            <div>
                <h1 class="font-bold text-lg md:text-xl text-slate-800 line-clamp-1">{{ $soal->subIndikator?->subJenisUjian?->nama_sub_jenis_ujian ?? 'Ujian Simulasi' }}</h1>
                <div class="flex items-center gap-2 mt-1">
                    <p class="text-xs text-slate-500">Sistem Penilaian: {{ $soal->subIndikator?->subJenisUjian?->sistem_penilaian === 'benar_salah' ? 'Benar-Salah' : 'Poin per Jawaban' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4 sm:gap-6">
                <div class="text-right bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100 hidden sm:block">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Sisa Waktu</span>
                    <span class="text-xl font-black tabular-nums tracking-tight leading-none text-slate-800">59:59</span>
                </div>
                <button type="button" class="btn btn-primary shadow-sm hidden sm:inline-flex opacity-50 cursor-not-allowed">Selesai Ujian</button>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-6 items-start relative">
            {{-- Bagian Kiri: Area Soal (75%) --}}
            <div class="flex-1 w-full space-y-8">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6 sm:p-8 flex items-start gap-4 sm:gap-6">
                        <div class="flex-col items-center flex-shrink-0 hidden sm:flex">
                            <span class="w-10 h-10 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-lg border border-slate-200 shadow-sm">1</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            {{-- Indikator Kategori & Nomor (Mobile) --}}
                            <div class="flex items-center gap-2 mb-4 pb-4 border-b border-slate-100">
                                <span class="sm:hidden w-7 h-7 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-sm border border-slate-200">1</span>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-600 tracking-wide uppercase">
                                    {{ $soal->subIndikator?->subJenisUjian?->nama_sub_jenis_ujian ?? 'Umum' }} &mdash; {{ $soal->subIndikator?->nama_sub_indikator ?? 'Tanpa Kategori' }}
                                </span>
                            </div>

                            {{-- Teks Soal & Gambar --}}
                            <div class="prose prose-slate max-w-none text-slate-800 text-base leading-relaxed">
                                {!! $soal->soal !!}
                            </div>
                            @if($soal->gambar_soal)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($soal->gambar_soal) }}" alt="Gambar soal" class="mt-4 max-h-80 rounded-xl border border-slate-200 shadow-sm">
                            @endif

                            {{-- Opsi Jawaban (Interaktif Alpine Tiruan) --}}
                            <div class="mt-8 space-y-3" x-data="{ terpilih: '' }">
                                @foreach(['A', 'B', 'C', 'D', 'E'] as $opsi)
                                    @php 
                                        $opsiText = $soal->{'opsi_'.strtolower($opsi)}; 
                                        $gambarOpsi = $soal->{'gambar_opsi_'.strtolower($opsi)};
                                    @endphp
                                    
                                    @if($opsiText !== null && $opsiText !== '')
                                        <label class="flex items-start gap-4 p-4 border rounded-xl cursor-pointer transition-colors group"
                                               :class="terpilih === '{{ $opsi }}' ? 'border-primary-500 bg-primary-50 shadow-sm ring-1 ring-primary-500' : 'border-slate-200 hover:bg-slate-50'">
                                            
                                            <div class="flex items-center h-6">
                                                <input type="radio" name="simulasi_opsi" value="{{ $opsi }}" x-model="terpilih" class="w-5 h-5 text-primary-600 border-slate-300 focus:ring-primary-600">
                                            </div>
                                            
                                            <div class="flex-1 pt-0.5">
                                                <span class="text-base font-bold mr-2" :class="terpilih === '{{ $opsi }}' ? 'text-primary-700' : 'text-slate-700'">{{ $opsi }}.</span>
                                                <span class="text-base" :class="terpilih === '{{ $opsi }}' ? 'text-primary-900 font-medium' : 'text-slate-700'">{{ $opsiText }}</span>
                                                
                                                @if($gambarOpsi)
                                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($gambarOpsi) }}" alt="Opsi {{ $opsi }}" class="mt-3 max-h-40 rounded-lg border border-slate-200">
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

            {{-- Bagian Kanan: Navigasi Nomor Soal Tiruan (25%) --}}
            <div class="w-full lg:w-72 xl:w-80 flex-shrink-0 lg:sticky lg:top-28 z-10 order-first lg:order-last mb-6 lg:mb-0">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-slate-50 py-3 px-5 border-b border-slate-200 flex justify-between items-center">
                        <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            Navigasi Soal
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="mb-5 last:mb-0">
                            <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2.5">{{ $soal->subIndikator?->subJenisUjian?->nama_sub_jenis_ujian ?? 'Kategori Simulasi' }}</h4>
                            <div class="grid grid-cols-5 gap-2">
                                {{-- Nomor 1: Current Soal --}}
                                <button type="button" class="w-full aspect-square flex items-center justify-center rounded-lg bg-primary-500 text-white border-primary-600 shadow-sm font-bold text-sm transition-all">
                                    1
                                </button>

                                {{-- Tiruan nomor lainnya --}}
                                @for($i = 2; $i <= 10; $i++)
                                    <button type="button" class="w-full aspect-square flex items-center justify-center rounded-lg bg-white text-slate-600 border border-slate-300 hover:bg-slate-50 font-bold text-sm transition-all opacity-50 cursor-not-allowed">
                                        {{ $i }}
                                    </button>
                                @endfor
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-slate-50 p-4 border-t border-slate-200">
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 text-xs font-medium text-slate-600">
                                <span class="w-4 h-4 rounded bg-primary-500 border border-primary-600 inline-block shadow-sm"></span> Posisi Saat Ini
                            </div>
                            <div class="flex items-center gap-2 text-xs font-medium text-slate-600">
                                <span class="w-4 h-4 rounded bg-white border border-slate-300 inline-block"></span> Belum Dijawab
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
