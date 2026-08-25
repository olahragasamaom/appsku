@extends('peserta.layouts.app')

@section('title', 'Mengerjakan Ujian')

@section('content')
@php
    // Kelompokkan soal per Sub Jenis Ujian untuk di sidebar navigasi
    $soalGroups = $ujianSoals->groupBy(function($item) {
        return $item->soal?->subIndikator?->subJenisUjian?->nama_sub_jenis_ujian ?? 'Umum';
    });
@endphp

{{-- Dekorasi background: gradient blob halus di belakang konten --}}
<div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none" aria-hidden="true">
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-primary-200/40 rounded-full blur-3xl"></div>
    <div class="absolute top-1/3 -left-24 w-80 h-80 bg-indigo-200/30 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 right-1/4 w-72 h-72 bg-emerald-200/20 rounded-full blur-3xl"></div>
</div>

<div x-data="examEngine({
        saveUrl: '{{ route('peserta.ujian.jawaban', $ujian) }}',
        sisaDetik: {{ $sisaDetik === null ? 'null' : $sisaDetik }},
        submitFormId: 'submit-form',
        initialJawaban: {{ Js::from($jawaban) }},
        currentSoalIndex: 0,
        totalSoal: {{ count($ujianSoals) }}
     })"
     x-init="init()"
     class="pb-28">

    {{-- Header sticky: Judul ujian + status simpan --}}
    <div class="flex items-center justify-between bg-gradient-to-r from-primary-600 to-primary-700 shadow-lg shadow-primary-600/20 rounded-2xl px-5 py-4 mb-6 sticky top-2 z-30 transition-all">
        <div class="min-w-0 flex-1">
            <h1 class="font-bold text-lg md:text-xl text-white line-clamp-1">{{ $ujian->nama_ujian }}</h1>
            <div class="flex items-center gap-2 mt-1 h-4">
                <p class="text-xs text-primary-100" x-show="saving" x-cloak>
                    <svg class="animate-spin -ml-1 mr-1.5 h-3 w-3 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Menyimpan...
                </p>
                <p class="text-xs font-medium text-white" x-show="!saving && lastSaved" x-cloak>
                    <svg class="w-3.5 h-3.5 inline mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Tersimpan
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3 flex-shrink-0">
            <span class="text-xs font-semibold text-primary-100 whitespace-nowrap">Soal <span x-text="currentSoalIndex + 1"></span> dari <span x-text="totalSoal"></span></span>
            <button type="button" @click="confirmSubmit()" class="btn bg-white text-primary-700 hover:bg-primary-50 shadow-sm hidden sm:inline-flex">Selesai Ujian</button>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-6 items-start relative">
        {{-- Area Kiri: Soal Saat Ini (75%) --}}
        <div class="flex-1 w-full">
            @php 
                $soalArray = $ujianSoals->all();
                $globalIndex = 1;
            @endphp
            @foreach($soalArray as $index => $ujianSoal)
                @php $soal = $ujianSoal->soal; @endphp
                <div id="soal-{{ $ujianSoal->id }}" 
                     class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden"
                     x-show="currentSoalIndex === {{ $index }}"
                     x-transition:enter="transition ease-in-out duration-300"
                     x-transition:leave="transition ease-in-out duration-300">
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
                            @if($soal->soal)
                                <div class="prose prose-slate max-w-none text-slate-800 text-base leading-relaxed">
                                    {!! $soal->soal !!}
                                </div>
                            @endif
                            @if($soal->gambar_soal)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($soal->gambar_soal) }}" alt="Gambar soal" class="mt-4 max-h-80 rounded-xl border border-slate-200 shadow-sm">
                            @endif

                            {{-- Opsi Jawaban - Gunakan component yang support vertical/horizontal --}}
                            <x-soal-options-display :ujianSoal="$ujianSoal" :jawaban="$jawaban" />

                            {{-- Navigation Buttons --}}
                            <div class="flex gap-3 justify-between mt-8 pt-6 border-t border-slate-200">
                                <button type="button" 
                                        @click="currentSoalIndex = Math.max(0, currentSoalIndex - 1)"
                                        :disabled="currentSoalIndex === 0"
                                        class="btn btn-secondary"
                                        :class="currentSoalIndex === 0 ? 'opacity-50 cursor-not-allowed' : ''">
                                    ← Sebelumnya
                                </button>
                                <button type="button" 
                                        @click="currentSoalIndex === totalSoal - 1 ? confirmSubmit() : (currentSoalIndex = Math.min(totalSoal - 1, currentSoalIndex + 1))"
                                        class="btn btn-primary"
                                        x-text="currentSoalIndex === totalSoal - 1 ? 'Akhiri Ujian' : 'Selanjutnya →'">
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @php $globalIndex++; @endphp
            @endforeach
        </div>

        {{-- Area Kanan: Navigasi Soal Sticky (25%) --}}
        <div class="w-full lg:w-72 xl:w-80 flex-shrink-0 lg:sticky lg:top-24 z-10 order-first lg:order-last mb-6 lg:mb-0">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                <div class="bg-gradient-to-r from-slate-800 to-slate-700 py-3 px-5 border-b border-slate-200">
                    <h3 class="font-bold text-white text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        Navigasi Soal
                    </h3>
                </div>
                <div class="p-4 h-[52vh] overflow-y-auto">
                    @php 
                        $navIndex = 0;
                        $soalIndexMap = [];
                    @endphp
                    @foreach($soalGroups as $groupName => $items)
                        <div class="mb-5 last:mb-0">
                            <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2.5">{{ $groupName }}</h4>
                            <div class="grid grid-cols-5 gap-2">
                                @foreach($items as $ujianSoal)
                                    <button type="button" 
                                            @click="currentSoalIndex = {{ $navIndex }}"
                                            class="w-full aspect-square flex items-center justify-center rounded-lg text-sm font-bold transition-all border"
                                            :class="currentSoalIndex === {{ $navIndex }} ? 'bg-indigo-600 text-white border-indigo-700 shadow-md ring-2 ring-indigo-400' : (jawaban[{{ $ujianSoal->id }}] ? 'bg-primary-500 text-white border-primary-600 shadow-sm' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50 hover:border-slate-400')">
                                         {{ $navIndex + 1 }}
                                    </button>
                                    @php $navIndex++; @endphp
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

    {{-- Timer Floating: fixed kanan bawah, tetap saat scroll --}}
    <template x-if="sisaDetik !== null">
        <div class="fixed bottom-5 right-5 z-40">
            <div class="flex flex-col items-center rounded-2xl px-5 py-3 shadow-xl border transition-all"
                 :class="sisaDetik <= 300 ? 'bg-danger-600 border-danger-700 shadow-danger-600/40' : 'bg-white border-slate-200 shadow-slate-400/20'">
                <span class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider mb-0.5"
                      :class="sisaDetik <= 300 ? 'text-danger-100' : 'text-slate-400'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Sisa Waktu
                </span>
                <span class="text-2xl font-black tabular-nums tracking-tight leading-none"
                      :class="sisaDetik <= 300 ? 'text-white animate-pulse' : 'text-slate-800'"
                      x-text="formatTime(sisaDetik)"></span>
            </div>
        </div>
    </template>

    {{-- Tombol Selesai Floating (mobile) --}}
    <button type="button" @click="confirmSubmit()"
            class="fixed bottom-5 left-5 z-40 btn btn-primary shadow-xl shadow-primary-500/40 sm:hidden">
        Selesai Ujian
    </button>

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
            currentSoalIndex: config.currentSoalIndex || 0,
            totalSoal: config.totalSoal || 0,
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

                // Scroll to top when soal changes (untuk UX yang lebih baik)
                this.$watch('currentSoalIndex', () => {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });

                // Keyboard shortcuts (arrow keys untuk navigasi)
                document.addEventListener('keydown', (e) => {
                    // Skip jika user sedang mengetik di input/textarea
                    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
                    if (e.key === 'ArrowLeft') {
                        this.currentSoalIndex = Math.max(0, this.currentSoalIndex - 1);
                    } else if (e.key === 'ArrowRight') {
                        this.currentSoalIndex = Math.min(this.totalSoal - 1, this.currentSoalIndex + 1);
                    }
                });
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
