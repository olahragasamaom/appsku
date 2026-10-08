@extends('superadmin.layouts.app')

@section('title', 'Review Jawaban - '.$ujian->nama_ujian)

@section('breadcrumb')
    <a href="{{ route('superadmin.hasil-ujian.index') }}" class="text-secondary-500 hover:text-secondary-700">Hasil Ujian</a>
    <span class="mx-2 text-secondary-400">/</span>
    <a href="{{ route('superadmin.hasil-ujian.show', $ujian) }}" class="text-secondary-500 hover:text-secondary-700">{{ $ujian->nama_ujian }}</a>
    <span class="mx-2 text-secondary-400">/</span>
    <a href="{{ route('superadmin.hasil-ujian.peserta-list', $ujian) }}" class="text-secondary-500 hover:text-secondary-700">Daftar Peserta</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Review Jawaban</span>
@endsection

@php
    $namaPeserta = $peserta->user?->name ?? $peserta->pesertaOffline?->nama_peserta ?? 'Peserta';
    $nomorPeserta = $peserta->pesertaOffline?->nomor_peserta ?? '';
@endphp

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h2 class="font-semibold text-xl text-secondary-800">Review Jawaban</h2>
            <p class="text-sm text-secondary-500">
                <strong>{{ $namaPeserta }}</strong>
                @if($nomorPeserta) <span class="text-secondary-400">•</span> No: <span class="font-mono">{{ $nomorPeserta }}</span>@endif
                <span class="text-secondary-400">•</span> {{ $ujian->nama_ujian }}
            </p>
        </div>
        <a href="{{ route('superadmin.hasil-ujian.peserta-list', $ujian) }}" class="btn btn-ghost">Kembali</a>
    </div>
@endsection

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">
        {{-- Summary Card --}}
        <div class="card">
            <div class="card-body">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs text-secondary-500 mb-1">Status</p>
                        @if($peserta->status === 'selesai')
                            <x-badge type="success">Selesai</x-badge>
                        @elseif($peserta->status === 'sedang_ujian')
                            <x-badge type="warning">Sedang Ujian</x-badge>
                        @else
                            <x-badge type="secondary">{{ $peserta->status }}</x-badge>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-secondary-500 mb-1">Total Nilai</p>
                        <p class="text-xl font-bold text-secondary-900">{{ $peserta->total_nilai !== null ? number_format($peserta->total_nilai, 2) : '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-500 mb-1">Kelulusan</p>
                        @if($peserta->lulus === true)
                            <x-badge type="success">✓ Lulus</x-badge>
                        @elseif($peserta->lulus === false)
                            <x-badge type="danger">✕ Tidak Lulus</x-badge>
                        @else
                            <x-badge type="secondary">-</x-badge>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-secondary-500 mb-1">Waktu Mulai</p>
                        <p class="text-sm text-secondary-700">{{ $peserta->waktu_mulai?->format('d M Y H:i') ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Breakdown per Jenis --}}
        @if(count($breakdown) > 0)
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($breakdown as $row)
                    <div class="card">
                        <div class="card-body">
                            <p class="text-sm text-secondary-500">{{ $row['nama'] }}</p>
                            <p class="text-2xl font-bold text-secondary-900">{{ number_format($row['nilai'], 2) }}</p>
                            <p class="text-xs mt-1">
                                Passing Grade: {{ $row['passing_grade'] ?? '-' }}
                                @if($row['lulus'] === true)
                                    <span class="text-success-600 font-medium">• Lulus</span>
                                @elseif($row['lulus'] === false)
                                    <span class="text-danger-600 font-medium">• Tidak Lulus</span>
                                @endif
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Soal List --}}
        <div class="space-y-4">
            @foreach($ujianSoals as $index => $ujianSoal)
                @php
                    $soal = $ujianSoal->soal;
                    $jawaban = $jawabanMap[$ujianSoal->id] ?? null;
                    $sistem = $soal?->subIndikator?->subJenisUjian?->sistem_penilaian ?? 'benar_salah';
                    $jawabanPeserta = $jawaban?->jawaban;
                    $isBenar = $sistem === 'benar_salah' ? (bool) ($jawaban?->benar) : null;
                    $belumDijawab = $jawabanPeserta === null;

                    // Card border color
                    $borderColor = 'border-l-slate-300';
                    if ($sistem === 'benar_salah') {
                        if ($belumDijawab) {
                            $borderColor = 'border-l-slate-400';
                        } elseif ($isBenar) {
                            $borderColor = 'border-l-success-500';
                        } else {
                            $borderColor = 'border-l-danger-500';
                        }
                    } else {
                        $borderColor = $belumDijawab ? 'border-l-slate-400' : 'border-l-primary-500';
                    }
                @endphp

                <div class="card border-l-4 {{ $borderColor }}">
                    <div class="card-body">
                        <div class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-10 h-10 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center">{{ $index + 1 }}</span>

                            <div class="flex-1 min-w-0">
                                {{-- Header: Jenis + Status --}}
                                <div class="flex items-center justify-between gap-3 flex-wrap">
                                    <p class="text-xs text-slate-500 font-medium">
                                        {{ $ujianSoal->jenisUjian?->nama_jenis_ujian ?? 'N/A' }}
                                        @if($soal?->subIndikator?->subJenisUjian?->nama_sub_jenis_ujian)
                                            — {{ $soal->subIndikator->subJenisUjian->nama_sub_jenis_ujian }}
                                        @endif
                                    </p>

                                    @if($sistem === 'benar_salah')
                                        @if($belumDijawab)
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-slate-200 text-slate-700">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093M12 17h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Belum Dijawab
                                            </span>
                                        @elseif($isBenar)
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-success-100 text-success-700">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                Benar ({{ number_format($jawaban->nilai, 2) }} poin)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-danger-100 text-danger-700">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                Salah (0 poin)
                                            </span>
                                        @endif
                                    @else
                                        {{-- Sistem bobot (TKP) --}}
                                        @if($belumDijawab)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-200 text-slate-700">Belum Dijawab</span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-primary-100 text-primary-700">{{ number_format($jawaban->nilai, 2) }} poin</span>
                                        @endif
                                    @endif
                                </div>

                                {{-- Soal --}}
                                <div class="prose prose-sm max-w-none text-slate-800 mt-3">{!! $soal?->soal !!}</div>

                                @if($soal?->gambar_soal)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($soal->gambar_soal) }}" class="mt-3 max-h-64 rounded-lg border border-slate-200">
                                @endif

                                {{-- Opsi Jawaban --}}
                                <div class="mt-4 space-y-2">
                                    @foreach(['A', 'B', 'C', 'D', 'E'] as $opsi)
                                        @php
                                            $opsiText = $soal?->{'opsi_'.strtolower($opsi)};
                                            if ($opsiText === null || $opsiText === '') { continue; }
                                            $isKunci = $sistem === 'benar_salah' && $soal->kunci_jawaban === $opsi;
                                            $isJawaban = $jawabanPeserta === $opsi;
                                            $poin = $soal->{'nilai_bobot_'.strtolower($opsi)};

                                            // Styling classes
                                            $classes = 'border-slate-200 bg-white';
                                            if ($isKunci && $isJawaban) {
                                                $classes = 'border-success-400 bg-success-50 ring-2 ring-success-200';
                                            } elseif ($isKunci) {
                                                $classes = 'border-success-300 bg-success-50';
                                            } elseif ($isJawaban) {
                                                $classes = 'border-danger-400 bg-danger-50 ring-2 ring-danger-200';
                                            }
                                        @endphp
                                        <div class="flex items-center gap-3 text-sm px-3 py-2.5 rounded-lg border {{ $classes }}">
                                            <span class="font-bold text-slate-700 w-6">{{ $opsi }}.</span>
                                            <span class="text-slate-700 flex-1">{!! strip_tags($opsiText) !!}</span>

                                            @if($sistem !== 'benar_salah' && $poin !== null)
                                                <span class="text-xs font-semibold text-primary-700 bg-primary-100 px-2 py-0.5 rounded">{{ number_format($poin, 2) }} poin</span>
                                            @endif

                                            @if($isKunci)
                                                <span class="inline-flex items-center gap-1 text-xs text-success-700 font-semibold bg-success-100 px-2 py-0.5 rounded">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    Kunci
                                                </span>
                                            @endif
                                            @if($isJawaban && ! $isKunci && $sistem === 'benar_salah')
                                                <span class="inline-flex items-center gap-1 text-xs text-danger-700 font-semibold bg-danger-100 px-2 py-0.5 rounded">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    Jawaban Peserta
                                                </span>
                                            @elseif($isJawaban && $isKunci)
                                                <span class="text-xs text-success-700 font-semibold">Jawaban Peserta</span>
                                            @elseif($isJawaban && $sistem !== 'benar_salah')
                                                <span class="inline-flex items-center gap-1 text-xs text-primary-700 font-semibold bg-primary-100 px-2 py-0.5 rounded">
                                                    Jawaban Peserta
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Jawaban yang seharusnya (jika salah atau belum dijawab) --}}
                                @if($sistem === 'benar_salah' && (! $isBenar || $belumDijawab))
                                    <div class="mt-4 p-3 rounded-lg bg-success-50 border border-success-200">
                                        <p class="text-sm text-success-800">
                                            <span class="font-semibold">Jawaban yang seharusnya:</span>
                                            <strong class="text-success-900 text-base ml-1">{{ $soal->kunci_jawaban ?? '-' }}</strong>
                                        </p>
                                    </div>
                                @endif

                                {{-- Pembahasan --}}
                                @if(! empty($soal?->pembahasan))
                                    <div class="mt-4 border-t border-slate-100 pt-3">
                                        <div class="flex items-center gap-2 mb-2">
                                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                            </svg>
                                            <p class="text-sm font-semibold text-primary-900">Pembahasan</p>
                                        </div>
                                        <div class="prose prose-sm max-w-none text-slate-700 bg-primary-50/30 p-3 rounded-lg border border-primary-100">{!! $soal->pembahasan !!}</div>
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
