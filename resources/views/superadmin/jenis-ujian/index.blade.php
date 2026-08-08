@extends('superadmin.layouts.app')

@section('title', 'Kelola Jenis Ujian')

@section('breadcrumb')
    <span class="text-secondary-900 font-medium">Jenis Ujian</span>
@endsection

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Kelola Jenis Ujian</h1>
            <p class="text-secondary-500 mt-1">Daftar jenis ujian yang tersedia</p>
        </div>
        <button type="button"
                @click="$dispatch('jenis-ujian-form', { mode: 'create' })"
                class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Jenis Ujian
        </button>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-body-sm">
            <x-table>
                <x-slot name="header">
                    <th class="px-6 py-3 text-left">Jenis Ujian</th>
                    <th class="px-6 py-3 text-left">Keterangan</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </x-slot>

                @forelse($jenisUjians as $jenisUjian)
                    <tr class="hover:bg-secondary-50">
                        <td class="px-6 py-4">
                            <p class="font-medium text-secondary-900">{{ $jenisUjian->nama_jenis_ujian }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-secondary-600">{{ $jenisUjian->keterangan ?: '-' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button"
                                        @click="$dispatch('jenis-ujian-form', {
                                            mode: 'edit',
                                            action: '{{ route('superadmin.jenis-ujian.update', $jenisUjian) }}',
                                            nama: {{ Js::from($jenisUjian->nama_jenis_ujian) }},
                                            keterangan: {{ Js::from($jenisUjian->keterangan ?? '') }}
                                        })"
                                        class="btn btn-ghost btn-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button type="button"
                                        @click="$dispatch('confirm-dialog', {
                                            title: 'Hapus Jenis Ujian',
                                            message: 'Apakah Anda yakin ingin menghapus jenis ujian {{ $jenisUjian->nama_jenis_ujian }}?',
                                            confirmText: 'Ya, Hapus',
                                            type: 'danger',
                                            formAction: '{{ route('superadmin.jenis-ujian.destroy', $jenisUjian) }}'
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
                        <td colspan="3" class="px-6 py-12 text-center text-secondary-500">
                            Belum ada jenis ujian yang dibuat
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>

        @if($jenisUjians->hasPages())
            <div class="card-footer">
                {{ $jenisUjians->links() }}
            </div>
        @endif
    </div>

    <div
        x-data="{
            open: {{ $errors->any() ? 'true' : 'false' }},
            mode: '{{ old('_form_mode', 'create') }}',
            action: '{{ old('_form_action', route('superadmin.jenis-ujian.store')) }}',
            nama: {{ Js::from(old('nama_jenis_ujian', '')) }},
            keterangan: {{ Js::from(old('keterangan', '')) }},
            show(detail) {
                this.mode = detail.mode;
                this.action = detail.mode === 'edit' ? detail.action : '{{ route('superadmin.jenis-ujian.store') }}';
                this.nama = detail.mode === 'edit' ? detail.nama : '';
                this.keterangan = detail.mode === 'edit' ? detail.keterangan : '';
                this.open = true;
                this.$nextTick(() => this.$refs.namaInput.focus());
            }
        }"
        x-on:jenis-ujian-form.window="show($event.detail)"
        x-on:keydown.escape.window="open = false"
        x-show="open"
        x-cloak
        class="modal-backdrop"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div
            @click.outside="open = false"
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="modal"
        >
            <div class="modal-header">
                <h3 class="modal-title" x-text="mode === 'edit' ? 'Edit Jenis Ujian' : 'Tambah Jenis Ujian'"></h3>
                <button type="button" @click="open = false" class="modal-close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="action" method="POST">
                @csrf
                <template x-if="mode === 'edit'">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <input type="hidden" name="_form_mode" :value="mode">
                <input type="hidden" name="_form_action" :value="action">

                <div class="modal-body">
                    <div>
                        <label for="nama_jenis_ujian" class="block text-sm font-medium text-secondary-700 mb-1">
                            Jenis Ujian <span class="text-danger-500">*</span>
                        </label>
                        <input type="text" name="nama_jenis_ujian" id="nama_jenis_ujian"
                               x-ref="namaInput" x-model="nama"
                               class="input w-full @error('nama_jenis_ujian') border-danger-500 @enderror"
                               placeholder="Ujian Tengah Semester" required>
                        @error('nama_jenis_ujian')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <label for="keterangan" class="block text-sm font-medium text-secondary-700 mb-1">
                            Keterangan
                        </label>
                        <textarea name="keterangan" id="keterangan" rows="3"
                                  x-model="keterangan"
                                  class="input w-full @error('keterangan') border-danger-500 @enderror"
                                  placeholder="Keterangan ujian (opsional)"></textarea>
                        @error('keterangan')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer flex items-center justify-end gap-3">
                    <button type="button" @click="open = false" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary"
                            x-text="mode === 'edit' ? 'Simpan Perubahan' : 'Simpan Jenis Ujian'"></button>
                </div>
            </form>
        </div>
    </div>
@endsection
