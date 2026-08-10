@extends('superadmin.layouts.app')
@section('title', 'Latihan Sederhana')

{{-- Breadcrumb --}}
@section('breadcrumb')
    <a href="{{ route('superadmin.dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Dashboard</a>
    <span class="text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Latihan Sederhana</span>
@endsection

{{-- Header dengan tombol aksi --}}
@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Latihan Sederhana</h1>
            <p class="text-secondary-500 mt-1">Modul CRUD sederhana dengan 4 input text</p>
        </div>
        <a href="{{ route('superadmin.latihan-sederhana.create') }}" class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Data
        </a>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-body-sm">
            <x-table>
                <x-slot name="header">
                    <th class="px-6 py-3 text-left">Judul</th>
                    <th class="px-6 py-3 text-left">Kode</th>
                    <th class="px-6 py-3 text-left">Penulis</th>
                    <th class="px-6 py-3 text-left">Keterangan</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </x-slot>

                @forelse($items as $item)
                    <tr class="hover:bg-secondary-50">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-secondary-900">{{ $item->judul }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-secondary-600">{{ $item->kode }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-secondary-600">{{ $item->penulis }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-secondary-500">{{ Str::limit($item->keterangan, 50) }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                {{-- Tombol Edit --}}
                                <a href="{{ route('superadmin.latihan-sederhana.edit', $item) }}" 
                                   class="btn btn-ghost btn-sm text-primary-600 hover:text-primary-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>

                                {{-- Tombol Delete dengan confirm dialog --}}
                                <button type="button"
                                        @click="$dispatch('confirm-dialog', {
                                            title: 'Hapus Data',
                                            message: 'Apakah Anda yakin ingin menghapus data {{ addslashes($item->judul) }}?',
                                            confirmText: 'Ya, Hapus',
                                            type: 'danger',
                                            formAction: '{{ route('superadmin.latihan-sederhana.destroy', $item) }}'
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
                        <td colspan="5" class="px-6 py-12 text-center text-secondary-500">
                            Belum ada data. Klik "Tambah Data" untuk membuat data baru.
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>

        {{-- Pagination --}}
        @if($items->hasPages())
            <div class="card-footer">
                {{ $items->links() }}
            </div>
        @endif
    </div>
@endsection
