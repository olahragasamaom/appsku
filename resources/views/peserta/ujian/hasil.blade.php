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

            @if(!empty($breakdownSubIndikator))
                @php
                    $totalSubIndikator = count($breakdownSubIndikator);
                    $subIndikatorKurang = collect($breakdownSubIndikator)->filter(fn($i) => $i['is_kurang']);
                    $countKurang = $subIndikatorKurang->count();
                @endphp
                <div class="card mt-6 border-l-4 {{ $countKurang > 0 ? 'border-l-danger-500' : 'border-l-success-500' }}">
                    <div class="card-body">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                            <div>
                                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                    Analisis Penguasaan Materi (Sub Indikator)
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Evaluasi tipe soal/materi untuk bahan bimbingan peserta dan mentor
                                </p>
                            </div>
                            <a href="{{ route('peserta.ujian.analisis', $ujian) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary-50 text-primary-700 hover:bg-primary-100 transition-colors self-start sm:self-auto border border-primary-200">
                                Buka Analisis Lengkap
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>

                        {{-- Ringkasan Indikator --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
                            <div class="bg-slate-50 rounded-lg p-3 border border-slate-200 text-center">
                                <span class="text-xs text-slate-500 font-medium">Total Topik Diuji</span>
                                <p class="text-lg font-bold text-slate-800 mt-0.5">{{ $totalSubIndikator }} Materi</p>
                            </div>
                            <div class="bg-success-50 rounded-lg p-3 border border-success-200 text-center">
                                <span class="text-xs text-success-700 font-medium">Dikuasai (≥ 60%)</span>
                                <p class="text-lg font-bold text-success-700 mt-0.5">{{ $totalSubIndikator - $countKurang }} Materi</p>
                            </div>
                            <div class="col-span-2 sm:col-span-1 {{ $countKurang > 0 ? 'bg-danger-50 border-danger-200 text-danger-700' : 'bg-slate-50 border-slate-200 text-slate-600' }} rounded-lg p-3 border text-center">
                                <span class="text-xs font-medium">Perlu Evaluasi (< 60%)</span>
                                <p class="text-lg font-bold mt-0.5 {{ $countKurang > 0 ? 'text-danger-700' : 'text-slate-700' }}">{{ $countKurang }} Materi</p>
                            </div>
                        </div>

                        @if($countKurang > 0)
                            <div class="p-3 bg-danger-50/70 border border-danger-200 rounded-lg">
                                <div class="flex items-center gap-1.5 text-xs font-bold text-danger-700 mb-2">
                                    <svg class="w-4 h-4 text-danger-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    Topik Prioritas yang Dinilai Kurang (Grafik Merah):
                                </div>
                                <div class="space-y-2">
                                    @foreach($subIndikatorKurang->take(3) as $kurang)
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between text-xs gap-1 bg-white p-2 rounded border border-danger-100">
                                            <div class="font-semibold text-slate-800">
                                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 mr-1.5">{{ $kurang['sub_jenis_nama'] }}</span>
                                                {{ $kurang['nama'] }}
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <span class="text-slate-500">Benar: <strong class="text-slate-700">{{ $kurang['total_benar'] }}/{{ $kurang['total_soal'] }}</strong> ({{ $kurang['nilai'] }}/{{ $kurang['nilai_maksimal'] }})</span>
                                                <div class="w-20 bg-slate-200 rounded-full h-2 overflow-hidden flex-shrink-0">
                                                    <div class="h-2 rounded-full" style="width: {{ min($kurang['pencapaian_persen'], 100) }}%; background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%);"></div>
                                                </div>
                                                <span class="font-bold text-danger-600 w-12 text-right">{{ $kurang['pencapaian_persen'] }}%</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="p-3 bg-success-50 border border-success-200 rounded-lg text-xs text-success-800 flex items-center gap-2">
                                <svg class="w-4 h-4 text-success-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Luar biasa! Seluruh sub-indikator materi telah mencapai standar penguasaan (≥ 60%).
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                <form method="POST" action="{{ route('peserta.ujian.retry', $ujian) }}" class="w-full sm:w-auto">
                    @csrf
                    <button type="submit" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff;" class="btn btn-warning font-medium shadow-sm w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Ujian Ulang
                    </button>
                </form>
                <a href="{{ route('peserta.ujian.riwayat', $ujian) }}" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); color: #ffffff;" class="btn btn-info font-medium shadow-sm w-full sm:w-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Lihat Riwayat
                </a>
                <a href="{{ route('peserta.ujian.analisis', $ujian) }}" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color: #ffffff;" class="btn btn-purple font-medium shadow-sm w-full sm:w-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Analisis Kemampuan
                </a>
                <a href="{{ route('peserta.ujian.leaderboard', $ujian) }}" style="background-color: #ffffff; color: #2563eb; border: 1.5px solid #3b82f6;" class="btn btn-secondary font-medium w-full sm:w-auto hover:bg-slate-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    Lihat Peringkat
                </a>
                <a href="{{ route('peserta.ujian.pembahasan', $ujian) }}" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: #ffffff;" class="btn btn-primary font-medium shadow-sm w-full sm:w-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Lihat Pembahasan Jawaban
                </a>
            </div>
        @endif
    </div>
@endsection
