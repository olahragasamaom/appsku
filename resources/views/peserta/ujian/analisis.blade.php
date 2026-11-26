@extends('peserta.layouts.app')

@section('title', 'Analisis Kemampuan — ' . $ujian->nama_ujian)

@section('content')
    @php
        $totalSubIndikator = count($breakdownSubIndikator);
        $listTinggi = collect($breakdownSubIndikator)->filter(fn($i) => $i['pencapaian_persen'] >= 80);
        $listCukup = collect($breakdownSubIndikator)->filter(fn($i) => $i['pencapaian_persen'] >= 60 && $i['pencapaian_persen'] < 80);
        $listKurang = collect($breakdownSubIndikator)->filter(fn($i) => $i['pencapaian_persen'] < 60);

        $categories = collect($breakdownSubIndikator)
            ->pluck('sub_jenis_nama')
            ->filter()
            ->unique()
            ->values();
    @endphp

    <div class="max-w-4xl mx-auto" x-data="{
        activeCategory: 'all',
        onlyKurang: false,
        searchQuery: '',
        matches(item) {
            const matchCategory = this.activeCategory === 'all' || item.sub_jenis_nama === this.activeCategory;
            const matchKurang = !this.onlyKurang || item.is_kurang;
            const matchSearch = !this.searchQuery || item.nama.toLowerCase().includes(this.searchQuery.toLowerCase());
            return matchCategory && matchKurang && matchSearch;
        }
    }">
        {{-- Navigation & Action Header --}}
        <div class="flex items-center justify-between gap-4 mb-4 print:hidden">
            <a href="{{ route('peserta.ujian.hasil', $ujian) }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-800 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Hasil Ujian
            </a>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-sm transition-all">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Lembar Analisis
            </button>
        </div>

        {{-- Hero Header Card --}}
        <div class="card border border-slate-200 shadow-sm">
            <div class="card-body p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-primary-50 text-primary-700 mb-2 border border-primary-200">
                            <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            Evaluasi & Diagnosa Penguasaan Materi
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $ujian->nama_ujian }}</h1>
                        <p class="text-slate-600 text-sm mt-1 max-w-xl">
                            Daftar sub-indikator diurutkan dari capaian tertinggi ke terendah untuk memudahkan evaluasi mandiri serta panduan mentor dalam membimbing materi yang dinilai kurang.
                        </p>
                    </div>
                    <div class="text-left sm:text-right bg-slate-50 p-4 rounded-xl border border-slate-200 flex-shrink-0">
                        <span class="text-xs text-slate-500 block font-semibold uppercase tracking-wider">Total Nilai Peserta</span>
                        <span class="text-3xl font-black text-slate-900">{{ $peserta->total_nilai ?? 0 }}</span>
                        <span class="text-xs text-slate-600 block mt-0.5 font-medium">{{ $totalSubIndikator }} Sub Indikator Diuji</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Metric Overview Grid --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 mt-5">
            <div class="card p-4 border border-slate-200">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Topik</span>
                    <span class="w-7 h-7 rounded-lg bg-primary-50 text-primary-700 flex items-center justify-center font-bold text-xs">ALL</span>
                </div>
                <p class="text-2xl font-black text-slate-900 mt-2">{{ $totalSubIndikator }}</p>
                <span class="text-[11px] text-slate-500">Materi dalam ujian</span>
            </div>

            <div class="card p-4 border border-emerald-200 bg-emerald-50/40">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-emerald-800 uppercase tracking-wider">Tinggi (≥ 80%)</span>
                    <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">✓</span>
                </div>
                <p class="text-2xl font-black text-emerald-700 mt-2">{{ $listTinggi->count() }}</p>
                <span class="text-[11px] text-emerald-600">Pemahaman sangat kuat (Hijau)</span>
            </div>

            <div class="card p-4 border border-amber-200 bg-amber-50/40">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-amber-800 uppercase tracking-wider">Cukup (60–79%)</span>
                    <span class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs">~</span>
                </div>
                <p class="text-2xl font-black text-amber-700 mt-2">{{ $listCukup->count() }}</p>
                <span class="text-[11px] text-amber-600">Pemahaman sedang (Kuning)</span>
            </div>

            <div class="card p-4 border-2 {{ $listKurang->count() > 0 ? 'border-rose-400 bg-rose-50/60' : 'border-slate-200 bg-slate-50' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-rose-700 uppercase tracking-wider">Perlu Evaluasi (&lt; 60%)</span>
                    <span class="w-7 h-7 rounded-lg {{ $listKurang->count() > 0 ? 'bg-rose-600 text-white animate-pulse' : 'bg-slate-200 text-slate-600' }} flex items-center justify-center font-bold text-xs">!</span>
                </div>
                <p class="text-2xl font-black text-rose-700 mt-2">{{ $listKurang->count() }}</p>
                <span class="text-[11px] text-rose-600 font-medium">Grafik merah (prioritas mentor)</span>
            </div>
        </div>

        {{-- Filter & Search Controls --}}
        <div class="card mt-6 p-5 border border-slate-200 shadow-sm print:hidden">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                {{-- Category Filter Tabs --}}
                <div class="flex flex-wrap items-center" style="gap: 10px;">
                    <button type="button"
                            @click="activeCategory = 'all'"
                            :style="activeCategory === 'all'
                                ? 'background-color: #2563eb; color: #ffffff; border-color: #2563eb; box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);'
                                : 'background-color: #ffffff; color: #334155; border-color: #cbd5e1;'"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold border transition-all hover:bg-slate-50 cursor-pointer">
                        <span>Semua Kategori</span>
                        <span :style="activeCategory === 'all' ? 'background-color: rgba(255,255,255,0.25); color: #ffffff;' : 'background-color: #f1f5f9; color: #475569;'"
                              class="px-2 py-0.5 rounded-full text-[11px] font-extrabold">
                            {{ $totalSubIndikator }}
                        </span>
                    </button>
                    @foreach($categories as $cat)
                        @php
                            $catCount = collect($breakdownSubIndikator)->where('sub_jenis_nama', $cat)->count();
                        @endphp
                        <button type="button"
                                @click="activeCategory = '{{ $cat }}'"
                                :style="activeCategory === '{{ $cat }}'
                                    ? 'background-color: #2563eb; color: #ffffff; border-color: #2563eb; box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);'
                                    : 'background-color: #ffffff; color: #334155; border-color: #cbd5e1;'"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold border transition-all hover:bg-slate-50 cursor-pointer">
                            <span>{{ $cat }}</span>
                            <span :style="activeCategory === '{{ $cat }}' ? 'background-color: rgba(255,255,255,0.25); color: #ffffff;' : 'background-color: #f1f5f9; color: #475569;'"
                                  class="px-2 py-0.5 rounded-full text-[11px] font-extrabold">
                                {{ $catCount }}
                            </span>
                        </button>
                    @endforeach
                </div>

                {{-- Quick Toggles & Search --}}
                <div class="flex flex-wrap items-center" style="gap: 12px;">
                    <label class="inline-flex items-center gap-2.5 px-4 py-2 rounded-xl border text-xs font-semibold cursor-pointer select-none transition-all shadow-sm"
                           :style="onlyKurang ? 'border-color: #f87171; background-color: #fef2f2; color: #b91c1c;' : 'border-color: #cbd5e1; background-color: #ffffff; color: #475569;'">
                        <input type="checkbox" x-model="onlyKurang" class="rounded text-danger-600 focus:ring-danger-500 w-4 h-4 cursor-pointer">
                        <span>Hanya Grafik Merah (&lt; 60%)</span>
                    </label>

                    <div class="relative w-full sm:w-56">
                        <input type="text"
                               x-model="searchQuery"
                               placeholder="Cari sub indikator..."
                               class="w-full text-xs pl-8 pr-3 py-2 border border-slate-300 rounded-xl text-slate-800 bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 shadow-sm">
                        <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sub Indikator List (Ordered Highest to Lowest) --}}
        <div class="space-y-5 mt-6">
            @forelse($breakdownSubIndikator as $index => $item)
                @php
                    $isKurang = $item['is_kurang'];
                    $persen = $item['pencapaian_persen'];

                    if ($persen >= 80) {
                        $cardBorder = 'border-slate-200 hover:border-emerald-400 bg-emerald-50/10';
                        $barStyle = 'background: linear-gradient(90deg, #10b981 0%, #059669 100%);';
                        $badgeStyle = 'background-color: #ecfdf5; color: #065f46; border-color: #a7f3d0;';
                        $persenStyle = 'color: #059669;';
                        $rekomendasi = 'Tingkat penguasaan sangat baik (≥ 80%). Pertahankan performa!';
                    } elseif ($persen >= 60) {
                        $cardBorder = 'border-slate-200 hover:border-amber-400';
                        $barStyle = 'background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);';
                        $badgeStyle = 'background-color: #fffbeb; color: #92400e; border-color: #fde68a;';
                        $persenStyle = 'color: #d97706;';
                        $rekomendasi = 'Pemahaman cukup memadai (60–79%). Tingkatkan ketelitian dan percepat pengerjaan.';
                    } else {
                        // GRAFIK MERAH (< 60%)
                        $cardBorder = 'border-rose-300 bg-rose-50/30 hover:border-rose-500 shadow-sm';
                        $barStyle = 'background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%);';
                        $badgeStyle = 'background-color: #fef2f2; color: #991b1b; border-color: #fca5a5;';
                        $persenStyle = 'color: #dc2626;';
                        $rekomendasi = 'Prioritas Bimbingan: Pemahaman pada materi ini dinilai kurang (< 60%). Disarankan penguatan konsep dan latihan soal bersama mentor.';
                    }
                @endphp

                <div class="card p-6 border-2 transition-all {{ $cardBorder }} shadow-sm"
                     x-show="matches({{ json_encode($item) }})"
                     x-transition>
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-4">
                        <div class="flex items-start gap-3.5">
                            {{-- Rank Badge --}}
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-xs font-black {{ $index < 3 ? 'bg-primary-100 text-primary-700' : 'bg-slate-100 text-slate-600' }} flex-shrink-0 mt-0.5 border border-slate-200">
                                #{{ $index + 1 }}
                            </span>

                            <div>
                                <div class="flex items-center gap-2.5 flex-wrap">
                                    <span class="inline-block px-2.5 py-0.5 rounded-md text-xs font-extrabold bg-slate-100 text-slate-700 uppercase tracking-wider border border-slate-200">
                                        {{ $item['sub_jenis_nama'] }}
                                    </span>
                                    <h2 class="text-lg font-bold text-slate-900 tracking-tight">{{ $item['nama'] }}</h2>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-slate-600 mt-2.5 flex-wrap">
                                    <span class="bg-slate-50 px-2.5 py-1 rounded-md border border-slate-200">Total Soal: <strong class="text-slate-900 font-bold">{{ $item['total_soal'] }}</strong></span>
                                    <span class="bg-slate-50 px-2.5 py-1 rounded-md border border-slate-200">Dijawab: <strong class="text-slate-900 font-bold">{{ $item['total_dijawab'] }}</strong></span>
                                    <span class="bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200 text-emerald-800">Benar: <strong class="text-emerald-700 font-bold">{{ $item['total_benar'] }}</strong></span>
                                    <span class="bg-rose-50 px-2.5 py-1 rounded-md border border-rose-200 text-rose-800">Salah / Kosong: <strong class="text-rose-700 font-bold">{{ $item['total_salah'] }}</strong></span>
                                </div>
                            </div>
                        </div>

                        {{-- Score & Status Badge --}}
                        <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-2 flex-shrink-0 pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                            <span :style="'{{ $badgeStyle }}'" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border shadow-xs">
                                @if($isKurang)
                                    <svg class="w-3.5 h-3.5 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                @else
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @endif
                                {{ $item['status_label'] }}
                            </span>
                            <div class="text-right">
                                <span class="text-2xl font-black" style="{{ $persenStyle }}">{{ $persen }}%</span>
                                <span class="text-xs text-slate-500 block">({{ $item['nilai'] }} / {{ $item['nilai_maksimal'] }} poin)</span>
                            </div>
                        </div>
                    </div>

                    {{-- Progress Bar (Grafik Nilai) --}}
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs text-slate-600 mb-1.5 font-medium">
                            <span>Tingkat Capaian Penguasaan</span>
                            <span class="font-bold" style="{{ $persenStyle }}">{{ $persen }}% dari 100%</span>
                        </div>
                        <div class="w-full bg-slate-200/90 rounded-full h-4 overflow-hidden p-0.5 border border-slate-300 shadow-inner">
                            <div class="h-full rounded-full transition-all duration-500 shadow-xs"
                                 style="width: {{ min($persen, 100) }}%; {{ $barStyle }}"></div>
                        </div>
                    </div>

                    {{-- Rekomendasi Evaluasi --}}
                    <div class="mt-4 p-3 rounded-xl flex items-start gap-2.5 text-xs border {{ $isKurang ? 'bg-danger-50/80 border-danger-200 text-danger-800' : 'bg-slate-50 border-slate-200 text-slate-700' }}">
                        <svg class="w-4 h-4 {{ $isKurang ? 'text-danger-600' : 'text-primary-600' }} flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span><strong>Catatan Evaluasi Mentor:</strong> {{ $rekomendasi }}</span>
                    </div>
                </div>
            @empty
                <div class="card p-8 text-center text-slate-500">
                    Belum ada data sub-indikator yang terhubung pada soal-soal ujian ini.
                </div>
            @endforelse
        </div>

        {{-- Footer Action Buttons --}}
        <div class="mt-8 flex flex-wrap items-center justify-center gap-3 print:hidden">
            <a href="{{ route('peserta.ujian.hasil', $ujian) }}" class="btn btn-secondary border border-slate-300 text-slate-700 hover:bg-slate-100 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Hasil
            </a>
            <a href="{{ route('peserta.ujian.pembahasan', $ujian) }}" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: #ffffff;" class="btn btn-primary font-medium shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Lihat Pembahasan Jawaban
            </a>
            <a href="{{ route('peserta.ujian.leaderboard', $ujian) }}" style="background-color: #ffffff; color: #2563eb; border: 1.5px solid #3b82f6;" class="btn btn-secondary font-medium hover:bg-slate-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                Lihat Peringkat
            </a>
        </div>
    </div>
@endsection
