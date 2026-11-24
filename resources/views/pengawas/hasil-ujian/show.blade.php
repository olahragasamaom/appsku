@extends('pengawas.layouts.app')

@section('title', $ujian->nama)

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <a href="{{ route('pengawas.hasil-ujian.index') }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium mb-2 inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Kembali
                </a>
                <h1 class="text-3xl font-bold text-secondary-900">{{ $ujian->nama }}</h1>
                <p class="text-secondary-500 mt-2">Hasil ujian peserta</p>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="card">
            <div class="card-body">
                <p class="text-secondary-500 text-sm">Total Peserta</p>
                <p class="text-3xl font-bold text-secondary-900 mt-2">{{ $pesertaList->count() }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="text-secondary-500 text-sm">Lulus</p>
                <p class="text-3xl font-bold text-success-600 mt-2">{{ $pesertaList->where('breakdown.lulus', true)->count() }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="text-secondary-500 text-sm">Tidak Lulus</p>
                <p class="text-3xl font-bold text-danger-600 mt-2">{{ $pesertaList->where('breakdown.lulus', false)->count() }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="text-secondary-500 text-sm">Rata-rata Nilai</p>
                <p class="text-3xl font-bold text-primary-600 mt-2">
                    {{ $pesertaList->count() > 0 ? number_format($pesertaList->avg('nilai_total'), 1) : '-' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Peserta List -->
    <div class="card">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-secondary-900 mb-4">Daftar Peserta</h3>

            @if($pesertaList->isEmpty())
                <p class="text-secondary-500 text-center py-8">Belum ada peserta ujian</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-secondary-200">
                        <thead class="bg-secondary-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-700 uppercase">Nama Peserta</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-secondary-700 uppercase">Nilai</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-secondary-700 uppercase">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-secondary-700 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-secondary-200">
                            @foreach($pesertaList as $item)
                                <tr class="hover:bg-secondary-50">
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="text-sm font-medium text-secondary-900">{{ $item['user']->name ?? 'Unknown' }}</p>
                                            <p class="text-xs text-secondary-500">{{ $item['user']->email }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <p class="text-lg font-bold text-secondary-900">{{ number_format($item['nilai_total'], 1) }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($item['breakdown']['lulus'])
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-success-100 text-success-700">
                                                LULUS
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-danger-100 text-danger-700">
                                                TIDAK LULUS
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('pengawas.hasil-ujian.peserta.show', [$ujian, $item['ujianPeserta']]) }}"
                                           class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                                            Lihat Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
