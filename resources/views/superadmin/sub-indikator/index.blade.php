@extends('superadmin.layouts.app')

@section('title', 'Kelola Sub Indikator')

@section('breadcrumb')
    <span class="text-secondary-900 font-medium">Sub Indikator</span>
@endsection

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Kelola Sub Indikator</h1>
            <p class="text-secondary-500 mt-1">Daftar sub indikator (materi) yang dikelompokkan per sub jenis ujian</p>
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
    <div class="space-y-6">
        @forelse($groupedSubIndikators as $namaJenisUjian => $subJenisGroups)
            <div class="card">
                <div class="card-header bg-slate-50 border-b border-slate-200">
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        Jenis Ujian: {{ $namaJenisUjian }}
                    </h2>
                </div>

                <div class="card-body-sm p-0">
                    @foreach($subJenisGroups as $namaSubJenis => $subIndikators)
                        @php $firstItem = $subIndikators->first(); @endphp
                        <div class="border-b border-slate-100 last:border-0 p-5">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                                <div>
                                    <h3 class="text-base font-semibold text-slate-800">{{ $namaSubJenis }}</h3>
                                    <p class="text-sm text-slate-500">{{ $subIndikators->count() }} sub indikator (materi)</p>
                                </div>
                                <button type="button"
                                        @click="$dispatch('sub-indikator-form', {
                                            mode: 'create',
                                            data: { sub_jenis_ujian_id: '{{ $firstItem ? $firstItem->sub_jenis_ujian_id : '' }}' }
                                        })"
                                        class="btn btn-secondary btn-sm">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Tambah Sub Indikator
                                </button>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                @foreach($subIndikators as $subIndikator)
                                    <div x-data="{
                                            menuOpen: false,
                                            menuX: 0,
                                            menuY: 0,
                                            toggle() {
                                                if (this.menuOpen) { this.menuOpen = false; return; }
                                                const rect = this.$refs.badge.getBoundingClientRect();
                                                this.menuX = rect.left;
                                                this.menuY = rect.bottom + 4;
                                                this.menuOpen = true;
                                            }
                                         }"
                                         @keydown.escape.window="menuOpen = false"
                                         @scroll.window="menuOpen = false"
                                         @resize.window="menuOpen = false">
                                        <button type="button"
                                                x-ref="badge"
                                                @click="toggle()"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-primary-50 hover:bg-primary-100 text-primary-700 font-medium text-sm transition-colors cursor-pointer border border-primary-100">
                                            {{ $subIndikator->nama_sub_indikator }}
                                            <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                        <template x-teleport="body">
                                            <div x-show="menuOpen"
                                                 x-cloak
                                                 @click.outside="menuOpen = false"
                                                 x-transition
                                                 :style="`position: fixed; left: ${menuX}px; top: ${menuY}px;`"
                                                 class="z-50 w-36 bg-white rounded-lg shadow-lg shadow-slate-200/50 border border-slate-200 py-1 overflow-hidden">
                                                <button type="button"
                                                        @click="menuOpen = false; $dispatch('sub-indikator-form', {
                                                            mode: 'edit',
                                                            action: '{{ route('superadmin.sub-indikator.update', $subIndikator) }}',
                                                            data: {{ Js::from($subIndikator->only(['sub_jenis_ujian_id', 'nama_sub_indikator'])) }}
                                                        })"
                                                        class="flex items-center gap-2 w-full px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                    Edit
                                                </button>
                                                <button type="button"
                                                        @click="menuOpen = false; $dispatch('confirm-dialog', {
                                                            title: 'Hapus Sub Indikator',
                                                            message: 'Apakah Anda yakin ingin menghapus {{ $subIndikator->nama_sub_indikator }}?',
                                                            confirmText: 'Ya, Hapus',
                                                            type: 'danger',
                                                            formAction: '{{ route('superadmin.sub-indikator.destroy', $subIndikator) }}'
                                                        })"
                                                        class="flex items-center gap-2 w-full px-4 py-2 text-sm text-danger-600 hover:bg-danger-50 transition-colors">
                                                    <svg class="w-4 h-4 text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    Hapus
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body py-12 text-center text-slate-500">
                    Belum ada sub indikator yang dibuat.
                </div>
            </div>
        @endforelse
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
                    this.form = { sub_jenis_ujian_id: detail.data?.sub_jenis_ujian_id ?? '', nama_sub_indikator: '' };
                }
                this.open = true;
            }
        }"
        x-on:sub-indikator-form.window="show($event.detail)"
        x-on:keydown.escape.window="open = false"
        x-effect="document.body.style.overflow = open ? 'hidden' : ''"
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

            <form :action="action" method="POST" class="flex flex-col flex-1 min-h-0">
                @csrf
                <template x-if="mode === 'edit'">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <input type="hidden" name="_form_mode" :value="mode">
                <input type="hidden" name="_form_action" :value="action">

                <div class="modal-body space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Sub Jenis Ujian <span class="text-danger-500">*</span></label>
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
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nama Sub Indikator <span class="text-danger-500">*</span></label>
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
