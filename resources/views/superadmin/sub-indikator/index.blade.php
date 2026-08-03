@extends('superadmin.layouts.app')

@section('title', 'Kelola Sub Indikator')

@section('breadcrumb')
    <span class="text-secondary-900 font-medium">Sub Indikator</span>
@endsection

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Kelola Sub Indikator</h1>
            <p class="text-secondary-500 mt-1">Daftar indikator soal per sub jenis ujian</p>
        </div>
        <button type="button"
                @click="$dispatch('sub-indikator-form', { mode: 'create' })"
                class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Sub Indikator
        </button>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-body-sm">
            <x-table>
                <x-slot name="header">
                    <th class="px-6 py-3 text-left">Jenis Ujian</th>
                    <th class="px-6 py-3 text-left">Sub Jenis Ujian</th>
                    <th class="px-6 py-3 text-left">Sub Indikator</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </x-slot>

                @forelse($subIndikators as $subIndikator)
                    <tr class="hover:bg-secondary-50">
                        <td class="px-6 py-4 text-secondary-700">{{ $subIndikator->subJenisUjian?->jenisUjian?->nama_jenis_ujian ?? '-' }}</td>
                        <td class="px-6 py-4 text-secondary-700">{{ $subIndikator->subJenisUjian?->nama_sub_jenis_ujian ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-secondary-900">{{ $subIndikator->nama_sub_indikator }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button"
                                        @click="$dispatch('sub-indikator-form', {
                                            mode: 'edit',
                                            action: '{{ route('superadmin.sub-indikator.update', $subIndikator) }}',
                                            data: {{ Js::from($subIndikator->only(['sub_jenis_ujian_id', 'nama_sub_indikator'])) }}
                                        })"
                                        class="btn btn-ghost btn-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button type="button"
                                        @click="$dispatch('confirm-dialog', {
                                            title: 'Hapus Sub Indikator',
                                            message: 'Apakah Anda yakin ingin menghapus {{ $subIndikator->nama_sub_indikator }}?',
                                            confirmText: 'Ya, Hapus',
                                            type: 'danger',
                                            formAction: '{{ route('superadmin.sub-indikator.destroy', $subIndikator) }}'
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
                        <td colspan="4" class="px-6 py-12 text-center text-secondary-500">
                            Belum ada sub indikator yang dibuat
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>

        @if($subIndikators->hasPages())
            <div class="card-footer">
                {{ $subIndikators->links() }}
            </div>
        @endif
    </div>

    <div
        x-data="{
            open: {{ $errors->any() ? 'true' : 'false' }},
            mode: '{{ old('_form_mode', 'create') }}',
            action: '{{ old('_form_action', route('superadmin.sub-indikator.store')) }}',
            form: {
                sub_jenis_ujian_id: {{ Js::from(old('sub_jenis_ujian_id', '')) }},
                nama_sub_indikator: {{ Js::from(old('nama_sub_indikator', '')) }},
            },
            show(detail) {
                this.mode = detail.mode;
                this.action = detail.mode === 'edit' ? detail.action : '{{ route('superadmin.sub-indikator.store') }}';
                if (detail.mode === 'edit') {
                    this.form = { sub_jenis_ujian_id: detail.data.sub_jenis_ujian_id, nama_sub_indikator: detail.data.nama_sub_indikator };
                } else {
                    this.form = { sub_jenis_ujian_id: '', nama_sub_indikator: '' };
                }
                this.open = true;
            }
        }"
        x-on:sub-indikator-form.window="show($event.detail)"
        x-on:keydown.escape.window="open = false"
        x-show="open"
        x-cloak
        class="modal-backdrop"
    >
        <div @click.outside="open = false" x-show="open" class="modal">
            <div class="modal-header">
                <h3 class="modal-title" x-text="mode === 'edit' ? 'Edit Sub Indikator' : 'Tambah Sub Indikator'"></h3>
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

                <div class="modal-body space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">Sub Jenis Ujian <span class="text-danger-500">*</span></label>
                        <select name="sub_jenis_ujian_id" x-model="form.sub_jenis_ujian_id"
                                class="input w-full @error('sub_jenis_ujian_id') border-danger-500 @enderror" required>
                            <option value="">-- Pilih Sub Jenis Ujian --</option>
                            @foreach($subJenisUjians as $subJenisUjian)
                                <option value="{{ $subJenisUjian->id }}">{{ $subJenisUjian->jenisUjian?->nama_jenis_ujian }} - {{ $subJenisUjian->nama_sub_jenis_ujian }}</option>
                            @endforeach
                        </select>
                        @error('sub_jenis_ujian_id')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">Nama Sub Indikator <span class="text-danger-500">*</span></label>
                        <input type="text" name="nama_sub_indikator" x-model="form.nama_sub_indikator"
                               class="input w-full @error('nama_sub_indikator') border-danger-500 @enderror"
                               placeholder="Hukum Perdata" required>
                        @error('nama_sub_indikator')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="modal-footer flex items-center justify-end gap-3">
                    <button type="button" @click="open = false" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary" x-text="mode === 'edit' ? 'Simpan Perubahan' : 'Simpan'"></button>
                </div>
            </form>
        </div>
    </div>
@endsection
