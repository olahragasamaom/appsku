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
                heartbeatUrl: config.heartbeatUrl,
                hasilUrl: config.hasilUrl,
                deadlineTimestamp: config.deadlineTimestamp, // unix timestamp (seconds) saat deadline
                serverTimestamp: config.serverTimestamp,     // unix timestamp (seconds) saat page dirender di server
                clientBaseTimestamp: Math.floor(Date.now() / 1000), // unix timestamp (seconds) saat page diload di client
                sisaDetik: null,
                submitFormId: config.submitFormId,
                jawaban: config.initialJawaban || {},
                ragu: {},
                raguKey: config.raguKey || 'ragu_ujian_default',
                currentSoalIndex: config.currentSoalIndex || 0,
                totalSoal: config.totalSoal || 0,
                saving: false,
                lastSaved: false,
                timer: null,
                heartbeatTimer: null,
                expired: false,

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

                    // Hitung sisa detik awal dari deadline timestamp
                    this.recalculateSisaDetik();

                    if (this.deadlineTimestamp !== null) {
                        // Timer tick setiap 1 detik - recalculate dari timestamp absolut
                        // Ini akan tetap akurat meskipun browser di-sleep/suspend
                        this.timer = setInterval(() => {
                            this.recalculateSisaDetik();
                            if (this.sisaDetik <= 0 && !this.expired) {
                                this.expired = true;
                                clearInterval(this.timer);
                                this.autoSubmitOnTimeout();
                            }
                        }, 1000);

                        // Heartbeat ke server setiap 30 detik - verifikasi deadline + sinkronisasi waktu
                        this.heartbeatTimer = setInterval(() => this.heartbeat(), 30000);

                        // Recalculate saat tab aktif lagi (setelah sleep/switch tab)
                        document.addEventListener('visibilitychange', () => {
                            if (document.visibilityState === 'visible') {
                                this.recalculateSisaDetik();
                                this.heartbeat();
                            }
                        });

                        // Recalculate saat window focus kembali
                        window.addEventListener('focus', () => {
                            this.recalculateSisaDetik();
                            this.heartbeat();
                        });
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

                /**
                 * Recalculate sisa detik berdasarkan timestamp absolut.
                 * Formula: deadline - (serverTime + elapsedSinceLoad)
                 * elapsedSinceLoad = waktu yang berlalu di client sejak page load (akurat untuk pause/sleep)
                 */
                recalculateSisaDetik() {
                    if (this.deadlineTimestamp === null) {
                        this.sisaDetik = null;
                        return;
                    }
                    const nowClient = Math.floor(Date.now() / 1000);
                    const elapsedClient = nowClient - this.clientBaseTimestamp;
                    const estimatedServerNow = this.serverTimestamp + elapsedClient;
                    const sisa = this.deadlineTimestamp - estimatedServerNow;
                    this.sisaDetik = Math.max(0, sisa);
                },

                /**
                 * Heartbeat ke server untuk verifikasi deadline dan sinkronisasi waktu.
                 * Jika server bilang expired, redirect ke halaman hasil.
                 */
                async heartbeat() {
                    if (this.expired || !this.heartbeatUrl) return;
                    try {
                        const res = await fetch(this.heartbeatUrl, {
                            headers: { 'Accept': 'application/json' },
                            cache: 'no-store',
                        });
                        if (!res.ok) {
                            const data = await res.json().catch(() => ({}));
                            if (data.redirect) {
                                this.expired = true;
                                clearInterval(this.timer);
                                clearInterval(this.heartbeatTimer);
                                window.location.href = data.redirect;
                            }
                            return;
                        }
                        const data = await res.json();
                        if (data.expired) {
                            this.expired = true;
                            clearInterval(this.timer);
                            clearInterval(this.heartbeatTimer);
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            }
                            return;
                        }
                        // Sinkronisasi waktu server (koreksi clock drift)
                        if (data.server_time && data.sisa_detik !== undefined && data.sisa_detik !== null) {
                            this.serverTimestamp = data.server_time;
                            this.clientBaseTimestamp = Math.floor(Date.now() / 1000);
                            this.deadlineTimestamp = data.server_time + data.sisa_detik;
                            this.recalculateSisaDetik();
                        }
                    } catch (e) {
                        console.warn('Heartbeat gagal:', e);
                    }
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
                        const res = await fetch(this.saveUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ ujian_soal_id: ujianSoalId, jawaban: jawaban }),
                        });

                        if (!res.ok) {
                            const data = await res.json().catch(() => ({}));
                            if (data.expired) {
                                this.expired = true;
                                clearInterval(this.timer);
                                clearInterval(this.heartbeatTimer);
                                alert(data.message || 'Batas waktu ujian telah habis.');
                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                }
                                return;
                            }
                        }
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
    {!! app('App\Services\FormulaRenderer')->getKaTeXScripts() !!}
</head>
<body class="font-sans antialiased bg-slate-50 min-h-screen">
    @php
        $soalGroups = $ujianSoals->groupBy(function($item) {
            return $item->soal?->subIndikator?->subJenisUjian?->nama_sub_jenis_ujian ?? 'Umum';
        });
    @endphp

    @php
        $deadlineTimestamp = null;
        if ($sisaDetik !== null) {
            $deadlineTimestamp = now()->addSeconds($sisaDetik)->timestamp;
        }
    @endphp

    <div x-data="examEngine({
            saveUrl: '{{ route('peserta.ujian.jawaban', $ujian) }}',
            heartbeatUrl: '{{ route('peserta.ujian.heartbeat', $ujian) }}',
            hasilUrl: '{{ route('peserta.ujian.hasil', $ujian) }}',
            deadlineTimestamp: {{ $deadlineTimestamp === null ? 'null' : $deadlineTimestamp }},
            serverTimestamp: {{ now()->timestamp }},
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
                @php
                    $namaPeserta = $peserta->pesertaOffline?->nama_peserta ?? $peserta->user?->name ?? 'Peserta';
                    $nomorPeserta = $peserta->pesertaOffline?->nomor_peserta ?? null;
                @endphp
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    {{-- Left: Logo & Title --}}
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-primary-600 to-primary-800 flex items-center justify-center text-white font-bold text-lg shadow-sm">P</div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h1 class="font-bold text-base md:text-lg text-slate-900 line-clamp-1">{{ $ujian->nama_ujian }}</h1>
                            <p class="text-xs text-slate-500 flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span class="font-medium text-slate-700">{{ $namaPeserta }}</span>
                                </span>
                                @if($nomorPeserta)
                                    <span class="text-slate-400">•</span>
                                    <span>No: <span class="font-mono font-medium text-slate-700">{{ $nomorPeserta }}</span></span>
                                @endif
                            </p>
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

                        {{-- Buttons --}}
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('peserta.logout') }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="btn btn-secondary whitespace-nowrap text-sm"
                                        title="Keluar sementara, waktu tetap berjalan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    <span class="hidden sm:inline">Keluar</span>
                                </button>
                            </form>
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
            </div>
        </header>

        <div class="max-w-5xl mx-auto px-4 py-6 pb-20">
            <div class="flex flex-col lg:flex-row gap-2 items-start relative">
                {{-- Main Content (Soal) --}}
                <div class="flex-1 w-full space-y-3">
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
                            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                                {{-- Content --}}
                                <div class="p-3 sm:p-4">
                                    {{-- Header dengan kategori & Nomor (Gaya Simulasi) --}}
                                    <div class="flex items-center gap-1.5 mb-3 pb-2.5 border-b border-slate-100">
                                        <span class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 font-bold flex items-center justify-center text-sm border border-primary-200 shadow-sm flex-shrink-0">
                                            {{ $index + 1 }}
                                        </span>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            @php
                                                $subJenis = $soal->subIndikator?->subJenisUjian;
                                                $subJenisNama = $subJenis?->nama_sub_jenis_ujian ?? 'Umum';
                                                $subIndikatorNama = $soal->subIndikator?->nama_sub_indikator ?? null;
                                                $isTkp = ($subJenis?->sistem_penilaian === 'tiap_jawaban_ada_poin')
                                                    || str_contains(strtoupper($subJenisNama), 'TKP')
                                                    || ($soal->nilai_bobot_a > 0 && $soal->nilai_bobot_b > 0);
                                            @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-semibold bg-slate-100 text-slate-600 tracking-wide uppercase">
                                            {{ $subJenisNama }}@if($subIndikatorNama) &mdash; {{ $subIndikatorNama }}@endif
                                        </span>
                                        @if($isTkp)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                                    TKP (Skala 1-5)
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Teks Soal --}}
                                    <div class="mb-4">
                                        @if($soal->soal)
                                            <div class="prose prose-sm prose-slate max-w-none text-slate-800 leading-relaxed text-base">
                                                {!! app('App\Services\FormulaRenderer')->render($soal->soal) !!}
                                            </div>
                                        @endif
                                        @if($soal->gambar_soal)
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($soal->gambar_soal) }}" 
                                                 alt="Gambar soal" 
                                                 class="mt-6 max-h-96 rounded-lg border border-slate-200 shadow-sm">
                                        @endif
                                    </div>

                                    {{-- Opsi Jawaban --}}
                                    <div class="mt-4 space-y-1.5">
                                        <x-soal-options-display :ujianSoal="$ujianSoal" :jawaban="$jawaban" :ujianPeserta="$peserta" />
                                    </div>

                                    {{-- Navigation --}}
                                    <div class="flex flex-col sm:flex-row gap-2 justify-between pt-4 border-t border-slate-100 mt-4">
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
                <div class="w-full lg:w-64 flex-shrink-0 lg:sticky lg:top-24 z-10 order-first lg:order-last mb-6 lg:mb-0">
                    <div class="space-y-3">
                        {{-- Navigation Panel --}}
                         <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                             <div class="bg-gradient-to-r from-slate-800 to-slate-700 py-3 px-5 border-b border-slate-200">
                                 <h3 class="font-bold text-white text-sm flex items-center gap-2">
                                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                     </svg>
                                     Navigasi Soal
                                 </h3>
                             </div>
                             <div class="p-4 h-[48vh] overflow-y-auto">
                                 @php 
                                     $navIndex = 0;
                                 @endphp
                                 @foreach($soalGroups as $groupName => $items)
                                     <div class="mb-5 last:mb-0">
                                         <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2.5">{{ $groupName }}</h4>
                                         <div class="grid grid-cols-5 gap-0.5">
                                             @foreach($items as $ujianSoal)
                                                 @php $idxNow = $navIndex; @endphp
                                                  <button type="button" 
                                                          @click="currentSoalIndex = {{ $idxNow }}"
                                                          class="w-full aspect-square flex items-center justify-center rounded text-[7px] font-bold transition-all"
                                                          :style="{
                                                               'backgroundColor': jawaban[{{ $ujianSoal->id }}] ? 'rgb(59, 130, 246)' : (ragu[{{ $ujianSoal->id }}] ? 'rgb(250, 204, 21)' : 'white'),
                                                               'color': jawaban[{{ $ujianSoal->id }}] ? 'white' : (ragu[{{ $ujianSoal->id }}] ? 'white' : 'rgb(55, 65, 81)'),
                                                               'borderWidth': currentSoalIndex === {{ $idxNow }} ? '3px' : '2px',
                                                               'borderColor': currentSoalIndex === {{ $idxNow }} ? 'rgb(37, 99, 235)' : (ragu[{{ $ujianSoal->id }}] ? 'rgb(202, 138, 4)' : (jawaban[{{ $ujianSoal->id }}] ? 'rgb(29, 78, 216)' : 'rgb(209, 213, 219)'))
                                                           }">
                                                     {{ $navIndex + 1 }}
                                                 </button>
                                                 @php $navIndex++; @endphp
                                             @endforeach
                                         </div>
                                     </div>
                                 @endforeach
                             </div>
                             <div class="border-t border-slate-200 px-4 py-3 flex gap-2">
                                 <button type="button"
                                         @click="currentSoalIndex = Math.max(0, currentSoalIndex - 1)"
                                         :disabled="currentSoalIndex === 0"
                                         class="flex-1 btn btn-sm bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 disabled:opacity-50 disabled:cursor-not-allowed"
                                         title="Soal Sebelumnya">
                                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                     </svg>
                                 </button>
                                 <button type="button"
                                         @click="currentSoalIndex = Math.min(totalSoal - 1, currentSoalIndex + 1)"
                                         :disabled="currentSoalIndex === totalSoal - 1"
                                         class="flex-1 btn btn-sm bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 disabled:opacity-50 disabled:cursor-not-allowed"
                                         title="Soal Selanjutnya">
                                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                     </svg>
                                 </button>
                             </div>
                         </div>

                        {{-- Legend --}}
                         <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                             <h4 class="font-bold text-slate-900 text-sm mb-3">Keterangan</h4>
                             <div class="space-y-2">
                                 <div class="flex items-center gap-2">
                                     <div class="w-4 h-4 rounded" style="background-color: white; border: 3px solid rgb(37, 99, 235);"></div>
                                     <span class="text-xs text-slate-600">Soal Aktif</span>
                                 </div>
                                 <div class="flex items-center gap-2">
                                     <div class="w-4 h-4 rounded" style="background-color: rgb(59, 130, 246); border: 2px solid rgb(29, 78, 216);"></div>
                                     <span class="text-xs text-slate-600">Sudah Dijawab</span>
                                 </div>
                                 <div class="flex items-center gap-2">
                                     <div class="w-4 h-4 rounded" style="background-color: rgb(250, 204, 21); border: 3px solid rgb(202, 138, 4);"></div>
                                     <span class="text-xs text-slate-600">Ragu-Ragu</span>
                                 </div>
                                 <div class="flex items-center gap-2">
                                     <div class="w-4 h-4 rounded" style="background-color: white; border: 2px solid rgb(209, 213, 219);"></div>
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
