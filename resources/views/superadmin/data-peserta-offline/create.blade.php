@extends('superadmin.layouts.app')

@section('title', 'Tambah Peserta Offline')

@section('breadcrumb')
    <a href="{{ route('superadmin.data-peserta-offline.index') }}" class="text-secondary-500 hover:text-secondary-700">Data Peserta Offline</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Tambah Peserta</span>
@endsection

@php
    $backParams = array_filter([
        'nama_kelas' => $selectedNamaKelas ?? request('nama_kelas'),
        'kelas_id' => $selectedKelasId ?? request('kelas_id'),
    ]);
    $backUrl = route('superadmin.data-peserta-offline.index', $backParams);
@endphp

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Tambah Peserta Offline</h1>
            <p class="text-secondary-500 mt-1">
                @if(!empty($selectedNamaKelas))
                    Menambahkan peserta baru ke kelas <strong class="text-secondary-800">{{ $selectedNamaKelas }}</strong>
                @else
                    Masukkan data master peserta offline baru
                @endif
            </p>
        </div>
        <a href="{{ $backUrl }}" class="btn btn-secondary">
            &larr; Kembali
        </a>
    </div>
@endsection

@section('content')
    @if (isset($errors) && $errors->any())
        <div class="mb-6 p-4 bg-danger-50 border border-danger-200 rounded-lg">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-danger-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="font-semibold text-danger-900 mb-2">Form Error:</h3>
                    <ul class="text-sm text-danger-800 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('superadmin.data-peserta-offline.store') }}" method="POST" class="space-y-6">
        @csrf

        {{-- Card Parameter Utama Peserta --}}
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-secondary-900">Parameter Utama Peserta</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nomor_peserta" class="block text-sm font-medium text-secondary-700 mb-1">
                            Username / Nomor Peserta <span class="text-danger-500">*</span>
                        </label>
                        <input type="text" name="nomor_peserta" id="nomor_peserta"
                               value="{{ old('nomor_peserta') }}"
                               placeholder="Contoh: 01, P001, CPNS-01"
                               class="input w-full @error('nomor_peserta') border-danger-500 @enderror" required autofocus>
                        @error('nomor_peserta')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-secondary-500">Kredensial login peserta di portal ujian offline.</p>
                    </div>

                    <div>
                        <label for="nama_peserta" class="block text-sm font-medium text-secondary-700 mb-1">
                            Nama Lengkap Peserta <span class="text-danger-500">*</span>
                        </label>
                        <input type="text" name="nama_peserta" id="nama_peserta"
                               value="{{ old('nama_peserta') }}"
                               placeholder="Contoh: Ahmad Fauzi"
                               class="input w-full @error('nama_peserta') border-danger-500 @enderror" required>
                        @error('nama_peserta')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="kelas_id" class="block text-sm font-medium text-secondary-700">
                                Kelas / Gelombang Pelatihan <span class="text-danger-500">*</span>
                            </label>
                            @if(!empty($selectedNamaKelas))
                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                    ✓ Terisi Otomatis
                                </span>
                            @else
                                <a href="{{ route('superadmin.kelas.index') }}" target="_blank" class="text-xs text-primary-600 hover:underline">
                                    + Kelola Master Kelas
                                </a>
                            @endif
                        </div>
                        <select name="kelas_id" id="kelas_id" class="input w-full @error('kelas_id') border-danger-500 @enderror" {{ !empty($selectedKelasId) ? 'required' : '' }}>
                            <option value="">-- Pilih Kelas Terdaftar --</option>
                            @foreach($kelasList as $kelas)
                                <option value="{{ $kelas->id }}" @selected(old('kelas_id', $selectedKelasId) == $kelas->id)>
                                    {{ $kelas->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                        @error('kelas_id')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror

                        @if(empty($selectedKelasId))
                            <div class="mt-2 text-xs text-secondary-500">
                                <span>Atau ketik nama kelas baru jika belum ada:</span>
                            </div>
                            <input type="text" name="nama_kelas" id="nama_kelas"
                                   value="{{ old('nama_kelas') }}"
                                   placeholder="Ketik kelas baru di sini..."
                                   class="input w-full mt-1 text-sm @error('nama_kelas') border-danger-500 @enderror">
                            @error('nama_kelas')
                                <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>

                    <div>
                        <label for="nomor_hp" class="block text-sm font-medium text-secondary-700 mb-1">
                            Nomor Handphone / WhatsApp
                        </label>
                        <input type="text" name="nomor_hp" id="nomor_hp"
                               value="{{ old('nomor_hp') }}"
                               placeholder="Contoh: 08123456789"
                               class="input w-full @error('nomor_hp') border-danger-500 @enderror">
                        @error('nomor_hp')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-secondary-500">Kontak nomor aktif untuk informasi ujian/pelatihan.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-secondary-700 mb-1">
                            Password / Kode Akses
                        </label>
                        <input type="text" name="password" id="password"
                               value="{{ old('password') }}"
                               placeholder="Kosongkan jika ingin dibuatkan otomatis (6 karakter)"
                               class="input w-full font-mono @error('password') border-danger-500 @enderror">
                        @error('password')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-secondary-500">Kode akses untuk login peserta di ujian offline.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form Actions (Konsisten dengan Ujian) --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ $backUrl }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Peserta</button>
        </div>
    </form>
@endsection
