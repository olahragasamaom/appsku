@extends('superadmin.layouts.app')

@section('title', 'Kelola Sub Jenis Ujian')

@section('breadcrumb')
    <span class="text-secondary-900 font-medium">Sub Jenis Ujian</span>
@endsection

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Kelola Sub Jenis Ujian</h1>
            <p class="text-secondary-500 mt-1">Daftar sub jenis ujian dikelompokkan per jenis ujian</p>
        </div>
        <button type="button"
                @click="$dispatch('sub-jenis-ujian-form', { mode: 'create' })"
                class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Sub Jenis Ujian
        </button>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        @forelse($jenisUjians as $jenisUjian)
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary-100 text-primary-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-lg font-semibold text-secondary-900">{{ $jenisUjian->nama_jenis_ujian }}</h2>
                            <p class="text-sm text-secondary-500">{{ $jenisUjian->subJenisUjian->count() }} sub jenis ujian</p>
                        </div>
                    </div>
                </div>

                <div class="card-body-sm">
                    @if($jenisUjian->subJenisUjian->isNotEmpty())
                        <x-table>
                            <x-slot name="header">
                                <th class="px-6 py-3 text-center w-20">Urutan</th>
                                <th class="px-6 py-3 text-left">Sub Jenis Ujian</th>
                                <th class="px-6 py-3 text-left">Keterangan</th>
                                <th class="px-6 py-3 text-left">Sistem Penilaian</th>
                                <th class="px-6 py-3 text-center">Jml Opsi</th>
                                <th class="px-6 py-3 text-center">Nilai Benar</th>
                                <th class="px-6 py-3 text-center">Aksi</th>
                            </x-slot>

                            @foreach($jenisUjian->subJenisUjian as $subJenisUjian)
                                <tr class="hover:bg-secondary-50">
                                    <td class="px-6 py-4 text-center text-secondary-700">{{ $subJenisUjian->urutan }}</td>
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-secondary-900">{{ $subJenisUjian->nama_sub_jenis_ujian }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-secondary-600">{{ $subJenisUjian->keterangan ?: '-' }}</td>
                                    <td class="px-6 py-4">
                                        <span class="badge {{ $subJenisUjian->sistem_penilaian === 'benar_salah' ? 'badge-primary' : 'badge-warning' }}">
                                            {{ $subJenisUjian->sistem_penilaian === 'benar_salah' ? 'Benar-Salah' : 'Poin per Jawaban' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center text-secondary-700">{{ $subJenisUjian->jumlah_jawaban_pilihan_ganda }}</td>
                                    <td class="px-6 py-4 text-center text-secondary-700">{{ $subJenisUjian->nilai_benar }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <button type="button"
                                                    @click="$dispatch('sub-jenis-ujian-form', {
                                                        mode: 'edit',
                                                        action: '{{ route('superadmin.sub-jenis-ujian.update', $subJenisUjian) }}',
                                                        data: {{ Js::from($subJenisUjian->only(['jenis_ujian_id', 'nama_sub_jenis_ujian', 'keterangan', 'urutan', 'sistem_penilaian', 'jumlah_jawaban_pilihan_ganda', 'nilai_benar'])) }}
                                                    })"
                                                    class="btn btn-ghost btn-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            <button type="button"
                                                    @click="$dispatch('confirm-dialog', {
                                                        title: 'Hapus Sub Jenis Ujian',
                                                        message: 'Apakah Anda yakin ingin menghapus {{ $subJenisUjian->nama_sub_jenis_ujian }}?',
                                                        confirmText: 'Ya, Hapus',
                                                        type: 'danger',
                                                        formAction: '{{ route('superadmin.sub-jenis-ujian.destroy', $subJenisUjian) }}'
                                                    })"
                                                    class="btn btn-ghost btn-sm text-danger-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </x-table>
                    @else
                        <p class="px-6 py-8 text-center text-secondary-500">Belum ada sub jenis ujian pada jenis ini</p>
                    @endif
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body py-12 text-center text-secondary-500">
                    Belum ada jenis ujian. Buat jenis ujian terlebih dahulu.
                </div>
            </div>
        @endforelse
    </div>

    <div
        x-data="{
            open: {{ $errors->any() ? 'true' : 'false' }},
            mode: '{{ old('_form_mode', 'create') }}',
            action: '{{ old('_form_action', route('superadmin.sub-jenis-ujian.store')) }}',
            form: {
                jenis_ujian_id: {{ Js::from(old('jenis_ujian_id', '')) }},
                nama_sub_jenis_ujian: {{ Js::from(old('nama_sub_jenis_ujian', '')) }},
                keterangan: {{ Js::from(old('keterangan', '')) }},
                urutan: {{ Js::from(old('urutan', '0')) }},
                sistem_penilaian: {{ Js::from(old('sistem_penilaian', 'benar_salah')) }},
                jumlah_jawaban_pilihan_ganda: {{ Js::from(old('jumlah_jawaban_pilihan_ganda', '5')) }},
                nilai_benar: {{ Js::from(old('nilai_benar', '5')) }},
            },
            show(detail) {
                this.mode = detail.mode;
                this.action = detail.mode === 'edit' ? detail.action : '{{ route('superadmin.sub-jenis-ujian.store') }}';
                if (detail.mode === 'edit') {
                    this.form = {
                        jenis_ujian_id: detail.data.jenis_ujian_id,
                        nama_sub_jenis_ujian: detail.data.nama_sub_jenis_ujian,
                        keterangan: detail.data.keterangan ?? '',
                        urutan: String(detail.data.urutan ?? 0),
                        sistem_penilaian: detail.data.sistem_penilaian,
                        jumlah_jawaban_pilihan_ganda: String(detail.data.jumlah_jawaban_pilihan_ganda),
                        nilai_benar: detail.data.nilai_benar,
                    };
                } else {
                    this.form = { jenis_ujian_id: '', nama_sub_jenis_ujian: '', keterangan: '', urutan: '0', sistem_penilaian: 'benar_salah', jumlah_jawaban_pilihan_ganda: '5', nilai_benar: '5' };
                }
                this.open = true;
            }
        }"
        x-on:sub-jenis-ujian-form.window="show($event.detail)"
        x-on:keydown.escape.window="open = false"
        x-effect="document.body.style.overflow = open ? 'hidden' : ''"
        x-show="open"
        x-cloak
        class="modal-backdrop"
    >
        <div @click.outside="open = false" x-show="open" class="modal">
            <div class="modal-header">
                <h3 class="modal-title" x-text="mode === 'edit' ? 'Edit Sub Jenis Ujian' : 'Tambah Sub Jenis Ujian'"></h3>
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
                        <label class="block text-sm font-medium text-secondary-700 mb-1">Jenis Ujian <span class="text-danger-500">*</span></label>
                        <select name="jenis_ujian_id" x-model="form.jenis_ujian_id"
                                class="input w-full @error('jenis_ujian_id') border-danger-500 @enderror" required>
                            <option value="">-- Pilih Jenis Ujian --</option>
                            @foreach($jenisUjians as $jenisUjian)
                                <option value="{{ $jenisUjian->id }}">{{ $jenisUjian->nama_jenis_ujian }}</option>
                            @endforeach
                        </select>
                        @error('jenis_ujian_id')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-secondary-700 mb-1">Nama Sub Jenis Ujian <span class="text-danger-500">*</span></label>
                            <input type="text" name="nama_sub_jenis_ujian" x-model="form.nama_sub_jenis_ujian"
                                   class="input w-full @error('nama_sub_jenis_ujian') border-danger-500 @enderror"
                                   placeholder="Hukum Materil" required>
                            @error('nama_sub_jenis_ujian')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-1">Urutan</label>
                            <input type="number" min="0" name="urutan" x-model="form.urutan"
                                   class="input w-full @error('urutan') border-danger-500 @enderror">
                            @error('urutan')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">Keterangan</label>
                        <textarea name="keterangan" x-model="form.keterangan" rows="3"
                                  class="input w-full @error('keterangan') border-danger-500 @enderror"
                                  placeholder="Keterangan sub jenis ujian (opsional)"></textarea>
                        @error('keterangan')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-2">Sistem Penilaian <span class="text-danger-500">*</span></label>
                        <div class="flex flex-col gap-2">
                            <label class="flex items-center gap-2">
                                <input type="radio" name="sistem_penilaian" value="benar_salah" x-model="form.sistem_penilaian">
                                <span class="text-sm text-secondary-700">Benar-Salah</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="radio" name="sistem_penilaian" value="tiap_jawaban_ada_poin" x-model="form.sistem_penilaian">
                                <span class="text-sm text-secondary-700">Tiap Jawaban Ada Poin</span>
                            </label>
                        </div>
                        @error('sistem_penilaian')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-1">Jumlah Opsi Jawaban <span class="text-danger-500">*</span></label>
                            <select name="jumlah_jawaban_pilihan_ganda" x-model="form.jumlah_jawaban_pilihan_ganda" class="input w-full" required>
                                <option value="4">4 (A-D)</option>
                                <option value="5">5 (A-E)</option>
                            </select>
                            @error('jumlah_jawaban_pilihan_ganda')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
                        </div>
                        <div x-show="form.sistem_penilaian === 'benar_salah'">
                            <label class="block text-sm font-medium text-secondary-700 mb-1">Nilai Benar (Default)</label>
                            <input type="number" step="0.01" name="nilai_benar" x-model="form.nilai_benar"
                                   class="input w-full @error('nilai_benar') border-danger-500 @enderror">
                            @error('nilai_benar')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
                        </div>
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
