@extends('peserta.layouts.app')

@section('title', 'Ringkasan Hasil Ujian')

@section('content')
    @php
        $breakdownList = ! empty($breakdownSubJenis ?? []) ? $breakdownSubJenis : $breakdown;
        $hasFailedSubJenis = collect($breakdownList)->contains(fn($row) => $row['lulus'] === false);
        $allSubJenisPassed = collect($breakdownList)->every(fn($row) => $row['lulus'] === true);
        $overallLulus = count($breakdownList) > 0 && $allSubJenisPassed && !$hasFailedSubJenis;
    @endphp

    <div class="max-w-4xl mx-auto px-4 py-6">
        <div class="card">
            <div class="card-body text-center">
                <h1 class="text-3xl font-bold text-slate-800 mb-2">{{ $ujian->nama_ujian }}</h1>
                <p class="text-slate-500">Ujian berhasil diselesaikan</p>

                @if($ujian->tampilkan_hasil)
                    <div class="mt-8 mb-8">
                        <p class="text-5xl font-extrabold {{ $overallLulus ? 'text-success-600' : 'text-danger-600' }}">
                            {{ $peserta->total_nilai ?? 0 }}
                        </p>
                        <p class="text-slate-500 text-sm mt-2">Nilai Total</p>

                        <div class="mt-6">
                            @if($overallLulus)
                                <span class="inline-flex items-center px-6 py-2 rounded-full text-lg font-bold bg-success-500 text-white">
                                    ✓ LULUS
                                </span>
                            @else
                                <span class="inline-flex items-center px-6 py-2 rounded-full text-lg font-bold bg-danger-500 text-white">
                                    ✗ TIDAK LULUS
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 py-6 border-t border-b border-slate-200">
                        <div class="flex flex-col items-center">
                            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Durasi</p>
                            <p class="text-lg font-bold text-slate-800">
                                @php
                                    $durasi = $peserta->waktu_mulai && $peserta->waktu_selesai
                                        ? $peserta->waktu_selesai->diffInMinutes($peserta->waktu_mulai)
                                        : 0;
                                    $jam = floor($durasi / 60);
                                    $menit = $durasi % 60;
                                @endphp
                                @if($jam > 0)
                                    {{ $jam }}j {{ $menit }}m
                                @else
                                    {{ $menit }}m
                                @endif
                            </p>
                        </div>
                        <div class="flex flex-col items-center">
                            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Peringkat</p>
                            <p class="text-lg font-bold text-slate-800">
                                @if($ranking && $ranking['rank'])
                                    #{{ $ranking['rank'] }} / {{ $ranking['total'] }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <div class="flex flex-col items-center">
                            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Status</p>
                            <p class="text-lg font-bold {{ $overallLulus ? 'text-success-600' : 'text-danger-600' }}">
                                {{ $overallLulus ? 'LULUS' : 'TIDAK LULUS' }}
                            </p>
                        </div>
                    </div>

                    @if(count($breakdownList) > 0)
                        <div class="mt-8">
                            <h2 class="text-left text-sm font-bold text-slate-900 mb-4">DETAIL PER KATEGORI</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                @foreach($breakdownList as $row)
                                    @php
                                        $isPassed = $row['lulus'] === true;
                                        $borderClass = $isPassed ? 'border-success-300 bg-success-50' : 'border-danger-300 bg-danger-50';
                                        $badgeClass = $isPassed ? 'bg-success-100 text-success-700' : 'bg-danger-100 text-danger-700';
                                    @endphp
                                    <div class="border-2 rounded-lg p-4 {{ $borderClass }} text-left">
                                        <div class="flex items-start justify-between mb-2">
                                            <h3 class="font-bold text-sm text-slate-900">{{ $row['nama'] }}</h3>
                                            <span class="text-xs font-bold px-2 py-1 rounded {{ $badgeClass }}">
                                                {{ $isPassed ? '✓ Lulus' : '✗ Tidak' }}
                                            </span>
                                        </div>
                                        <p class="text-2xl font-bold text-slate-900">
                                            {{ $row['nilai'] }}
                                            <span class="text-xs text-slate-500 font-normal">/ {{ $row['nilai_maksimal'] }}</span>
                                        </p>
                                        <p class="text-xs text-slate-600 mt-2">
                                            {{ $row['pencapaian_persen'] }}% dari maksimal
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <p class="mt-6 text-slate-600">Ujian telah selesai. Hasil belum ditampilkan oleh penyelenggara.</p>
                @endif
            </div>
        </div>

        <div class="mt-8 flex flex-wrap gap-3 justify-center">
            @if($ujian->tampilkan_hasil)
                <a href="{{ route('peserta.ujian.riwayat', $ujian) }}" class="btn btn-secondary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Lihat Riwayat
                </a>

                <a href="{{ route('peserta.ujian.pembahasan', $ujian) }}" class="btn btn-info">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Lihat Pembahasan
                </a>

                @if($ujian->allow_retry)
                    <form method="POST" action="{{ route('peserta.ujian.retry', $ujian) }}" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Ujian Ulang
                        </button>
                    </form>
                @endif

                <a href="{{ route('peserta.ujian.leaderboard', $ujian) }}" class="btn btn-outline-secondary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Lihat Peringkat
                </a>
            @endif

            <a href="{{ route('peserta.offline.exams') }}" class="btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12a9 9 0 019-9 9.75 9.75 0 016.74 2.74L21 8m0 0v-4m0 4H17m6 4a9 9 0 01-9 9 9.75 9.75 0 01-6.74-2.74L3 16m0 0v4m0-4h4"></path>
                </svg>
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    <style>
        .btn-info {
            @apply btn bg-sky-500 hover:bg-sky-600 text-white;
        }

        .btn-warning {
            @apply btn bg-amber-500 hover:bg-amber-600 text-white;
        }
    </style>
@endsection
