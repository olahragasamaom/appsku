@extends('peserta.layouts.app')

@section('title', 'Hasil Ujian')

@section('content')
    @php
        // Prefer breakdownSubJenis (TWK/TIU/TKP), fallback ke breakdown per Jenis
        $breakdownList = ! empty($breakdownSubJenis ?? []) ? $breakdownSubJenis : $breakdown;

        // Auto-calculate overall LULUS/TIDAK LULUS berdasarkan sub-jenis
        $hasFailedSubJenis = collect($breakdownList)->contains(fn($row) => $row['lulus'] === false);
        $allSubJenisPassed = collect($breakdownList)->every(fn($row) => $row['lulus'] === true);
        $overallLulus = count($breakdownList) > 0 && $allSubJenisPassed && !$hasFailedSubJenis;
    @endphp

    <div class="max-w-2xl mx-auto">
        <a href="{{ route('peserta.offline.exams') }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Kembali ke Dashboard</a>

        <div class="card mt-4">
            <div class="card-body text-center">
                <h1 class="text-xl font-bold text-slate-800">{{ $ujian->nama_ujian }}</h1>

                @if($ujian->tampilkan_hasil)
                    <p class="text-4xl font-extrabold {{ $overallLulus ? 'text-success-600' : 'text-danger-600' }} mt-4">{{ $peserta->total_nilai ?? 0 }}</p>
                    <p class="text-slate-500 text-sm">Nilai Akhir</p>

                    <div class="mt-4">
                        @if($overallLulus)
                            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold bg-success-500 text-white">LULUS</span>
                        @else
                            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold bg-danger-500 text-white">TIDAK LULUS</span>
                        @endif
                    </div>

                    @if(count($breakdownList) > 0)
                        <p class="text-xs text-slate-500 mt-3">
                            Kesimpulan berdasarkan passing grade {{ count($breakdownList) }} kategori ujian
                        </p>
                    @endif
                @else
                    <p class="mt-6 text-slate-600">Ujian telah selesai. Hasil belum ditampilkan oleh penyelenggara.</p>
                @endif
            </div>
        </div>

        @if($ujian->tampilkan_hasil)
            <div class="card mt-6">
                <div class="card-body">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">Detail Nilai Per Kategori</h2>
                    <div class="space-y-3">
                        @foreach($breakdownList as $row)
                            @php
                                $isPassed = $row['lulus'] === true;
                                $pencapaian = $row['pencapaian_persen'] ?? 0;
                                $nilaiMax = $row['nilai_maksimal'] ?? 0;

                                $borderClass = $isPassed ? 'border-success-400 bg-success-50' : 'border-danger-400 bg-danger-50';
                                $textClass = $isPassed ? 'text-success-700' : 'text-danger-700';

                                // Warna progress bar berdasarkan pencapaian:
                                // >= 75% = hijau, 50-74% = kuning, < 50% = merah
                                if ($pencapaian >= 75) {
                                    $barClass = 'from-success-400 to-success-600';
                                    $pencapaianTextClass = 'text-success-700';
                                } elseif ($pencapaian >= 50) {
                                    $barClass = 'from-yellow-400 to-yellow-600';
                                    $pencapaianTextClass = 'text-yellow-700';
                                } else {
                                    $barClass = 'from-danger-400 to-danger-600';
                                    $pencapaianTextClass = 'text-danger-700';
                                }
                            @endphp
                            <div class="border-2 rounded-lg p-4 transition-all {{ $borderClass }}">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <div class="flex items-baseline gap-2 flex-wrap">
                                            <h3 class="font-bold text-base {{ $textClass }}">Nilai {{ $row['nama'] }}:</h3>
                                            <span class="text-2xl font-extrabold {{ $textClass }}">{{ $row['nilai'] }}</span>
                                            <span class="text-xs text-slate-500">dari {{ $nilaiMax }}</span>
                                            @if($isPassed)
                                                <span class="text-sm font-semibold text-success-700">(Lulus)</span>
                                            @else
                                                <span class="text-sm font-semibold text-danger-700">(Tidak Lulus)</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-600 mt-1">
                                            @if($row['passing_grade'] !== null)
                                                @if($isPassed)
                                                    <span class="text-success-600 font-semibold">*Melewati Passing Grade {{ $row['nama'] }} ({{ $row['passing_grade'] }})</span>
                                                @else
                                                    <span class="text-danger-600 font-semibold">*Tidak Melewati Passing Grade {{ $row['nama'] }} ({{ $row['passing_grade'] }})</span>
                                                @endif
                                            @else
                                                <span class="text-slate-500">Passing Grade belum ditentukan</span>
                                            @endif
                                        </p>
                                    </div>
                                    @if($isPassed)
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-success-500 text-white flex-shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            LULUS
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-danger-500 text-white flex-shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                            TIDAK LULUS
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-end gap-4">
                                    <div class="flex-1">
                                        <div class="mb-2">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-xs font-medium text-slate-600">Pencapaian Nilai Benar ({{ $row['nilai'] }}/{{ $nilaiMax }})</span>
                                                <span class="text-xs font-bold {{ $pencapaianTextClass }}">{{ $pencapaian }}%</span>
                                            </div>
                                            <div class="w-full bg-slate-200 rounded-full h-2">
                                                <div class="bg-gradient-to-r h-2 rounded-full transition-all {{ $barClass }}"
                                                     style="width: {{ min($pencapaian, 100) }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Ringkasan Nilai Akhir --}}
                    <div class="mt-6 pt-4 border-t border-slate-200">
                        <div class="flex items-center justify-between">
                            <span class="text-base font-bold text-slate-900">Nilai Akhir:</span>
                            <span class="text-2xl font-extrabold {{ $overallLulus ? 'text-success-600' : 'text-danger-600' }}">{{ $peserta->total_nilai ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-sm font-semibold text-slate-700">Kesimpulan:</span>
                            @if($overallLulus)
                                <span class="text-sm font-bold text-success-700">LULUS (semua kategori memenuhi passing grade)</span>
                            @else
                                <span class="text-sm font-bold text-danger-700">TIDAK LULUS (ada kategori di bawah passing grade)</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('peserta.ujian.leaderboard', $ujian) }}" class="btn btn-secondary w-full sm:w-auto">
                    Lihat Peringkat
                </a>
                <a href="{{ route('peserta.ujian.pembahasan', $ujian) }}" class="btn btn-primary w-full sm:w-auto">
                    Lihat Pembahasan Jawaban
                </a>
            </div>
        @endif
    </div>
@endsection
