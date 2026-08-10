@extends('superadmin.layouts.app')
@section('title', 'Tambah Latihan Sederhana')

@section('breadcrumb')
    <a href="{{ route('superadmin.dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Dashboard</a>
    <span class="text-secondary-400">/</span>
    <a href="{{ route('superadmin.latihan-sederhana.index') }}" class="text-secondary-500 hover:text-secondary-700">Latihan Sederhana</a>
    <span class="text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Tambah Data</span>
@endsection

@section('header')
    <div>
        <h1 class="text-2xl font-bold text-secondary-900">Tambah Latihan Sederhana</h1>
        <p class="text-secondary-500 mt-1">Isi form di bawah untuk menambahkan data baru</p>
    </div>
@endsection

@section('content')
    <form action="{{ route('superadmin.latihan-sederhana.store') }}" method="POST">
        @csrf

        <div class="card">
            <div class="card-body space-y-6">
                {{-- Input 1: Judul --}}
                <div>
                    <label for="judul" class="block text-sm font-medium text-secondary-700 mb-1">
                        Judul <span class="text-danger-500">*</span>
                    </label>
                    <input type="text" 
                           name="judul" 
                           id="judul" 
                           value="{{ old('judul') }}"
                           class="input w-full @error('judul') border-danger-500 @enderror"
                           placeholder="Masukkan judul"
                           required>
                    @error('judul')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Input 2: Kode --}}
                <div>
                    <label for="kode" class="block text-sm font-medium text-secondary-700 mb-1">
                        Kode <span class="text-danger-500">*</span>
                    </label>
                    <input type="text" 
                           name="kode" 
                           id="kode" 
                           value="{{ old('kode') }}"
                           class="input w-full @error('kode') border-danger-500 @enderror"
                           placeholder="Masukkan kode"
                           required>
                    @error('kode')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Input 3: Penulis --}}
                <div>
                    <label for="penulis" class="block text-sm font-medium text-secondary-700 mb-1">
                        Penulis <span class="text-danger-500">*</span>
                    </label>
                    <input type="text" 
                           name="penulis" 
                           id="penulis" 
                           value="{{ old('penulis') }}"
                           class="input w-full @error('penulis') border-danger-500 @enderror"
                           placeholder="Masukkan nama penulis"
                           required>
                    @error('penulis')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Input 4: Keterangan --}}
                <div>
                    <label for="keterangan" class="block text-sm font-medium text-secondary-700 mb-1">
                        Keterangan
                    </label>
                    <textarea name="keterangan" 
                              id="keterangan" 
                              rows="3"
                              class="input w-full @error('keterangan') border-danger-500 @enderror"
                              placeholder="Masukkan keterangan (opsional)">{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Footer dengan tombol aksi --}}
            <div class="card-footer flex items-center justify-end gap-3">
                <a href="{{ route('superadmin.latihan-sederhana.index') }}" class="btn btn-secondary">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Data
                </button>
            </div>
        </div>
    </form>
@endsection
