@extends('pengawas.layouts.app')

@section('title', 'Dashboard Pengawas')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-secondary-900">Dashboard Pengawas</h1>
        <p class="text-secondary-500 mt-2">Kelola absensi dan lihat hasil ujian</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Absensi Card -->
        <a href="{{ route('pengawas.absensi.index') }}"
           class="card hover:shadow-lg hover:border-primary-200 transition group">
            <div class="card-body">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center group-hover:bg-primary-200 transition">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-secondary-900 mb-1">Absensi</h3>
                <p class="text-secondary-500 text-sm">Kelola kehadiran peserta ujian</p>
            </div>
        </a>

        <!-- Hasil Ujian Card -->
        <a href="{{ route('pengawas.hasil-ujian.index') }}"
           class="card hover:shadow-lg hover:border-primary-200 transition group">
            <div class="card-body">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 bg-success-100 rounded-lg flex items-center justify-center group-hover:bg-success-200 transition">
                        <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-secondary-900 mb-1">Hasil Ujian</h3>
                <p class="text-secondary-500 text-sm">Pantau hasil dan analisis ujian peserta</p>
            </div>
        </a>
    </div>
</div>
@endsection
