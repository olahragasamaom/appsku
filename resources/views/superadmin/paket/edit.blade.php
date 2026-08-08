@extends('superadmin.layouts.app')

@section('title', 'Edit Paket Member')

@section('breadcrumb')
    <a href="{{ route('superadmin.paket.index') }}" class="text-secondary-500 hover:text-secondary-700">Paket Member</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Edit Paket</span>
@endsection

@section('header')
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Edit Paket Member</h1>
            <p class="text-secondary-500 mt-1">Perbarui data paket {{ $paket->nama_paket }}</p>
        </div>
        <a href="{{ route('superadmin.paket.index') }}" class="btn btn-ghost">Kembali</a>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('superadmin.paket.update', $paket) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Nama Paket --}}
                    <div>
                        <label for="nama_paket" class="block text-sm font-medium text-secondary-700 mb-1">
                            Nama Paket <span class="text-danger-500">*</span>
                        </label>
                        <input type="text" name="nama_paket" id="nama_paket"
                               value="{{ old('nama_paket', $paket->nama_paket) }}"
                               class="input w-full @error('nama_paket') border-danger-500 @enderror"
                               required>
                        @error('nama_paket')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Harga --}}
                    <div>
                        <label for="harga" class="block text-sm font-medium text-secondary-700 mb-1">
                            Harga (Rp) <span class="text-danger-500">*</span>
                        </label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 14px; pointer-events: none;">Rp</span>
                            <input type="number" name="harga" id="harga" min="0" step="1000"
                                   value="{{ old('harga', $paket->harga) }}"
                                   class="input w-full @error('harga') border-danger-500 @enderror"
                                   style="padding-left: 36px;" required>
                        </div>
                        <p class="mt-1 text-sm text-secondary-500">Isi 0 untuk paket gratis.</p>
                        @error('harga')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Durasi Hari --}}
                    <div>
                        <label for="durasi_hari" class="block text-sm font-medium text-secondary-700 mb-1">
                            Durasi Langganan (Hari) <span class="text-danger-500">*</span>
                        </label>
                        <input type="number" name="durasi_hari" id="durasi_hari" min="1"
                               value="{{ old('durasi_hari', $paket->durasi_hari) }}"
                               class="input w-full @error('durasi_hari') border-danger-500 @enderror"
                               required>
                        @error('durasi_hari')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kuota Ujian --}}
                    <div>
                        <label for="kuota_ujian" class="block text-sm font-medium text-secondary-700 mb-1">
                            Kuota Percobaan Ujian
                        </label>
                        <input type="number" name="kuota_ujian" id="kuota_ujian" min="1"
                               value="{{ old('kuota_ujian', $paket->kuota_ujian) }}"
                               class="input w-full @error('kuota_ujian') border-danger-500 @enderror">
                        <p class="mt-1 text-sm text-secondary-500">Kosongkan untuk unlimited (tanpa batas).</p>
                        @error('kuota_ujian')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Urutan --}}
                    <div>
                        <label for="urutan" class="block text-sm font-medium text-secondary-700 mb-1">
                            Urutan Tampil
                        </label>
                        <input type="number" name="urutan" id="urutan" min="0"
                               value="{{ old('urutan', $paket->urutan) }}"
                               class="input w-full @error('urutan') border-danger-500 @enderror">
                        <p class="mt-1 text-sm text-secondary-500">Semakin kecil, semakin awal ditampilkan.</p>
                        @error('urutan')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Deskripsi --}}
                    <div class="md:col-span-2">
                        <label for="deskripsi" class="block text-sm font-medium text-secondary-700 mb-1">
                            Deskripsi Singkat
                        </label>
                        <textarea name="deskripsi" id="deskripsi" rows="3"
                                  class="input w-full @error('deskripsi') border-danger-500 @enderror">{{ old('deskripsi', $paket->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Fitur Flags --}}
                <div class="mt-6 border-t border-secondary-100 pt-6">
                    <h3 class="text-lg font-medium text-secondary-900 mb-4">Fitur Paket</h3>
                    <div class="flex flex-wrap gap-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="video_pembahasan" value="1" {{ old('video_pembahasan', $paket->video_pembahasan) ? 'checked' : '' }} class="rounded border-secondary-300 text-primary-600 shadow-sm focus:ring-primary-500">
                            <span class="ml-2 text-sm text-secondary-600">Video Pembahasan</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="analitik" value="1" {{ old('analitik', $paket->analitik) ? 'checked' : '' }} class="rounded border-secondary-300 text-primary-600 shadow-sm focus:ring-primary-500">
                            <span class="ml-2 text-sm text-secondary-600">Analitik Lanjutan</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="sertifikat" value="1" {{ old('sertifikat', $paket->sertifikat) ? 'checked' : '' }} class="rounded border-secondary-300 text-primary-600 shadow-sm focus:ring-primary-500">
                            <span class="ml-2 text-sm text-secondary-600">Sertifikat Kelulusan</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $paket->is_active) ? 'checked' : '' }} class="rounded border-secondary-300 text-primary-600 shadow-sm focus:ring-primary-500">
                            <span class="ml-2 text-sm text-secondary-600 font-medium">Paket Aktif (Tampil)</span>
                        </label>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="submit" class="btn btn-primary">Perbarui Paket</button>
                </div>
            </form>
        </div>
    </div>
@endsection
