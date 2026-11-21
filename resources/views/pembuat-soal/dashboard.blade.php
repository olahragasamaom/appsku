@extends('pembuat-soal.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="p-8">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900 mb-1">Selamat datang, {{ auth()->user()->name }}!</h2>
        <p class="text-slate-600">Overview sistem manajemen ujian dan soal</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Total Ujian --}}
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm font-medium">Total Ujian</p>
                    <p class="text-3xl font-bold text-slate-900 mt-2">{{ $totalUjian }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Ujian Aktif --}}
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm font-medium">Ujian Aktif</p>
                    <p class="text-3xl font-bold text-slate-900 mt-2">{{ $ujianAktif }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Total Soal --}}
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm font-medium">Total Soal</p>
                    <p class="text-3xl font-bold text-slate-900 mt-2">{{ $totalSoal }}</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C6.5 6.253 2 10.998 2 17s4.5 10.747 10 10.747c5.5 0 10-4.998 10-10.747 0-6.002-4.5-10.747-10-10.747z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Total Peserta --}}
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm font-medium">Total Peserta</p>
                    <p class="text-3xl font-bold text-slate-900 mt-2">{{ $totalPeserta }}</p>
                </div>
                <div class="bg-orange-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM9 20H4v-2a6 6 0 0112 0v2H9z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Akses Cepat</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('pembuat-soal.ujian.create') }}" class="p-4 border border-slate-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition">
                <h4 class="font-semibold text-blue-600 mb-1">Buat Ujian Baru</h4>
                <p class="text-sm text-slate-600">Tambah ujian terbaru ke sistem</p>
            </a>
            <a href="{{ route('pembuat-soal.soal.create') }}" class="p-4 border border-slate-200 rounded-lg hover:bg-purple-50 hover:border-purple-300 transition">
                <h4 class="font-semibold text-purple-600 mb-1">Buat Soal Baru</h4>
                <p class="text-sm text-slate-600">Tambah soal ke bank soal</p>
            </a>
            <a href="{{ route('pembuat-soal.ujian.index') }}" class="p-4 border border-slate-200 rounded-lg hover:bg-green-50 hover:border-green-300 transition">
                <h4 class="font-semibold text-green-600 mb-1">Kelola Ujian</h4>
                <p class="text-sm text-slate-600">Lihat dan kelola semua ujian</p>
            </a>
        </div>
    </div>
</div>
@endsection
