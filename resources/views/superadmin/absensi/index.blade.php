@extends('superadmin.layouts.app')

@section('title', 'Kelola Absensi')

@section('breadcrumb')
    <span class="text-secondary-900 font-medium">Kelola Absensi</span>
@endsection

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h2 class="font-semibold text-xl text-secondary-800">Kelola Absensi Peserta</h2>
            <p class="text-sm text-secondary-500">Pilih ujian untuk mengaktifkan/nonaktifkan peserta</p>
        </div>
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
                    <p class="text-secondary-500 text-sm mt-1">Silakan aktifkan ujian terlebih dahulu di menu Manajemen Ujian</p>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($ujians as $ujian)
                    <div class="card hover:shadow-lg transition-shadow">
                        <div class="card-body">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-lg text-secondary-900">{{ $ujian->nama_ujian }}</h3>
                                    <p class="text-sm text-secondary-500 mt-1">{{ $ujian->sub_jenis_ujian->nama_sub_jenis_ujian ?? 'N/A' }}</p>
                                </div>
                                <x-badge type="success">Aktif</x-badge>
                            </div>

                            <div class="space-y-2 mb-4 text-sm">
                                <div class="flex items-center gap-2 text-secondary-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $ujian->tanggal_ujian->format('d M Y H:i') }}
                                </div>
                                <div class="flex items-center gap-2 text-secondary-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <span class="font-semibold text-secondary-900">{{ $ujian->pesertaOffline->count() }} peserta</span>
                                </div>
                            </div>

                            <a href="{{ route('superadmin.absensi.activation', $ujian) }}" class="btn btn-primary w-full">
                                Kelola Aktivasi Peserta
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
