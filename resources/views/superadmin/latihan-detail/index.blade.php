@extends('superadmin.layouts.app')
@section('title', 'Latihan Detail')

@section('breadcrumb')
    <a href="{{ route('superadmin.dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Dashboard</a>
    <span class="text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Latihan Detail</span>
@endsection

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Latihan Detail</h1>
            <p class="text-secondary-500 mt-1">Form kompleks dengan relasi antar tabel dan modal picker</p>
        </div>
        <a href="{{ route('superadmin.latihan-detail.create') }}" class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Transaksi
        </a>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-body-sm">
            <x-table>
                <x-slot name="header">
                    <th class="px-6 py-3 text-left">Nomor</th>
                    <th class="px-6 py-3 text-left">Nama Transaksi</th>
                    <th class="px-6 py-3 text-left">Kategori</th>
                    <th class="px-6 py-3 text-left">Tanggal</th>
                    <th class="px-6 py-3 text-right">Total</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </x-slot>

                @forelse($details as $detail)
                    <tr class="hover:bg-secondary-50">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-secondary-900">{{ $detail->nomor }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-secondary-900">{{ $detail->nama_transaksi }}</div>
                            @if($detail->catatan)
                                <div class="text-xs text-secondary-500 mt-1">{{ Str::limit($detail->catatan, 40) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-secondary-600">
                                {{ $detail->kategori?->nama ?? '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-secondary-600">
                                {{ $detail->tanggal->format('d M Y') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="text-sm font-medium text-secondary-900">
                                Rp {{ number_format($detail->total, 0, ',', '.') }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('superadmin.latihan-detail.edit', $detail) }}" 
                                   class="btn btn-ghost btn-sm text-primary-600 hover:text-primary-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>

                                <button type="button"
                                        @click="$dispatch('confirm-dialog', {
                                            title: 'Hapus Transaksi',
                                            message: 'Apakah Anda yakin ingin menghapus transaksi {{ addslashes($detail->nomor) }}? Semua item di dalamnya akan ikut terhapus.',
                                            confirmText: 'Ya, Hapus',
                                            type: 'danger',
                                            formAction: '{{ route('superadmin.latihan-detail.destroy', $detail) }}'
                                        })"
                                        class="btn btn-ghost btn-sm text-danger-600 hover:text-danger-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-secondary-500">
                            Belum ada transaksi. Klik "Tambah Transaksi" untuk membuat transaksi baru.
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>

        @if($details->hasPages())
            <div class="card-footer">
                {{ $details->links() }}
            </div>
        @endif
    </div>
@endsection
