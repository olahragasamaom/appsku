@extends('pengawas.layouts.app')

@section('title', $ujianPeserta->user->name)

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <a href="{{ route('pengawas.hasil-ujian.show', $ujian) }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium mb-2 inline-flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
        </a>
        <h1 class="text-3xl font-bold text-secondary-900">{{ $ujianPeserta->user->name }}</h1>
        <p class="text-secondary-500 mt-2">{{ $ujian->nama }}</p>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="card">
            <div class="card-body">
                <p class="text-secondary-500 text-sm">Total Nilai</p>
                <p class="text-3xl font-bold text-secondary-900 mt-2">{{ number_format($ujianPeserta->nilai_total, 1) }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="text-secondary-500 text-sm">Status</p>
                <div class="mt-2">
                    @if($breakdown['lulus'])
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-success-100 text-success-700">
                            LULUS
                        </span>
                    @else
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-danger-100 text-danger-700">
                            TIDAK LULUS
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="text-secondary-500 text-sm">Waktu Pengerjaan</p>
                <p class="text-secondary-900 font-semibold mt-2">
                    @if($ujianPeserta->submitted_at)
                        {{ $ujianPeserta->submitted_at->diffInMinutes($ujianPeserta->started_at) }} menit
                    @else
                        Belum selesai
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Breakdown Per Sub Jenis -->
    <div class="card mb-8">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-secondary-900 mb-4">Hasil Per Sub Jenis Ujian</h3>

            <div class="space-y-4">
                @foreach($breakdown['sub_jenis'] as $subJenis)
                    <div class="border border-secondary-200 rounded-lg p-4">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h4 class="font-semibold text-secondary-900">{{ $subJenis['nama'] }}</h4>
                            </div>
                            @if($subJenis['pencapaian_persen'] >= 75)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-success-100 text-success-700">
                                    LULUS
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-danger-100 text-danger-700">
                                    TIDAK LULUS
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-3 gap-4 mb-3">
                            <div>
                                <p class="text-secondary-500 text-sm">Nilai Dicapai</p>
                                <p class="text-secondary-900 font-semibold">{{ number_format($subJenis['nilai_dicapai'], 1) }}</p>
                            </div>
                            <div>
                                <p class="text-secondary-500 text-sm">Nilai Maksimal</p>
                                <p class="text-secondary-900 font-semibold">{{ number_format($subJenis['nilai_maksimal'], 1) }}</p>
                            </div>
                            <div>
                                <p class="text-secondary-500 text-sm">Persentase</p>
                                <p class="text-secondary-900 font-semibold">{{ number_format($subJenis['pencapaian_persen'], 1) }}%</p>
                            </div>
                        </div>

                        <div class="w-full bg-secondary-200 rounded-full h-2">
                            <div class="bg-gradient-to-r from-primary-500 to-primary-600 h-2 rounded-full"
                                 style="width: {{ min($subJenis['pencapaian_persen'], 100) }}%">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Jawaban Detail -->
    <div class="card">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-secondary-900 mb-4">Detail Jawaban</h3>

            @if($ujianPeserta->ujianJawaban->isEmpty())
                <p class="text-secondary-500 text-center py-8">Belum ada jawaban</p>
            @else
                <div class="space-y-6">
                    @foreach($ujianPeserta->ujianJawaban as $jawaban)
                        <div class="border border-secondary-200 rounded-lg p-4">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-secondary-900">{{ $jawaban->soal->soal ?? 'No soal' }}</h4>
                                </div>
                                @if($jawaban->nilai > 0)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-success-100 text-success-700 ml-2 flex-shrink-0">
                                        BENAR
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-danger-100 text-danger-700 ml-2 flex-shrink-0">
                                        SALAH
                                    </span>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-secondary-500">Jawaban Peserta</p>
                                    <p class="text-secondary-900 font-medium mt-1">{{ $jawaban->jawaban ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-secondary-500">Jawaban Benar</p>
                                    <p class="text-secondary-900 font-medium mt-1">{{ $jawaban->soal->kunci_jawaban ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
