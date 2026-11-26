<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Soal &amp; Kunci Jawaban — {{ $ujian->nama_ujian }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    {!! app('App\Services\FormulaRenderer')->getKaTeXScripts() !!}
    <style>
        @media print {
            @page {
                size: A4;
                margin: 1.5cm 1.2cm 1.5cm 1.2cm;
            }
            body {
                background: white !important;
                color: #0f172a !important;
                font-size: 10.5pt !important;
                line-height: 1.5 !important;
            }
            .print\:hidden {
                display: none !important;
            }
            .soal-item {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
        .print-image-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.125rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 700;
            background-color: #fef3c7;
            color: #78350f;
            border: 1px solid #fde68a;
        }
        @media print {
            .print-image-badge {
                background-color: #f1f5f9 !important;
                color: #334155 !important;
                border-color: #cbd5e1 !important;
            }
        }
    </style>
</head>
<body class="font-sans antialiased bg-slate-100 min-h-screen text-slate-800">
    @php
        $formulaRenderer = app('App\Services\FormulaRenderer');

        /**
         * Helper untuk merender konten teks soal/opsi/pembahasan:
         * - Render formula KaTeX
         * - Hapus tag <img> dan gantikan teks (Soal Bergambar) sesuai instruksi user
         */
        $renderPrintContent = function (?string $content) use ($formulaRenderer): string {
            if ($content === null || trim($content) === '') {
                return '';
            }
            $cleanHtml = $formulaRenderer->render($formulaRenderer->stripParagraphTags($content));
            
            // Ganti setiap tag <img> (atau entitasnya) dengan teks (Soal Bergambar)
            return preg_replace('/<img[^>]*>/i', ' <span class="print-image-badge">(Soal Bergambar)</span> ', $cleanHtml);
        };
    @endphp

    {{-- Top Action Bar (Hanya tampil di layar browser, tersembunyi saat dicetak) --}}
    <aside class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-sm print:hidden">
        <div class="max-w-5xl mx-auto px-4 h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-primary-50 text-primary-700 text-xs font-bold uppercase tracking-wider">
                    Pratinjau Cetak
                </span>
                <div class="min-w-0">
                    <h2 class="text-sm font-bold text-slate-900 truncate">{{ $ujian->nama_ujian }}</h2>
                    <p class="text-xs text-slate-500">Total {{ $ujianSoals->count() }} soal &middot; Tampilan 1 HTML utuh</p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
                <button type="button" onclick="window.print()" class="btn btn-primary btn-sm flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Cetak Sekarang (Print / PDF)</span>
                </button>
                <button type="button" onclick="window.close()" class="btn btn-ghost btn-sm text-slate-600">
                    Tutup
                </button>
            </div>
        </div>
    </aside>

    {{-- Halaman Dokumen Cetak --}}
    <main class="max-w-5xl mx-auto my-6 p-6 sm:p-10 bg-white rounded-2xl shadow-sm border border-slate-200 print:border-0 print:shadow-none print:m-0 print:p-0 print:max-w-none">
        {{-- Header Dokumen --}}
        <header class="border-b-2 border-slate-900 pb-5 mb-8">
            <div class="text-center mb-4">
                <h1 class="text-xl sm:text-2xl font-black uppercase tracking-wide text-slate-900">
                    Naskah Soal &amp; Kunci Jawaban Ujian
                </h1>
                <p class="text-base font-semibold text-primary-700 mt-1">
                    {{ $ujian->nama_ujian }}
                </p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-3 border-t border-slate-200 text-xs text-slate-600">
                <div>
                    <span class="block font-semibold text-slate-400 uppercase tracking-wider text-[10px]">Tipe Ujian</span>
                    <span class="font-bold text-slate-800">{{ $ujian->tipe_ujian === 'offline_kelas' ? 'Offline di Kelas' : 'Online Paket' }}</span>
                </div>
                <div>
                    <span class="block font-semibold text-slate-400 uppercase tracking-wider text-[10px]">Jumlah Soal</span>
                    <span class="font-bold text-slate-800">{{ $ujianSoals->count() }} Butir Soal</span>
                </div>
                <div>
                    <span class="block font-semibold text-slate-400 uppercase tracking-wider text-[10px]">Durasi Ujian</span>
                    <span class="font-bold text-slate-800">{{ $ujian->durasi_ujian ?? '-' }} Menit</span>
                </div>
                <div>
                    <span class="block font-semibold text-slate-400 uppercase tracking-wider text-[10px]">Token Ujian</span>
                    <span class="font-mono font-bold text-slate-800">{{ $ujian->token_ujian ?? '-' }}</span>
                </div>
            </div>
        </header>

        {{-- Daftar Soal (Semua nomor dalam 1 HTML utuh) --}}
        <div class="space-y-6">
            @forelse($ujianSoals as $index => $ujianSoal)
                @php
                    $soal = $ujianSoal->soal;
                    $subJenis = $soal->subIndikator?->subJenisUjian;
                    $subJenisNama = $subJenis?->nama_sub_jenis_ujian ?? 'Umum';
                    $subIndikatorNama = $soal->subIndikator?->nama_sub_indikator ?? null;
                    
                    $isTkp = ($subJenis?->sistem_penilaian === 'tiap_jawaban_ada_poin')
                        || str_contains(strtoupper($subJenisNama), 'TKP')
                        || ($soal->nilai_bobot_a > 0 && $soal->nilai_bobot_b > 0);

                    $kunciJawaban = strtoupper(trim((string) ($soal->kunci_jawaban ?? '')));
                    
                    // Cek apakah soal memiliki gambar (di field terpisah atau di dalam teks)
                    $hasGambar = !empty($soal->gambar_soal) || $soal->hasImages();
                @endphp

                <article class="soal-item pb-6 border-b border-slate-200 last:border-b-0 print:pb-5 print:mb-5">
                    {{-- Header Nomor & Kategori Soal --}}
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="w-7 h-7 rounded-full bg-primary-100 text-primary-800 font-extrabold flex items-center justify-center text-xs border border-primary-200 print:bg-slate-100 print:text-black print:border-black">
                                {{ $index + 1 }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700 tracking-wide uppercase border border-slate-200 print:border-slate-400">
                                {{ $subJenisNama }}@if($subIndikatorNama) &mdash; {{ $subIndikatorNama }}@endif
                            </span>
                            @if($isTkp)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                    TKP (Skala 1-5)
                                </span>
                            @endif
                        </div>

                        @if($hasGambar)
                            <span class="print-image-badge">
                                (Soal Bergambar)
                            </span>
                        @endif
                    </div>

                    {{-- Teks Soal (Gambar diganti dengan (Soal Bergambar)) --}}
                    <div class="text-slate-900 text-sm leading-relaxed mb-3 font-medium">
                        {!! $renderPrintContent($soal->soal) !!}
                        @if(!empty($soal->gambar_soal))
                            <div class="my-2">
                                <span class="print-image-badge">(Soal Bergambar)</span>
                            </div>
                        @endif
                    </div>

                    {{-- Opsi Jawaban (A - E) --}}
                    <div class="space-y-1.5 ml-1 sm:ml-4 mb-4">
                        @foreach(['A', 'B', 'C', 'D', 'E'] as $opsiKey)
                            @php
                                $teksOpsi = $soal->{'opsi_'.strtolower($opsiKey)};
                                $hasOpsiImage = !empty($soal->{'gambar_opsi_'.strtolower($opsiKey)});
                                
                                $bobotVal = $soal->{'nilai_bobot_'.strtolower($opsiKey)};
                                $bobot = null;
                                if ($bobotVal !== null && $bobotVal !== '') {
                                    $floatVal = (float) $bobotVal;
                                    $bobot = ($floatVal == (int) $floatVal) ? (int) $floatVal : $floatVal;
                                }

                                $isCorrect = ! $isTkp && ($opsiKey === $kunciJawaban);
                                $isMaxTkp = $isTkp && ($bobot === 5);
                            @endphp

                            @if($teksOpsi !== null && $teksOpsi !== '')
                                <div class="flex items-start justify-between gap-3 p-2 rounded-lg text-xs leading-relaxed border transition-colors {{ ($isCorrect || $isMaxTkp) ? 'bg-emerald-50/90 border-emerald-400 font-semibold text-emerald-950 print:bg-emerald-50 print:border-emerald-600' : 'bg-white border-transparent text-slate-700' }}">
                                    <div class="flex items-start gap-2 flex-1 min-w-0">
                                        <span class="font-bold {{ ($isCorrect || $isMaxTkp) ? 'text-emerald-800' : 'text-slate-600' }}">{{ $opsiKey }}.</span>
                                        <div class="flex-1 min-w-0">
                                            {!! $renderPrintContent($teksOpsi) !!}
                                            @if($hasOpsiImage)
                                                <span class="print-image-badge ml-1">(Soal Bergambar)</span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Keterangan Jawaban / Bobot --}}
                                    <div class="flex-shrink-0 flex items-center gap-1.5">
                                        @if($isCorrect)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-600 text-white shadow-2xs print:bg-emerald-700">
                                                &#10003; Kunci Jawaban
                                            </span>
                                        @elseif($isTkp && $bobot !== null)
                                            @if($isMaxTkp)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-600 text-white shadow-2xs print:bg-emerald-700">
                                                    &#10003; Bobot: 5 (Maksimal)
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200 print:border-slate-300">
                                                    Bobot: {{ $bobot }}
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    {{-- Kotak Kunci Jawaban & Pembahasan --}}
                    <div class="mt-3 p-3 rounded-lg border text-xs leading-relaxed {{ $isTkp ? 'bg-amber-50/40 border-amber-200' : 'bg-slate-50 border-slate-200' }} print:bg-white print:border-slate-300">
                        <div class="flex items-center gap-2 font-bold mb-1 {{ $isTkp ? 'text-amber-900' : 'text-slate-800' }}">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            @if(! $isTkp)
                                <span>KUNCI JAWABAN: <strong class="text-emerald-700 underline">{{ $kunciJawaban ?: '-' }}</strong></span>
                            @else
                                <span>PENILAIAN TKP: <span class="font-normal text-amber-800">Skala Poin 1 sampai 5</span></span>
                            @endif
                        </div>

                        @if(!empty($soal->pembahasan))
                            <div class="mt-1 text-slate-700">
                                <strong class="text-slate-900">Pembahasan:</strong>
                                <div class="mt-0.5">
                                    {!! $renderPrintContent($soal->pembahasan) !!}
                                    @if(!empty($soal->gambar_pembahasan))
                                        <div class="my-1.5">
                                            <span class="print-image-badge">(Soal Bergambar)</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </article>
            @empty
                <div class="py-12 text-center text-slate-500">
                    Belum ada soal pada ujian ini.
                </div>
            @endforelse
        </div>

        {{-- Footer Dokumen --}}
        <footer class="mt-10 pt-4 border-t border-slate-200 text-center text-xs text-slate-400">
            Dokumen ini dicetak otomatis dari Sistem Ujian Panritta pada {{ now()->translatedFormat('d F Y, H:i') }}.
        </footer>
    </main>
</body>
</html>
