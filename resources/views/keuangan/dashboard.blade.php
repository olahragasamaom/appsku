@extends('keuangan.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="p-8">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900 mb-1">Selamat datang, {{ auth()->user()->name }}!</h2>
        <p class="text-slate-600">Overview sistem keuangan dan pembayaran</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm font-medium">Total Peserta</p>
                    <p class="text-3xl font-bold text-slate-900 mt-2">{{ $totalPeserta }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM9 20H4v-2a6 6 0 0112 0v2H9z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm font-medium">Total Paket</p>
                    <p class="text-3xl font-bold text-slate-900 mt-2">{{ $totalPaket }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm font-medium">Pembayaran Pending</p>
                    <p class="text-3xl font-bold text-slate-900 mt-2">{{ $pembayaranPending }}</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm font-medium">Pembayaran Terverifikasi</p>
                    <p class="text-3xl font-bold text-slate-900 mt-2">{{ $pembayaranVerified }}</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Akses Cepat</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('keuangan.peserta.index') }}" class="p-4 border border-slate-200 rounded-lg hover:bg-green-50 hover:border-green-300 transition">
                <h4 class="font-semibold text-green-600 mb-1">Kelola Peserta</h4>
                <p class="text-sm text-slate-600">Lihat dan kelola data peserta</p>
            </a>
            <a href="{{ route('keuangan.paket.index') }}" class="p-4 border border-slate-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition">
                <h4 class="font-semibold text-blue-600 mb-1">Kelola Paket</h4>
                <p class="text-sm text-slate-600">Atur paket & langganan</p>
            </a>
            <a href="{{ route('keuangan.pembayaran.index') }}" class="p-4 border border-slate-200 rounded-lg hover:bg-yellow-50 hover:border-yellow-300 transition">
                <h4 class="font-semibold text-yellow-600 mb-1">Verifikasi Pembayaran</h4>
                <p class="text-sm text-slate-600">Proses pembayaran pending</p>
            </a>
        </div>
    </div>
</div>
@endsection
