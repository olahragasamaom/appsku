@extends('superadmin.layouts.app')

@section('title', 'Hasil Ujian - '.$ujian->nama_ujian)

@section('breadcrumb')
    <a href="{{ route('superadmin.hasil-ujian.index') }}" class="text-secondary-500 hover:text-secondary-700">Hasil Ujian</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">{{ $ujian->nama_ujian }}</span>
@endsection

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h2 class="font-semibold text-xl text-secondary-800">{{ $ujian->nama_ujian }}</h2>
            <p class="text-sm text-secondary-500">{{ $ujian->subJenisUjian->nama_sub_jenis_ujian ?? 'N/A' }}</p>
        </div>
        <a href="{{ route('superadmin.hasil-ujian.index') }}" class="btn btn-ghost">Kembali</a>
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">
        @if(session('success'))
            <x-alert type="success" dismissible>{{ session('success') }}</x-alert>
        @endif
        @if(session('info'))
            <x-alert type="info" dismissible>{{ session('info') }}</x-alert>
        @endif

        {{-- Statistics Overview --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="card">
                <div class="card-body">
                    <p class="text-sm text-secondary-500 mb-1">Total Terdaftar</p>
                    <p class="text-3xl font-bold text-secondary-900">{{ $totalRegistered }}</p>
                </div>
            </div>
            <div class="card bg-success-50 border-success-100">
                <div class="card-body">
                    <p class="text-sm text-success-700 mb-1">Selesai Ujian</p>
                    <p class="text-3xl font-bold text-success-900">{{ $ujian->peserta_selesai_count }}</p>
                </div>
            </div>
            <div class="card bg-warning-50 border-warning-100">
                <div class="card-body">
                    <p class="text-sm text-warning-700 mb-1">Sedang Ujian</p>
                    <p class="text-3xl font-bold text-warning-900">{{ $ujian->peserta_sedang_count }}</p>
                </div>
            </div>
            <div class="card bg-slate-50 border-slate-200">
                <div class="card-body">
                    <p class="text-sm text-slate-600 mb-1">Belum Ujian</p>
                    <p class="text-3xl font-bold text-slate-900">{{ $pesertaBelumUjian }}</p>
                </div>
            </div>
        </div>

        {{-- Menu Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Live Score --}}
            <a href="{{ route('superadmin.hasil-ujian.live-score', $ujian) }}" class="card hover:shadow-lg transition-shadow group">
                <div class="card-body">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-primary-200 transition-colors">
                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-secondary-900 mb-1">Live Score</h3>
                            <p class="text-sm text-secondary-500">Monitor skor peserta secara real-time saat ujian berlangsung</p>
                        </div>
                        <svg class="w-5 h-5 text-secondary-400 group-hover:text-primary-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>

            {{-- Final Score (Ranking) --}}
            <a href="{{ route('superadmin.hasil-ujian.final-score', $ujian) }}" class="card hover:shadow-lg transition-shadow group">
                <div class="card-body">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-success-100 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-success-200 transition-colors">
                            <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-secondary-900 mb-1">Skor Akhir & Ranking</h3>
                            <p class="text-sm text-secondary-500">Lihat peringkat dan nilai akhir semua peserta</p>
                        </div>
                        <svg class="w-5 h-5 text-secondary-400 group-hover:text-success-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>

            {{-- Analisis Soal --}}
            <a href="{{ route('superadmin.hasil-ujian.soal-analysis', $ujian) }}" class="card hover:shadow-lg transition-shadow group">
                <div class="card-body">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-amber-200 transition-colors">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-secondary-900 mb-1">Analisis Soal</h3>
                            <p class="text-sm text-secondary-500">Statistik jawaban benar/salah per nomor soal</p>
                        </div>
                        <svg class="w-5 h-5 text-secondary-400 group-hover:text-amber-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>

            {{-- Daftar Peserta --}}
            <a href="{{ route('superadmin.hasil-ujian.peserta-list', $ujian) }}" class="card hover:shadow-lg transition-shadow group">
                <div class="card-body">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-violet-100 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-violet-200 transition-colors">
                            <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-secondary-900 mb-1">Daftar Peserta</h3>
                            <p class="text-sm text-secondary-500">Status ujian & review jawaban per peserta</p>
                        </div>
                        <svg class="w-5 h-5 text-secondary-400 group-hover:text-violet-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
        </div>
    </div>
@endsection
