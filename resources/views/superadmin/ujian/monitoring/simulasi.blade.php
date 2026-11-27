<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Simulasi Ujian - {{ $ujian->nama_ujian }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {!! app('App\Services\FormulaRenderer')->getKaTeXScripts() !!}
</head>
<body class="font-sans antialiased bg-slate-50 min-h-screen relative">
    {{-- Dekorasi background --}}
    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none" aria-hidden="true">
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-primary-200/40 rounded-full blur-3xl"></div>
        <div class="absolute top-1/3 -left-24 w-80 h-80 bg-indigo-200/30 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-1/4 w-72 h-72 bg-emerald-200/20 rounded-full blur-3xl"></div>
    </div>
    @php
        $formulaRenderer = app('App\Services\FormulaRenderer');
        // Siapkan data soal sebagai array untuk konsumsi Alpine.js
        $soalData = [];
        $globalIndex = 0;
        foreach ($ujianSoals as $ujianSoal) {
            $globalIndex++;
            $soal = $ujianSoal->soal;
            $subJenis = $soal->subIndikator?->subJenisUjian;
            $isTkp = ($subJenis?->sistem_penilaian === 'tiap_jawaban_ada_poin')
                || str_contains(strtoupper($subJenis?->nama_sub_jenis_ujian ?? ''), 'TKP')
                || ($soal->nilai_bobot_a > 0 && $soal->nilai_bobot_b > 0);

            $kunciJawaban = strtoupper(trim((string) ($soal->kunci_jawaban ?? '')));

            $opsi = [];
            foreach (['A', 'B', 'C', 'D', 'E'] as $huruf) {
                $teks = $soal->{'opsi_'.strtolower($huruf)};
                if ($teks !== null && $teks !== '') {
                    $bobotVal = $soal->{'nilai_bobot_'.strtolower($huruf)};
                    $bobot = null;
                    if ($bobotVal !== null && $bobotVal !== '') {
                        $floatVal = (float) $bobotVal;
                        $bobot = ($floatVal == (int) $floatVal) ? (int) $floatVal : $floatVal;
                    }

                    $gambarOpsi = $soal->{'gambar_opsi_'.strtolower($huruf)};
                    $gambarOpsiUrl = null;
                    if ($gambarOpsi) {
                        $gambarOpsiUrl = \Illuminate\Support\Facades\Storage::disk('uploads')->url($gambarOpsi);
                    }

                    $opsi[] = [
                        'huruf' => $huruf,
                        'teks' => $formulaRenderer->render($formulaRenderer->stripParagraphTags($teks)),
                        'gambar' => $gambarOpsiUrl,
                        'bobot' => $bobot,
                        'is_kunci' => ! $isTkp && ($huruf === $kunciJawaban),
                    ];
                }
            }

            $gambarSoalUrl = null;
            if ($soal->gambar_soal) {
                $gambarSoalUrl = \Illuminate\Support\Facades\Storage::disk('uploads')->url($soal->gambar_soal);
            }

            $pembahasanTeks = null;
            if (! empty($soal->pembahasan)) {
                $pembahasanTeks = $formulaRenderer->render($soal->pembahasan);
            }

            $gambarPembahasanUrl = null;
            if ($soal->gambar_pembahasan) {
                $gambarPembahasanUrl = \Illuminate\Support\Facades\Storage::disk('uploads')->url($soal->gambar_pembahasan);
            }

            $soalData[] = [
                'id' => $ujianSoal->id,
                'nomor' => $globalIndex,
                'sub_jenis' => $subJenis?->nama_sub_jenis_ujian ?? 'Umum',
                'sub_indikator' => $soal->subIndikator?->nama_sub_indikator ?? 'Tanpa Kategori',
                'is_tkp' => $isTkp,
                'kunci_jawaban' => $kunciJawaban,
                'teks' => $formulaRenderer->render($soal->soal ?? ''),
                'gambar' => $gambarSoalUrl,
                'pembahasan' => $pembahasanTeks,
                'gambar_pembahasan' => $gambarPembahasanUrl,
                'opsi' => $opsi,
            ];
        }

        // Kelompokkan nomor navigasi per sub jenis
        $navGroups = collect($soalData)->groupBy('sub_jenis');
    @endphp

    {{-- Data soal disimpan di script JSON terpisah agar aman dari konflik kutip/HTML --}}
    <script type="application/json" id="data-soal">{!! json_encode($soalData, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>

    <div x-data="simulasiEngine()">
        {{-- Header Tiruan --}}
        <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
            <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center px-2 py-1 rounded bg-warning-100 text-warning-700 text-xs font-bold uppercase tracking-wider">
                        Mode Simulasi
                    </span>
                    <span class="font-bold text-slate-800 hidden sm:inline">Portal Peserta (Tiruan)</span>
                </div>
                <button type="button" onclick="window.close()" class="btn btn-secondary btn-sm">Tutup Simulasi</button>
            </div>
        </header>

        <main class="max-w-6xl mx-auto px-4 py-6 pb-20">
            {{-- Header ujian --}}
            <div class="flex items-center justify-between bg-gradient-to-r from-primary-600 to-primary-700 shadow-lg shadow-primary-600/20 rounded-2xl px-5 py-4 mb-6">
                <div class="min-w-0">
                    <h1 class="font-bold text-lg md:text-xl text-white line-clamp-1">{{ $ujian->nama_ujian }}</h1>
                    <p class="text-xs text-primary-100 mt-1">Tipe: {{ $ujian->tipe_ujian === 'offline_kelas' ? 'Offline' : 'Online' }} &middot; {{ $ujianSoals->count() }} Soal</p>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-3 items-start relative">
                {{-- Bagian Kiri: Area 1 Soal --}}
                <div class="flex-1 w-full">
                    <template x-if="soalAktif">
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                            <div class="p-5 sm:p-6">
                                <div class="flex items-center gap-3 mb-5 pb-5 border-b border-slate-100">
                                    <span class="w-10 h-10 rounded-full bg-primary-100 text-primary-700 font-bold flex items-center justify-center text-lg border border-primary-200 shadow-sm flex-shrink-0" x-text="soalAktif.nomor"></span>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-600 tracking-wide uppercase" x-text="soalAktif.sub_jenis + ' — ' + soalAktif.sub_indikator"></span>
                                        <template x-if="soalAktif.is_tkp">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                                TKP (Skala 1-5)
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                {{-- Teks Soal --}}
                                <div class="prose prose-slate max-w-none text-slate-800 text-base leading-relaxed" x-html="soalAktif.teks"></div>
                                <template x-if="soalAktif.gambar">
                                    <img :src="soalAktif.gambar" alt="Gambar soal" class="mt-4 max-h-80 rounded-xl border border-slate-200 shadow-sm">
                                </template>

                                {{-- Opsi Jawaban --}}
                                <div class="mt-8 space-y-3">
                                    <template x-for="op in soalAktif.opsi" :key="op.huruf">
                                        <label class="flex items-start gap-4 p-4 rounded-xl cursor-pointer transition-all duration-150 relative"
                                               :style="{
                                                   'backgroundColor': ((!soalAktif.is_tkp && op.is_kunci) || (soalAktif.is_tkp && op.bobot === 5)) ? 'rgb(209, 250, 229)' : (jawaban[soalAktif.id] === op.huruf ? 'rgb(240, 249, 255)' : 'white'),
                                                   'borderWidth': '2px',
                                                   'borderColor': ((!soalAktif.is_tkp && op.is_kunci) || (soalAktif.is_tkp && op.bobot === 5)) ? 'rgb(5, 150, 105)' : (jawaban[soalAktif.id] === op.huruf ? 'rgb(59, 130, 246)' : 'rgb(203, 213, 225)'),
                                                   'boxShadow': ((!soalAktif.is_tkp && op.is_kunci) || (soalAktif.is_tkp && op.bobot === 5)) ? '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)' : '0 1px 2px 0 rgba(0, 0, 0, 0.05)'
                                               }">
                                            <div class="flex items-center h-6">
                                                <input type="radio" :value="op.huruf" x-model="jawaban[soalAktif.id]" 
                                                       class="w-5 h-5 text-primary-600 border-slate-300 focus:ring-primary-600">
                                            </div>
                                            <div class="flex-1 pt-0.5 min-w-0">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="flex-1 min-w-0">
                                                        <span class="text-base font-bold mr-2" 
                                                              :class="((!soalAktif.is_tkp && op.is_kunci) || (soalAktif.is_tkp && op.bobot === 5)) ? 'text-emerald-800' : (jawaban[soalAktif.id] === op.huruf ? 'text-primary-700' : 'text-slate-700')" 
                                                              x-text="op.huruf + '.'"></span>
                                                        <span class="text-base leading-relaxed" 
                                                              :class="((!soalAktif.is_tkp && op.is_kunci) || (soalAktif.is_tkp && op.bobot === 5)) ? 'text-emerald-950 font-medium' : (jawaban[soalAktif.id] === op.huruf ? 'text-primary-900 font-medium' : 'text-slate-700')" 
                                                              x-html="op.teks"></span>
                                                    </div>

                                                    {{-- Badges di belakang jawaban --}}
                                                    <div class="flex-shrink-0 flex items-center gap-2 pt-0.5">
                                                        {{-- Kunci Jawaban Non-TKP --}}
                                                        <template x-if="!soalAktif.is_tkp && op.is_kunci">
                                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs whitespace-nowrap">
                                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                                Jawaban Benar
                                                            </span>
                                                        </template>

                                                        {{-- Bobot TKP --}}
                                                        <template x-if="soalAktif.is_tkp && op.bobot !== null">
                                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold whitespace-nowrap shadow-2xs"
                                                                  :class="op.bobot === 5 ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700 border border-slate-200'">
                                                                <template x-if="op.bobot === 5">
                                                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                                </template>
                                                                <span>Bobot: <strong x-text="op.bobot"></strong></span>
                                                            </span>
                                                        </template>
                                                    </div>
                                                </div>

                                                <template x-if="op.gambar">
                                                    <img :src="op.gambar" class="mt-3 max-h-40 rounded-lg border border-slate-200">
                                                </template>
                                            </div>
                                        </label>
                                    </template>
                                </div>

                                {{-- Box Penjelasan / Pembahasan --}}
                                 <div class="mt-8 border border-yellow-300 rounded-2xl overflow-hidden shadow-xs bg-yellow-50">
                                    <div class="px-5 py-3 border-b border-yellow-300 flex items-center justify-between bg-yellow-100 text-yellow-900">
                                         <div class="flex items-center gap-2 font-bold text-sm">
                                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                            </svg>
                                            <span>Penjelasan & Pembahasan</span>
                                        </div>
                                        <template x-if="!soalAktif.is_tkp && soalAktif.kunci_jawaban">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-emerald-600 text-white shadow-xs">
                                                Kunci: <span x-text="soalAktif.kunci_jawaban"></span>
                                            </span>
                                        </template>
                                        <template x-if="soalAktif.is_tkp">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-amber-600 text-white shadow-xs">
                                                Sistem Poin TKP (Skala 1-5)
                                            </span>
                                        </template>
                                    </div>

                                    <div class="p-5 sm:p-6 space-y-4">
                                        <template x-if="soalAktif.pembahasan">
                                            <div class="prose prose-slate max-w-none text-slate-800 text-sm leading-relaxed" x-html="soalAktif.pembahasan"></div>
                                        </template>
                                        <template x-if="!soalAktif.pembahasan">
                                            <div class="text-xs text-slate-500 italic flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Penjelasan detail belum ditambahkan oleh pembuat soal untuk nomor ini.
                                            </div>
                                        </template>
                                        <template x-if="soalAktif.gambar_pembahasan">
                                            <img :src="soalAktif.gambar_pembahasan" alt="Gambar pembahasan" class="mt-3 max-h-72 rounded-xl border border-slate-200 shadow-sm">
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Tombol Navigasi Prev/Next --}}
                            <div class="bg-slate-50 border-t border-slate-100 px-6 py-4 flex items-center justify-between">
                                <button type="button" @click="prev()" :disabled="indexAktif === 0"
                                        class="btn btn-secondary" :class="indexAktif === 0 ? 'opacity-50 cursor-not-allowed' : ''">
                                    &larr; Sebelumnya
                                </button>
                                <button type="button" @click="ragukan()" class="btn btn-ghost text-warning-600 text-sm">Ragu-ragu</button>
                                <button type="button" @click="next()" :disabled="indexAktif === soalList.length - 1"
                                        class="btn btn-primary" :class="indexAktif === soalList.length - 1 ? 'opacity-50 cursor-not-allowed' : ''">
                                    Selanjutnya &rarr;
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Bagian Kanan: Navigasi Nomor --}}
                <div class="w-full lg:w-64 flex-shrink-0 lg:sticky lg:top-24 z-10 order-first lg:order-last mb-6 lg:mb-0">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                        <div class="bg-gradient-to-r from-slate-800 to-slate-700 py-3 px-5 border-b border-slate-200">
                            <h3 class="font-bold text-white text-sm flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                Navigasi Soal
                            </h3>
                        </div>
                        <div class="p-4 h-[48vh] overflow-y-auto">
                            @foreach($navGroups as $groupName => $items)
                                <div class="mb-5 last:mb-0">
                                    <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2.5">{{ $groupName }}</h4>
                                    <div class="grid grid-cols-5 gap-0.5">
                                         @foreach($items as $item)
                                             <button type="button" @click="goTo({{ $item['nomor'] - 1 }})"
                                                     class="w-full aspect-square flex items-center justify-center rounded text-[7px] font-bold transition-all"
                                                     :style="{
                                                         'backgroundColor': jawaban[{{ $item['id'] }}] ? 'rgb(59, 130, 246)' : (raguRagu[{{ $item['id'] }}] ? 'rgb(250, 204, 21)' : 'white'),
                                                         'color': jawaban[{{ $item['id'] }}] ? 'white' : (raguRagu[{{ $item['id'] }}] ? 'white' : 'rgb(55, 65, 81)'),
                                                         'borderWidth': indexAktif === {{ $item['nomor'] - 1 }} ? '3px' : '2px',
                                                         'borderStyle': 'solid',
                                                         'borderColor': indexAktif === {{ $item['nomor'] - 1 }} ? 'rgb(37, 99, 235)' : (raguRagu[{{ $item['id'] }}] ? 'rgb(202, 138, 4)' : (jawaban[{{ $item['id'] }}] ? 'rgb(29, 78, 216)' : 'rgb(209, 213, 219)'))
                                                     }">
                                                 {{ $item['nomor'] }}
                                             </button>
                                         @endforeach
                                     </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="bg-slate-50 p-4 border-t border-slate-200 space-y-2">
                            <div class="flex items-center gap-2 text-xs font-medium text-slate-600">
                                <span class="w-4 h-4 rounded inline-block" style="background-color: white; border: 3px solid rgb(37, 99, 235);"></span> Soal Aktif
                            </div>
                            <div class="flex items-center gap-2 text-xs font-medium text-slate-600">
                                <span class="w-4 h-4 rounded inline-block" style="background-color: rgb(59, 130, 246); border: 2px solid rgb(29, 78, 216);"></span> Sudah Dijawab
                            </div>
                            <div class="flex items-center gap-2 text-xs font-medium text-slate-600">
                                <span class="w-4 h-4 rounded inline-block" style="background-color: rgb(250, 204, 21); border: 2px solid rgb(202, 138, 4);"></span> Ragu-ragu
                            </div>
                            <div class="flex items-center gap-2 text-xs font-medium text-slate-600">
                                <span class="w-4 h-4 rounded inline-block" style="background-color: white; border: 2px solid rgb(209, 213, 219);"></span> Belum Dijawab
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        {{-- Timer Floating tiruan: fixed kanan bawah --}}
        <div class="fixed bottom-5 right-5 z-40">
            <div class="flex flex-col items-center rounded-2xl px-5 py-3 shadow-xl border bg-white border-slate-200 shadow-slate-400/20">
                <span class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider mb-0.5 text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Sisa Waktu (Tiruan)
                </span>
                <span class="text-2xl font-black tabular-nums tracking-tight leading-none text-slate-800">{{ $ujian->durasi_ujian ?? '00' }}:00</span>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('simulasiEngine', () => ({
                soalList: JSON.parse(document.getElementById('data-soal').textContent),
                indexAktif: 0,
                jawaban: {},
                raguRagu: {},
                get soalAktif() {
                    return this.soalList[this.indexAktif] ?? null;
                },
                init() {
                    const renderMath = () => {
                        this.$nextTick(() => {
                            requestAnimationFrame(() => {
                                if (typeof window.renderMathFormulas === 'function') {
                                    window.renderMathFormulas();
                                }
                            });
                        });
                    };

                    this.$watch('indexAktif', () => renderMath());
                    renderMath();
                },
                goTo(index) {
                    this.indexAktif = index;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                next() {
                    if (this.indexAktif < this.soalList.length - 1) this.goTo(this.indexAktif + 1);
                },
                prev() {
                    if (this.indexAktif > 0) this.goTo(this.indexAktif - 1);
                },
                ragukan() {
                    const id = this.soalAktif.id;
                    this.raguRagu[id] = !this.raguRagu[id];
                }
            }));
        });
    </script>
</body>
</html>