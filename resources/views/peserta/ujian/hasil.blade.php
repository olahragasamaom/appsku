@extends('peserta.layouts.app')

@section('title', 'Hasil Ujian')

@section('content')
    <div class="max-w-2xl mx-auto">
        <a href="{{ route('peserta.dashboard') }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Kembali ke Dashboard</a>

        <div class="card mt-4">
            <div class="card-body text-center">
                <h1 class="text-xl font-bold text-slate-800">{{ $ujian->nama_ujian }}</h1>

                @if($ujian->tampilkan_hasil)
                    <p class="text-4xl font-extrabold text-primary-600 mt-4">{{ $peserta->total_nilai ?? 0 }}</p>
                    <p class="text-slate-500 text-sm">Total Nilai</p>

                    <div class="mt-4">
                        @if($peserta->lulus === true)
                            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold bg-success-100 text-success-700">LULUS</span>
                        @elseif($peserta->lulus === false)
                            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold bg-danger-100 text-danger-700">TIDAK LULUS</span>
                        @endif
                    </div>
                @else
                    <p class="mt-6 text-slate-600">Ujian telah selesai. Hasil belum ditampilkan oleh penyelenggara.</p>
                @endif
            </div>
        </div>

        @if($ujian->tampilkan_hasil)
            <div class="card mt-6">
                <div class="card-body">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">Detail Hasil Per Kategori</h2>
                    <div class="space-y-3">
                        @foreach($breakdown as $row)
                            @php
                                $isPassed = $row['lulus'] === true;
                                $percentScore = $row['passing_grade'] ? round(($row['nilai'] / $row['passing_grade']) * 100, 0) : 0;
                            @endphp
                            <div class="border rounded-lg p-4 transition-all"
                                 :class="$isPassed ? 'border-success-300 bg-success-50' : 'border-danger-300 bg-danger-50'">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-slate-900">{{ $row['nama'] }}</h3>
                                        <p class="text-xs text-slate-500 mt-0.5">
                                            Passing Grade: <span class="font-medium">{{ $row['passing_grade'] ?? '-' }}</span>
                                        </p>
                                    </div>
                                    @if($isPassed)
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-success-100 text-success-700 flex-shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            Lulus
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-danger-100 text-danger-700 flex-shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Tidak Lulus
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-end gap-4">
                                    <div class="flex-1">
                                        <div class="mb-2">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-sm font-medium text-slate-700">Nilai Anda</span>
                                                <span class="text-sm font-bold" :class="$isPassed ? 'text-success-600' : 'text-danger-600'">
                                                    {{ $row['nilai'] }}
                                                </span>
                                            </div>
                                            <div class="w-full bg-slate-200 rounded-full h-2">
                                                <div class="bg-gradient-to-r h-2 rounded-full transition-all"
                                                     :class="$isPassed ? 'from-success-400 to-success-600' : 'from-danger-400 to-danger-600'"
                                                     style="width: {{ min($percentScore, 100) }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <div class="text-xs text-slate-500 mb-1">Capai Target</div>
                                        <div class="text-2xl font-bold" :class="$isPassed ? 'text-success-600' : 'text-danger-600'">
                                            {{ min($percentScore, 100) }}%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
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
