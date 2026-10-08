@extends('superadmin.layouts.app')

@section('title', 'Hasil Ujian')

@section('breadcrumb')
    <span class="text-secondary-900 font-medium">Hasil Ujian</span>
@endsection

@section('header')
    <div>
        <h2 class="font-semibold text-xl text-secondary-800">Hasil Ujian</h2>
        <p class="text-sm text-secondary-500">Monitoring dan analisis hasil ujian yang sedang berlangsung</p>
    </div>
@endsection

@section('content')
    <div class="max-w-5xl mx-auto">
        @if($ujians->isEmpty())
            <div class="card">
                <div class="card-body text-center py-12">
                    <svg class="w-16 h-16 text-secondary-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-secondary-600 font-medium">Tidak ada ujian aktif</p>
                    <p class="text-secondary-500 text-sm mt-1">Belum ada ujian yang sedang berlangsung</p>
                </div>
            </div>
        @else
            <div class="space-y-4">
                @foreach($ujians as $ujian)
                    <div class="card">
                        <div class="card-body">
                            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-6">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <h3 class="font-bold text-lg text-secondary-900">{{ $ujian->nama_ujian }}</h3>
                                        <x-badge type="success">Aktif</x-badge>
                                    </div>
                                    <p class="text-sm text-secondary-500 mb-3">{{ $ujian->subJenisUjian->nama_sub_jenis_ujian ?? 'N/A' }}</p>

                                    <div class="flex flex-wrap gap-4 text-sm text-secondary-600">
                                        @if($ujian->tanggal_ujian)
                                            <div class="flex items-center gap-1.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                {{ $ujian->tanggal_ujian->format('d M Y, H:i') }}
                                            </div>
                                        @endif
                                        @if($ujian->durasi_ujian)
                                            <div class="flex items-center gap-1.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                {{ $ujian->durasi_ujian }} menit
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <a href="{{ route('superadmin.hasil-ujian.show', $ujian) }}" class="btn btn-primary">
                                    Lihat Hasil
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>

                            {{-- Statistics --}}
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 pt-4 border-t border-secondary-100">
                                <div class="bg-primary-50 border border-primary-100 rounded-lg p-3">
                                    <p class="text-xs text-primary-600 font-medium mb-1">Total Peserta</p>
                                    <p class="text-2xl font-bold text-primary-900">{{ $ujian->peserta_count }}</p>
                                </div>
                                <div class="bg-success-50 border border-success-100 rounded-lg p-3">
                                    <p class="text-xs text-success-600 font-medium mb-1">Selesai</p>
                                    <p class="text-2xl font-bold text-success-900">{{ $ujian->peserta_selesai_count }}</p>
                                </div>
                                <div class="bg-warning-50 border border-warning-100 rounded-lg p-3">
                                    <p class="text-xs text-warning-600 font-medium mb-1">Sedang Ujian</p>
                                    <p class="text-2xl font-bold text-warning-900">{{ $ujian->peserta_sedang_count }}</p>
                                </div>
                                <div class="bg-slate-50 border border-slate-200 rounded-lg p-3">
                                    <p class="text-xs text-slate-600 font-medium mb-1">Total Soal</p>
                                    <p class="text-2xl font-bold text-slate-900">{{ $ujian->jumlah_soal }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
