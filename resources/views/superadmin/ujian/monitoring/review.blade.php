@extends('superadmin.layouts.app')

@section('title', 'Review Jawaban')

@section('breadcrumb')
    <a href="{{ route('superadmin.ujian.index') }}" class="text-secondary-500 hover:text-secondary-700">Manajemen Ujian</a>
    <span class="mx-2 text-secondary-400">/</span>
    <a href="{{ route('superadmin.ujian.monitoring.ranking', $ujian) }}" class="text-secondary-500 hover:text-secondary-700">Perankingan</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Review</span>
@endsection

@section('header')
    <div>
        <h1 class="text-2xl font-bold text-secondary-900">Review Jawaban — {{ $ujianPeserta->user?->name }}</h1>
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

    <div class="space-y-4">
        @foreach($ujianSoals as $index => $ujianSoal)
            @php
                $soal = $ujianSoal->soal;
                $jawaban = $jawabanMap[$ujianSoal->id] ?? null;
                $sistem = $soal->subIndikator?->subJenisUjian?->sistem_penilaian ?? 'benar_salah';
                $jawabanPeserta = $jawaban?->jawaban;
            @endphp
            <div class="card">
                <div class="card-body">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3 flex-1">
                            <span class="flex-shrink-0 w-8 h-8 rounded-full bg-secondary-100 text-secondary-700 font-semibold flex items-center justify-center text-sm">{{ $index + 1 }}</span>
                            <div class="flex-1">
                                <p class="text-xs text-secondary-400 mb-1">
                                    {{ $ujianSoal->jenisUjian?->nama_jenis_ujian }} &middot; {{ $soal->subIndikator?->nama_sub_indikator }}
                                    <span class="ml-1">(Sistem: {{ $sistem === 'benar_salah' ? 'Benar-Salah' : 'Tiap Jawaban Ada Poin' }})</span>
                                </p>
                                <div class="prose prose-sm max-w-none text-secondary-800">{!! $soal->soal !!}</div>

                                <div class="mt-3 space-y-1">
                                    @foreach(['A', 'B', 'C', 'D', 'E'] as $opsi)
                                        @php
                                            $opsiText = $soal->{'opsi_'.strtolower($opsi)};
                                            if ($opsiText === null || $opsiText === '') { continue; }
                                            $isKunci = $sistem === 'benar_salah' && $soal->kunci_jawaban === $opsi;
                                            $isJawaban = $jawabanPeserta === $opsi;
                                            $poin = $soal->{'nilai_bobot_'.strtolower($opsi)};
                                        @endphp
                                        <div class="flex items-center gap-2 text-sm px-3 py-1.5 rounded-lg
                                            @if($isKunci) bg-success-50 @elseif($isJawaban) bg-primary-50 @endif">
                                            <span class="font-medium text-secondary-700">{{ $opsi }}.</span>
                                            <span class="text-secondary-700">{{ $opsiText }}</span>
                                            @if($sistem === 'tiap_jawaban_ada_poin' && $poin !== null)
                                                <span class="text-xs text-secondary-400">(Poin {{ $poin }})</span>
                                            @endif
                                            @if($isKunci)<span class="text-xs text-success-600 font-medium ml-1">Kunci Jawaban</span>@endif
                                            @if($isJawaban)<span class="text-xs text-primary-600 font-medium ml-1">Jawaban Peserta</span>@endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            @if($sistem === 'benar_salah')
                                @if($jawaban && $jawaban->benar)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-success-100 text-success-700">BENAR ({{ $jawaban->nilai }} Poin)</span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-danger-100 text-danger-700">SALAH (0 Poin)</span>
                                @endif
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-secondary-100 text-secondary-700">Poin: {{ $jawaban->nilai ?? 0 }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
