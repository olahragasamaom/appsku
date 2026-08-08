@extends('peserta.layouts.app')

@section('title', 'Pembahasan Jawaban')

@section('content')
    <div class="max-w-3xl mx-auto">
        <a href="{{ route('peserta.ujian.hasil', $ujian) }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Kembali ke Hasil</a>

        <div class="card mt-4">
            <div class="card-body text-center">
                <h1 class="text-xl font-bold text-slate-800">Pembahasan — {{ $ujian->nama_ujian }}</h1>
                <p class="text-slate-500 text-sm mt-1">
                    {{ $peserta->user?->name ?? 'Peserta' }} &middot; Total Nilai:
                    <strong>{{ $peserta->total_nilai ?? 0 }}</strong>
                </p>
            </div>
        </div>

        <div class="space-y-4 mt-6">
            @foreach($ujianSoals as $index => $ujianSoal)
                @php
                    $soal = $ujianSoal->soal;
                    $jawaban = $jawabanMap[$ujianSoal->id] ?? null;
                    $sistem = $soal->subIndikator?->subJenisUjian?->sistem_penilaian ?? 'benar_salah';
                    $jawabanPeserta = $jawaban?->jawaban;
                    $isBenar = $sistem === 'benar_salah' ? (bool) ($jawaban?->benar) : null;
                @endphp
                <div class="card">
                    <div class="card-body">
                        <div class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-8 h-8 rounded-full bg-slate-100 text-slate-700 font-semibold flex items-center justify-center text-sm">{{ $index + 1 }}</span>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-xs text-slate-400">{{ $ujianSoal->jenisUjian?->nama_jenis_ujian }}</p>

                                    @if($sistem === 'benar_salah')
                                        @if($isBenar)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-success-100 text-success-700">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                Benar ({{ $jawaban->nilai }} poin)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-danger-100 text-danger-700">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                Salah (0 poin)
                                            </span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">{{ $jawaban->nilai ?? 0 }} poin</span>
                                    @endif
                                </div>

                                <div class="prose prose-sm max-w-none text-slate-800 mt-2">{!! $soal->soal !!}</div>

                                <div class="mt-3 space-y-1.5">
                                    @foreach(['A', 'B', 'C', 'D', 'E'] as $opsi)
                                        @php
                                            $opsiText = $soal->{'opsi_'.strtolower($opsi)};
                                            if ($opsiText === null || $opsiText === '') { continue; }
                                            $isKunci = $sistem === 'benar_salah' && $soal->kunci_jawaban === $opsi;
                                            $isJawaban = $jawabanPeserta === $opsi;
                                            $poin = $soal->{'nilai_bobot_'.strtolower($opsi)};
                                        @endphp
                                        <div class="flex items-center gap-2 text-sm px-3 py-2 rounded-lg border
                                            @if($isKunci) border-success-300 bg-success-50
                                            @elseif($isJawaban) border-danger-300 bg-danger-50
                                            @else border-slate-100 @endif">
                                            <span class="font-semibold text-slate-700 w-5">{{ $opsi }}.</span>
                                            <span class="text-slate-700 flex-1">{{ $opsiText }}</span>

                                            @if($sistem === 'tiap_jawaban_ada_poin' && $poin !== null)
                                                <span class="text-xs text-slate-400">({{ $poin }} poin)</span>
                                            @endif

                                            @if($isKunci)
                                                <span class="inline-flex items-center gap-1 text-xs text-success-700 font-medium">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    Kunci
                                                </span>
                                            @endif
                                            @if($isJawaban && ! $isKunci)
                                                <span class="inline-flex items-center gap-1 text-xs text-danger-700 font-medium">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    Jawaban Anda
                                                </span>
                                            @elseif($isJawaban && $isKunci)
                                                <span class="text-xs text-success-700 font-medium">Jawaban Anda</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                @if($sistem === 'benar_salah' && ! $isBenar)
                                    <p class="mt-2 text-sm text-slate-600">
                                        Jawaban seharusnya:
                                        <span class="font-semibold text-success-700">{{ $soal->kunci_jawaban ?? '-' }}</span>
                                    </p>
                                @endif

                                @if(! empty($soal->pembahasan))
                                    <div class="mt-3 border-t border-slate-100 pt-3">
                                        <p class="text-xs font-semibold text-slate-500 mb-1">Pembahasan</p>
                                        <div class="prose prose-sm max-w-none text-slate-700">{!! $soal->pembahasan !!}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
