<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Simulasi Ujian - {{ $ujian->nama_ujian }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 min-h-screen">
    @php
        // Siapkan data soal sebagai array untuk konsumsi Alpine.js
        $soalData = [];
        $globalIndex = 0;
        foreach ($ujianSoals as $ujianSoal) {
            $globalIndex++;
            $soal = $ujianSoal->soal;
            $opsi = [];
            foreach (['A', 'B', 'C', 'D', 'E'] as $huruf) {
                $teks = $soal->{'opsi_'.strtolower($huruf)};
                if ($teks !== null && $teks !== '') {
                    $opsi[] = ['huruf' => $huruf, 'teks' => strip_tags($teks), 'gambar' => $soal->{'gambar_opsi_'.strtolower($huruf)} ? \Illuminate\Support\Facades\Storage::url($soal->{'gambar_opsi_'.strtolower($huruf)}) : null];
                }
            }
            $soalData[] = [
                'id' => $ujianSoal->id,
                'nomor' => $globalIndex,
                'sub_jenis' => $soal->subIndikator?->subJenisUjian?->nama_sub_jenis_ujian ?? 'Umum',
                'sub_indikator' => $soal->subIndikator?->nama_sub_indikator ?? 'Tanpa Kategori',
                'teks' => $soal->soal,
                'gambar' => $soal->gambar_soal ? \Illuminate\Support\Facades\Storage::url($soal->gambar_soal) : null,
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
            {{-- Header sticky ujian: timer tiruan --}}
            <div class="flex items-center justify-between bg-white shadow-sm border border-slate-200 rounded-2xl px-5 py-4 mb-6">
                <div>
                    <h1 class="font-bold text-lg md:text-xl text-slate-800 line-clamp-1">{{ $ujian->nama_ujian }}</h1>
                    <p class="text-xs text-slate-500 mt-1">Tipe: {{ $ujian->tipe_ujian === 'offline_kelas' ? 'Offline' : 'Online' }} &middot; {{ $ujianSoals->count() }} Soal</p>
                </div>
                <div class="flex items-center gap-4 sm:gap-6">
                    <div class="text-right bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100 hidden sm:block">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Sisa Waktu (Tiruan)</span>
                        <span class="text-xl font-black tabular-nums tracking-tight leading-none text-slate-800">{{ $ujian->durasi_ujian ?? '00' }}:00</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-6 items-start relative">
                {{-- Bagian Kiri: Area 1 Soal --}}
                <div class="flex-1 w-full">
                    <template x-if="soalAktif">
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                            <div class="p-6 sm:p-8">
                                <div class="flex items-center gap-3 mb-5 pb-5 border-b border-slate-100">
                                    <span class="w-10 h-10 rounded-full bg-primary-100 text-primary-700 font-bold flex items-center justify-center text-lg border border-primary-200 shadow-sm flex-shrink-0" x-text="soalAktif.nomor"></span>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-600 tracking-wide uppercase" x-text="soalAktif.sub_jenis + ' — ' + soalAktif.sub_indikator"></span>
                                </div>

                                {{-- Teks Soal --}}
                                <div class="prose prose-slate max-w-none text-slate-800 text-base leading-relaxed" x-html="soalAktif.teks"></div>
                                <template x-if="soalAktif.gambar">
                                    <img :src="soalAktif.gambar" alt="Gambar soal" class="mt-4 max-h-80 rounded-xl border border-slate-200 shadow-sm">
                                </template>

                                {{-- Opsi Jawaban --}}
                                <div class="mt-8 space-y-3">
                                    <template x-for="op in soalAktif.opsi" :key="op.huruf">
                                        <label class="flex items-start gap-4 p-4 border rounded-xl cursor-pointer transition-colors"
                                               :class="jawaban[soalAktif.id] === op.huruf ? 'border-primary-500 bg-primary-50 shadow-sm ring-1 ring-primary-500' : 'border-slate-200 hover:bg-slate-50'">
                                            <div class="flex items-center h-6">
                                                <input type="radio" :value="op.huruf" x-model="jawaban[soalAktif.id]" class="w-5 h-5 text-primary-600 border-slate-300 focus:ring-primary-600">
                                            </div>
                                            <div class="flex-1 pt-0.5">
                                                <span class="text-base font-bold mr-2" :class="jawaban[soalAktif.id] === op.huruf ? 'text-primary-700' : 'text-slate-700'" x-text="op.huruf + '.'"></span>
                                                <span class="text-base" :class="jawaban[soalAktif.id] === op.huruf ? 'text-primary-900 font-medium' : 'text-slate-700'" x-text="op.teks"></span>
                                                <template x-if="op.gambar">
                                                    <img :src="op.gambar" class="mt-3 max-h-40 rounded-lg border border-slate-200">
                                                </template>
                                            </div>
                                        </label>
                                    </template>
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
                <div class="w-full lg:w-72 xl:w-80 flex-shrink-0 lg:sticky lg:top-24 z-10 order-first lg:order-last mb-6 lg:mb-0">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="bg-slate-50 py-3 px-5 border-b border-slate-200">
                            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                Navigasi Soal
                            </h3>
                        </div>
                        <div class="p-4 max-h-[55vh] overflow-y-auto">
                            @foreach($navGroups as $groupName => $items)
                                <div class="mb-5 last:mb-0">
                                    <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2.5">{{ $groupName }}</h4>
                                    <div class="grid grid-cols-5 gap-2">
                                        @foreach($items as $item)
                                            <button type="button" @click="goTo({{ $item['nomor'] - 1 }})"
                                                    class="w-full aspect-square flex items-center justify-center rounded-lg text-sm font-bold transition-all border"
                                                    :class="{
                                                        'bg-primary-500 text-white border-primary-600 shadow-sm': jawaban[{{ $item['id'] }}] && indexAktif !== {{ $item['nomor'] - 1 }},
                                                        'bg-warning-400 text-white border-warning-500': raguRagu[{{ $item['id'] }}] && !jawaban[{{ $item['id'] }}] && indexAktif !== {{ $item['nomor'] - 1 }},
                                                        'bg-slate-800 text-white border-slate-900 ring-2 ring-offset-1 ring-slate-800': indexAktif === {{ $item['nomor'] - 1 }},
                                                        'bg-white text-slate-600 border-slate-300 hover:bg-slate-50': !jawaban[{{ $item['id'] }}] && !raguRagu[{{ $item['id'] }}] && indexAktif !== {{ $item['nomor'] - 1 }}
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
                                <span class="w-4 h-4 rounded bg-slate-800 inline-block"></span> Posisi Saat Ini
                            </div>
                            <div class="flex items-center gap-2 text-xs font-medium text-slate-600">
                                <span class="w-4 h-4 rounded bg-primary-500 inline-block"></span> Sudah Dijawab
                            </div>
                            <div class="flex items-center gap-2 text-xs font-medium text-slate-600">
                                <span class="w-4 h-4 rounded bg-warning-400 inline-block"></span> Ragu-ragu
                            </div>
                            <div class="flex items-center gap-2 text-xs font-medium text-slate-600">
                                <span class="w-4 h-4 rounded bg-white border border-slate-300 inline-block"></span> Belum Dijawab
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
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
