<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mengerjakan Ujian - Portal Peserta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        function examEngine(config) {
            return {
                saveUrl: config.saveUrl,
                sisaDetik: config.sisaDetik,
                submitFormId: config.submitFormId,
                jawaban: config.initialJawaban || {},
                ragu: {},
                raguKey: config.raguKey || 'ragu_ujian_default',
                currentSoalIndex: config.currentSoalIndex || 0,
                totalSoal: config.totalSoal || 0,
                saving: false,
                lastSaved: false,
                timer: null,

                init() {
                    try {
                        const stored = localStorage.getItem(this.raguKey);
                        if (stored) {
                            this.ragu = JSON.parse(stored);
                        }
                    } catch (e) {
                        console.warn('Gagal memuat data ragu-ragu:', e);
                        this.ragu = {};
                    }

                    if (this.sisaDetik !== null) {
                        this.timer = setInterval(() => {
                            this.sisaDetik--;
                            if (this.sisaDetik <= 0) {
                                clearInterval(this.timer);
                                this.autoSubmitOnTimeout();
                            }
                        }, 1000);
                    }

                    this.$watch('currentSoalIndex', () => {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    });

                    document.addEventListener('keydown', (e) => {
                        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
                        if (e.key === 'ArrowLeft') {
                            this.currentSoalIndex = Math.max(0, this.currentSoalIndex - 1);
                        } else if (e.key === 'ArrowRight') {
                            this.currentSoalIndex = Math.min(this.totalSoal - 1, this.currentSoalIndex + 1);
                        }
                    });
                },

                toggleRagu(ujianSoalId) {
                    if (this.ragu[ujianSoalId]) {
                        delete this.ragu[ujianSoalId];
                    } else {
                        this.ragu[ujianSoalId] = true;
                    }
                    try {
                        localStorage.setItem(this.raguKey, JSON.stringify(this.ragu));
                    } catch (e) {
                        console.warn('Gagal menyimpan data ragu-ragu:', e);
                    }
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
                    const raguCount = Object.keys(this.ragu).length;
                    let message = 'Selesaikan dan kirim ujian? Jawaban tidak dapat diubah setelah ini.';
                    if (raguCount > 0) {
                        message = `Anda masih memiliki ${raguCount} soal yang ditandai Ragu-Ragu. Jawaban ragu-ragu akan tetap tersimpan sebagai jawaban terpilih.\n\nSelesaikan dan kirim ujian?`;
                    }
                    if (confirm(message)) {
                        this.clearRagu();
                        this.doSubmit();
                    }
                },

                autoSubmitOnTimeout() {
                    this.clearRagu();
                    this.doSubmit();
                },

                clearRagu() {
                    try {
                        localStorage.removeItem(this.raguKey);
                    } catch (e) {
                        // ignore
                    }
                },

                doSubmit() {
                    document.getElementById(this.submitFormId).submit();
                },

                formatTime(seconds) {
                    if (seconds === null) return '00:00:00';
                    const h = Math.floor(seconds / 3600);
                    const m = Math.floor((seconds % 3600) / 60);
                    const s = seconds % 60;
                    return [h, m, s].map(v => v < 10 ? '0' + v : v).join(':');
                },

                countAnswered() {
                    return Object.keys(this.jawaban || {}).filter(k => this.jawaban[k] != null).length || 0;
                },

                countUnanswered() {
                    return Math.max(0, (this.totalSoal || 0) - this.countAnswered());
                }
            };
        }
    </script>
</head>
<body class="font-sans antialiased bg-slate-50 min-h-screen">
    @php
        $soalGroups = $ujianSoals->groupBy(function($item) {
            return $item->soal?->subIndikator?->subJenisUjian?->nama_sub_jenis_ujian ?? 'Umum';
        });
    @endphp

    <div x-data="examEngine({
            saveUrl: '{{ route('peserta.ujian.jawaban', $ujian) }}',
            sisaDetik: {{ $sisaDetik === null ? 'null' : $sisaDetik }},
            submitFormId: 'submit-form',
            initialJawaban: {{ Js::from($jawaban) }},
            currentSoalIndex: 0,
            totalSoal: {{ count($ujianSoals) }},
            raguKey: 'ragu_ujian_{{ $ujian->id }}_{{ $peserta->id }}'
         })"
         x-init="init()"
         class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">

        {{-- Header Exam --}}
        <header class="sticky top-0 z-40 bg-white border-b border-slate-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 md:px-6 py-3 md:py-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    {{-- Left: Logo & Title --}}
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-primary-600 to-primary-800 flex items-center justify-center text-white font-bold text-lg shadow-sm">P</div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h1 class="font-bold text-base md:text-lg text-slate-900 line-clamp-1">{{ $ujian->nama_ujian }}</h1>
                            <p class="text-xs text-slate-500">Seleksi Kompetensi Dasar (SKD)</p>
                        </div>
                    </div>

                    {{-- Right: Timer + Button --}}
                    <div class="flex items-center gap-4 justify-between md:justify-end">
                        {{-- Timer --}}
                        <div class="flex items-center gap-2 px-3 py-2 bg-slate-50 rounded-lg">
                            <div class="text-center">
                                <div class="flex items-center gap-1 text-xs font-semibold text-slate-600 mb-1">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Sisa Waktu</span>
                                </div>
                                <div class="font-bold text-xl md:text-2xl text-slate-900 tabular-nums leading-none" 
                                     x-text="formatTime(sisaDetik)"
                                     :class="sisaDetik <= 300 && sisaDetik !== null ? 'text-danger-600 animate-pulse' : ''"></div>
                            </div>
                        </div>

                        {{-- Button --}}
                        <button type="button" 
                                @click="confirmSubmit()"
                                class="btn bg-danger-500 hover:bg-danger-600 text-white border-0 font-semibold whitespace-nowrap">
                            <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span class="hidden sm:inline">Akhiri Ujian</span>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 md:px-6 py-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Content (Soal) --}}
                <div class="lg:col-span-2 space-y-4">
                    @php 
                        $soalArray = $ujianSoals->all();
                        $globalIndex = 1;
                    @endphp
                    @foreach($soalArray as $index => $ujianSoal)
                        @php $soal = $ujianSoal->soal; @endphp
                        <div id="soal-{{ $ujianSoal->id }}"
                             x-show="currentSoalIndex === {{ $index }}"
                             x-transition:enter="transition ease-in-out duration-300"
                             x-transition:leave="transition ease-in-out duration-300">
                            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                                {{-- Content --}}
                                <div class="p-6 md:p-8">
                                    {{-- Header dengan kategori --}}
                                    <div class="mb-6 pb-6 border-b border-slate-200">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="flex-1">
                                                <h2 class="text-base md:text-lg font-bold text-primary-600">
                                                    {{ $soal->subIndikator?->subJenisUjian?->nama_sub_jenis_ujian ?? 'Umum' }}
                                                </h2>
                                                <p class="text-sm text-slate-600 mt-1">Soal <span x-text="currentSoalIndex + 1"></span> dari <span x-text="totalSoal"></span></p>
                                            </div>
                                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-primary-50 text-primary-600 font-bold text-sm flex-shrink-0 border-2 border-primary-200">
                                                {{ $globalIndex }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Teks Soal --}}
                                    <div class="mb-6">
                                        @if($soal->soal)
                                            <div class="prose prose-sm md:prose-base prose-slate max-w-none text-slate-800 leading-relaxed">
                                                {!! $soal->soal !!}
                                            </div>
                                        @endif
                                        @if($soal->gambar_soal)
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($soal->gambar_soal) }}" 
                                                 alt="Gambar soal" 
                                                 class="mt-6 max-h-96 rounded-lg border border-slate-200 shadow-sm">
                                        @endif
                                    </div>

                                    {{-- Opsi Jawaban --}}
                                    <div class="mb-8 space-y-3">
                                        <x-soal-options-display :ujianSoal="$ujianSoal" :jawaban="$jawaban" />
                                    </div>

                                    {{-- Navigation --}}
                                    <div class="flex flex-col sm:flex-row gap-3 justify-between pt-6 border-t border-slate-200">
                                        <button type="button" 
                                                @click="currentSoalIndex = Math.max(0, currentSoalIndex - 1)"
                                                :disabled="currentSoalIndex === 0"
                                                class="btn btn-secondary"
                                                :class="currentSoalIndex === 0 ? 'opacity-50 cursor-not-allowed' : ''">
                                            ← Soal Sebelumnya
                                        </button>

                                        <button type="button"
                                                @click="toggleRagu({{ $ujianSoal->id }})"
                                                class="btn transition-colors"
                                                :class="ragu[{{ $ujianSoal->id }}] ? 'bg-yellow-500 hover:bg-yellow-600 text-white border-yellow-600' : 'bg-white hover:bg-yellow-50 text-yellow-700 border-2 border-yellow-400'">
                                            <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span x-text="ragu[{{ $ujianSoal->id }}] ? 'Batal Ragu-Ragu' : 'Tandai Ragu-Ragu'"></span>
                                        </button>

                                        <button type="button" 
                                                @click="currentSoalIndex === totalSoal - 1 ? confirmSubmit() : (currentSoalIndex = Math.min(totalSoal - 1, currentSoalIndex + 1))"
                                                class="btn btn-primary">
                                            <span x-text="currentSoalIndex === totalSoal - 1 ? 'Soal Selanjutnya →' : 'Soal Selanjutnya →'"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Internet Connection Alert (di bawah card soal) --}}
                            <div class="mt-4 bg-blue-50 border border-blue-300 rounded-lg p-4 flex items-center gap-3 shadow-sm">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-sm text-blue-800">Pastikan koneksi internet stabil selama ujian berlangsung.</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Sidebar Navigation --}}
                <div class="lg:col-span-1">
                    <div class="sticky top-20 space-y-4">
                        {{-- Navigation Panel --}}
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                            <div class="bg-gradient-to-r from-slate-800 to-slate-700 px-5 py-4">
                                <h3 class="font-bold text-white text-sm flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                    </svg>
                                    Navigasi Soal
                                </h3>
                            </div>
                            <div class="p-4 max-h-96 overflow-y-auto space-y-4">
                                @php 
                                    $navIndex = 0;
                                @endphp
                                @foreach($soalGroups as $groupName => $items)
                                    <div>
                                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ $groupName }}</h4>
                                        <div class="grid grid-cols-5 gap-2">
                                            @foreach($items as $ujianSoal)
                                                @php $idxNow = $navIndex; @endphp
                                                <button type="button" 
                                                        @click="currentSoalIndex = {{ $idxNow }}"
                                                        class="w-full aspect-square flex items-center justify-center rounded-lg text-xs md:text-sm font-bold transition-all border-2"
                                                        :class="{
                                                            'bg-primary-600 text-white border-primary-700 shadow-md': currentSoalIndex === {{ $idxNow }},
                                                            'bg-yellow-400 text-white border-yellow-600': currentSoalIndex !== {{ $idxNow }} && ragu[{{ $ujianSoal->id }}],
                                                            'bg-primary-500 text-white border-primary-600': currentSoalIndex !== {{ $idxNow }} && !ragu[{{ $ujianSoal->id }}] && jawaban[{{ $ujianSoal->id }}],
                                                            'bg-white text-slate-700 border-slate-300 hover:bg-slate-50': currentSoalIndex !== {{ $idxNow }} && !ragu[{{ $ujianSoal->id }}] && !jawaban[{{ $ujianSoal->id }}]
                                                        }">
                                                    {{ $navIndex + 1 }}
                                                </button>
                                                @php $navIndex++; @endphp
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Legend --}}
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                            <h4 class="font-bold text-slate-900 text-sm mb-3">Keterangan</h4>
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 rounded bg-primary-600 border border-primary-700"></div>
                                    <span class="text-xs text-slate-600">Soal Aktif</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 rounded bg-primary-500 border border-primary-600"></div>
                                    <span class="text-xs text-slate-600">Sudah Dijawab</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 rounded bg-yellow-400 border border-yellow-600"></div>
                                    <span class="text-xs text-slate-600">Ragu-Ragu</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 rounded bg-white border border-slate-300"></div>
                                    <span class="text-xs text-slate-600">Belum Dijawab</span>
                                </div>
                            </div>
                        </div>

                        {{-- Summary --}}
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs font-semibold text-slate-600 mb-1">Sudah Dijawab</p>
                                    <p class="text-2xl font-bold text-primary-600" x-text="countAnswered()"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-slate-600 mb-1">Ragu-Ragu</p>
                                    <p class="text-2xl font-bold text-yellow-600" x-text="Object.keys(ragu || {}).length"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-slate-600 mb-1">Belum Dijawab</p>
                                    <p class="text-2xl font-bold text-slate-400" x-text="countUnanswered()"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-slate-600 mb-1">Total Soal</p>
                                    <p class="text-2xl font-bold text-slate-900" x-text="totalSoal || 0"></p>
                                </div>
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

    @stack('scripts')
</body>
</html>
