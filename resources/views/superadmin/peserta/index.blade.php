@extends('superadmin.layouts.app')

@section('title', 'Master Peserta')

@section('breadcrumb')
    <span class="text-secondary-900 font-medium">Master Peserta</span>
@endsection

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Master Peserta</h1>
            <p class="text-secondary-500 mt-1">Bank data peserta ujian</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" @click="$dispatch('open-import-peserta')" class="btn btn-secondary">Import Excel/CSV</button>
            <a href="{{ route('superadmin.peserta.create') }}" class="btn btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Peserta
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="card mb-6">
        <div class="card-body-sm">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="search" value="{{ request('search') }}"
                       class="input flex-1" placeholder="Cari nama / username / email...">
                <button type="submit" class="btn btn-secondary">Cari</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body-sm">
            <x-table>
                <x-slot name="header">
                    <th class="px-6 py-3 text-left">Nama</th>
                    <th class="px-6 py-3 text-left">Username</th>
                    <th class="px-6 py-3 text-left">Email</th>
                    <th class="px-6 py-3 text-center">Status</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </x-slot>

                @forelse($peserta as $item)
                    <tr class="hover:bg-secondary-50">
                        <td class="px-6 py-4 font-medium text-secondary-900">{{ $item->name }}</td>
                        <td class="px-6 py-4 text-secondary-700">{{ $item->username }}</td>
                        <td class="px-6 py-4 text-secondary-500 text-sm">{{ $item->email }}</td>
                        <td class="px-6 py-4 text-center">
                            @if($item->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-700">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary-100 text-secondary-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('superadmin.peserta.edit', $item) }}" class="btn btn-ghost btn-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <button type="button"
                                        @click="$dispatch('confirm-dialog', {
                                            title: 'Hapus Peserta',
                                            message: 'Apakah Anda yakin ingin menghapus peserta {{ $item->name }}?',
                                            confirmText: 'Ya, Hapus',
                                            type: 'danger',
                                            formAction: '{{ route('superadmin.peserta.destroy', $item) }}'
                                        })"
                                        class="btn btn-ghost btn-sm text-danger-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-secondary-500">
                            Belum ada peserta
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>

        @if($peserta->hasPages())
            <div class="card-footer">
                {{ $peserta->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Import --}}
    <div x-data="{ show: false }"
         x-on:open-import-peserta.window="show = true"
         x-on:keydown.escape.window="show = false"
         x-show="show" x-cloak class="modal-backdrop">
        <div @click.outside="show = false" x-show="show" class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Import Peserta</h3>
                <button type="button" @click="show = false" class="modal-close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('superadmin.peserta.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body space-y-3">
                    <p class="text-sm text-secondary-600">Format kolom header: <code>nama</code>, <code>username</code>, <code>password</code> (opsional), <code>email</code> (opsional), <code>no_hp</code> (opsional).</p>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" class="input w-full" required>
                    @error('file')<p class="text-sm text-danger-600">{{ $message }}</p>@enderror
                </div>
                <div class="modal-footer flex items-center justify-end gap-3">
                    <button type="button" @click="show = false" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
@endsection
