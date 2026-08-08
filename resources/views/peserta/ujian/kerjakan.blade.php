@extends('peserta.layouts.app')

@section('title', 'Mengerjakan Ujian')

@section('content')
@php
    // Kelompokkan soal per Sub Jenis Ujian untuk di sidebar navigasi
    $soalGroups = $ujianSoals->groupBy(function($item) {
        return $item->soal?->subIndikator?->subJenisUjian?->nama_sub_jenis_ujian ?? 'Umum';
    });
@endphp

<div x-data="examEngine({
        saveUrl: '{{ route('peserta.ujian.jawaban', $ujian) }}',
        sisaDetik: {{ $sisaDetik === null ? 'null' : $sisaDetik }},
        submitFormId: 'submit-form',
        initialJawaban: {{ Js::from($jawaban) }}
     })"
     x-init="init()"
     class="pb-20">

    {{-- Header sticky: Judul ujian + timer --}}
    <div class="flex items-center justify-between bg-white shadow-sm border border-slate-200 rounded-2xl px-5 py-4 mb-6 sticky top-2 z-30 transition-all">
        <div>
            <h1 class="font-bold text-lg md:text-xl text-slate-800 line-clamp-1">{{ $ujian->nama_ujian }}</h1>
            <div class="flex items-center gap-2 mt-1">
                <p class="text-xs text-slate-500" x-show="saving" x-cloak>
                    <svg class="animate-spin -ml-1 mr-1.5 h-3 w-3 text-primary-600 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Menyimpan...
                </p>
                <p class="text-xs font-medium text-success-600" x-show="!saving && lastSaved" x-cloak>
                    <svg class="w-3.5 h-3.5 inline mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Tersimpan
                </p>
            </div>
        </div>
        <div class="flex items-center gap-4 sm:gap-6">
            <template x-if="sisaDetik !== null">
                <div class="text-right bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Sisa Waktu</span>
                    <span class="text-xl font-black tabular-nums tracking-tight leading-none" :class="sisaDetik <= 300 ? 'text-danger-600 animate-pulse' : 'text-slate-800'" x-text="formatTime(sisaDetik)"></span>
                </div>
            </template>
            <button type="button" @click="confirmSubmit()" class="btn btn-primary shadow-sm hidden sm:inline-flex">Selesai Ujian</button>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-6 items-start relative">
        {{-- Area Kiri: Daftar Soal (75%) --}}
        <div class="flex-1 w-full space-y-8">
            @php $globalIndex = 1; @endphp
            @foreach($ujianSoals as $ujianSoal)
                @php $soal = $ujianSoal->soal; @endphp
                <div id="soal-{{ $ujianSoal->id }}" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden scroll-mt-24">
                    <div class="p-6 sm:p-8 flex items-start gap-4 sm:gap-6">
                        <div class="flex-col items-center flex-shrink-0 hidden sm:flex">
                            <span class="w-10 h-10 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-lg border border-slate-200 shadow-sm">{{ $globalIndex }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            {{-- Indikator Kategori & Nomor (Mobile) --}}
                            <div class="flex items-center gap-2 mb-4 pb-4 border-b border-slate-100">
                                <span class="sm:hidden w-7 h-7 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-sm border border-slate-200">{{ $globalIndex }}</span>
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

                            {{-- Opsi Jawaban --}}
                            <div class="mt-8 space-y-3">
                                @foreach(['A', 'B', 'C', 'D', 'E'] as $opsi)
                                    @php 
                                        $opsiText = $soal->{'opsi_'.strtolower($opsi)}; 
                                        $gambarOpsi = $soal->{'gambar_opsi_'.strtolower($opsi)};
                                    @endphp
                                    
                                    @if($opsiText !== null && $opsiText !== '')
                                        <label class="flex items-start gap-4 p-4 border rounded-xl cursor-pointer transition-colors group"
                                               :class="jawaban[{{ $ujianSoal->id }}] === '{{ $opsi }}' ? 'border-primary-500 bg-primary-50 shadow-sm ring-1 ring-primary-500' : 'border-slate-200 hover:bg-slate-50'">
                                            
                                            <div class="flex items-center h-6">
                                                <input type="radio"
                                                       name="soal_{{ $ujianSoal->id }}"
                                                       value="{{ $opsi }}"
                                                       x-model="jawaban[{{ $ujianSoal->id }}]"
                                                       @change="save({{ $ujianSoal->id }}, '{{ $opsi }}')"
                                                       class="w-5 h-5 text-primary-600 border-slate-300 focus:ring-primary-600">
                                            </div>
                                            
                                            <div class="flex-1 pt-0.5">
                                                <span class="text-base font-bold mr-2" :class="jawaban[{{ $ujianSoal->id }}] === '{{ $opsi }}' ? 'text-primary-700' : 'text-slate-700'">{{ $opsi }}.</span>
                                                <span class="text-base" :class="jawaban[{{ $ujianSoal->id }}] === '{{ $opsi }}' ? 'text-primary-900 font-medium' : 'text-slate-700'">{{ $opsiText }}</span>
                                                
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
                @php $globalIndex++; @endphp
            @endforeach
            
            <div class="flex justify-center sm:justify-end mt-8 pt-6 border-t border-slate-200">
                <button type="button" @click="confirmSubmit()" class="btn btn-primary px-8 py-3 text-lg w-full sm:w-auto shadow-lg shadow-primary-500/30">Akhiri Ujian Sekarang</button>
            </div>
        </div>

        {{-- Area Kanan: Navigasi Soal Sticky (25%) --}}
        <div class="w-full lg:w-72 xl:w-80 flex-shrink-0 lg:sticky lg:top-28 z-10 order-first lg:order-last mb-6 lg:mb-0">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 py-3 px-5 border-b border-slate-200">
                    <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        Navigasi Soal
                    </h3>
                </div>
                <div class="p-4 max-h-[60vh] overflow-y-auto">
                    @php $navIndex = 1; @endphp
                    @foreach($soalGroups as $groupName => $items)
                        <div class="mb-5 last:mb-0">
                            <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2.5">{{ $groupName }}</h4>
                            <div class="grid grid-cols-5 gap-2">
                                @foreach($items as $ujianSoal)
                                    <a href="#soal-{{ $ujianSoal->id }}" 
                                       class="w-full aspect-square flex items-center justify-center rounded-lg text-sm font-bold transition-all border"
                                       :class="jawaban[{{ $ujianSoal->id }}] ? 'bg-primary-500 text-white border-primary-600 shadow-sm' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50 hover:border-slate-400'">
                                        {{ $navIndex++ }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="bg-slate-50 p-4 border-t border-slate-200">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-xs font-medium text-slate-600">
                            <span class="w-4 h-4 rounded bg-primary-500 border border-primary-600 inline-block shadow-sm"></span> Sudah Dijawab
                        </div>
                        <div class="flex items-center gap-2 text-xs font-medium text-slate-600">
                            <span class="w-4 h-4 rounded bg-white border border-slate-300 inline-block"></span> Belum Dijawab
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="submit-form" method="POST" action="{{ route('peserta.ujian.submit', $ujian) }}" class="hidden">
        @csrf
    </form>
</div>
@endsection

@push('scripts')
<script>
    function examEngine(config) {
        return {
            saveUrl: config.saveUrl,
            sisaDetik: config.sisaDetik,
            submitFormId: config.submitFormId,
            jawaban: config.initialJawaban || {},
            saving: false,
            lastSaved: false,
            timer: null,

            init() {
                if (this.sisaDetik !== null) {
                    this.timer = setInterval(() => {
                        this.sisaDetik--;
                        if (this.sisaDetik <= 0) {
                            clearInterval(this.timer);
                            this.doSubmit();
                        }
                    }, 1000);
                }
                
                // Active link highlighting on scroll
                const observer = new IntersectionObserver(entries => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const id = entry.target.id;
                            document.querySelectorAll('.nav-link').forEach(link => {
                                link.classList.remove('ring-2', 'ring-offset-2', 'ring-slate-400');
                                if (link.getAttribute('href') === '#' + id) {
                                    link.classList.add('ring-2', 'ring-offset-2', 'ring-slate-400');
                                }
                            });
                        }
                    });
                }, { rootMargin: '-20% 0px -60% 0px' });
                
                document.querySelectorAll('[id^="soal-"]').forEach(el => observer.observe(el));
            },

            async save(ujianSoalId, jawaban) {
                this.saving = true;
                this.lastSaved = false;
                try {
                    await fetch(this.saveUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ ujian_soal_id: ujianSoalId, jawaban: jawaban }),
                    });
                    this.lastSaved = true;
                } finally {
                    this.saving = false;
                }
            },

            confirmSubmit() {
                if (confirm('Selesaikan dan kirim ujian? Jawaban tidak dapat diubah setelah ini.')) {
                    this.doSubmit();
                }
            },

            doSubmit() {
                document.getElementById(this.submitFormId).submit();
            },

            formatTime(seconds) {
                const h = Math.floor(seconds / 3600);
                const m = Math.floor((seconds % 3600) / 60);
                const s = seconds % 60;
                return [h, m, s].map(v => v < 10 ? '0' + v : v).join(':');
            }
        };
    }
</script>
@endpush
