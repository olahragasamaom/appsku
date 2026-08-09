@extends('superadmin.layouts.app')

@section('title', 'Review Jawaban')

@section('breadcrumb')
    <a href="{{ route('superadmin.ujian.index') }}" class="text-secondary-500 hover:text-secondary-700">Manajemen Ujian</a>
    <span class="mx-2 text-secondary-400">/</span>
    <a href="{{ route('superadmin.ujian.monitoring.ranking', $ujian) }}" class="text-secondary-500 hover:text-secondary-700">Perankingan</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Review Jawaban</span>
@endsection

@section('header')
    <div>
        <h1 class="text-2xl font-bold text-secondary-900">Review Jawaban — {{ $ujianPeserta->user?->name ?? $ujianPeserta->pesertaOffline?->nama_peserta ?? 'Peserta' }}</h1>
        <p class="text-secondary-500 mt-1">{{ $ujian->nama_ujian }} &middot; Total Nilai: <strong>{{ $ujianPeserta->total_nilai ?? 0 }}</strong></p>
    </div>
@endsection

@section('content')
    {{-- Breakdown per jenis --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        @foreach($breakdown as $row)
            <div class="card">
                <div class="card-body">
                    <p class="text-sm text-secondary-500">{{ $row['nama'] }}</p>
                    <p class="text-2xl font-bold text-secondary-900">{{ $row['nilai'] }}</p>
                    <p class="text-xs mt-1">
                        Passing Grade: {{ $row['passing_grade'] ?? '-' }}
                        @if($row['lulus'] === true)
                            <span class="text-success-600 font-medium">&middot; Lulus</span>
                        @elseif($row['lulus'] === false)
                            <span class="text-danger-600 font-medium">&middot; Tidak Lulus</span>
                        @endif
                    </p>
                </div>
            </div>
        @endforeach
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
                                <p class="text-xs text-slate-500 font-medium">{{ $ujianSoal->jenisUjian?->nama_jenis_ujian }} &mdash; {{ $soal->subIndikator?->subJenisUjian?->nama_sub_jenis_ujian }}</p>

                                @if($sistem === 'benar_salah')
                                    @if($isBenar)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-success-100 text-success-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            Benar ({{ $jawaban->nilai }} poin)
                                        </span>
                                    @elseif($jawabanPeserta)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-danger-100 text-danger-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Salah (0 poin)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                            Tidak Dijawab
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">{{ $jawaban->nilai ?? 0 }} poin</span>
                                @endif
                            </div>

                            <div class="prose prose-sm max-w-none text-slate-800 mt-3">{!! $soal->soal !!}</div>

                            @if($soal->gambar_soal)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($soal->gambar_soal) }}" class="mt-3 max-h-64 rounded-lg border border-slate-200">
                            @endif

                            <div class="mt-4 space-y-1.5">
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
                                        <span class="text-slate-700 flex-1">{!! strip_tags($opsiText) !!}</span>

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
                                                Jawaban Peserta
                                            </span>
                                        @elseif($isJawaban && $isKunci)
                                            <span class="text-xs text-success-700 font-medium">Jawaban Peserta</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            @if($sistem === 'benar_salah' && ! $isBenar)
                                <p class="mt-3 text-sm text-slate-600 bg-slate-50 p-3 rounded-lg border border-slate-100">
                                    Jawaban yang seharusnya benar:
                                    <strong class="text-success-700 text-base ml-1">{{ $soal->kunci_jawaban ?? '-' }}</strong>
                                </p>
                            @endif

                            @if(! empty($soal->pembahasan))
                                <div class="mt-4 border-t border-slate-100 pt-3">
                                    <p class="text-xs font-semibold text-slate-500 mb-1">Pembahasan:</p>
                                    <div class="prose prose-sm max-w-none text-slate-700">{!! $soal->pembahasan !!}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
