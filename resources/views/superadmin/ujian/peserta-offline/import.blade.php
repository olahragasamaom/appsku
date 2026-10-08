@extends('superadmin.layouts.app')

@section('title', 'Impor Peserta Offline')

@section('breadcrumb')
    <a href="{{ route('superadmin.ujian.index') }}" class="text-secondary-500 hover:text-secondary-700">Manajemen Ujian</a>
    <span class="mx-2 text-secondary-400">/</span>
    <a href="{{ route('superadmin.ujian.peserta-offline.index', $ujian) }}" class="text-secondary-500 hover:text-secondary-700">Peserta Offline</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Impor Peserta</span>
@endsection

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h2 class="font-semibold text-xl text-secondary-800">Impor Peserta dari Excel</h2>
            <p class="text-sm text-secondary-500">{{ $ujian->nama_ujian }}</p>
        </div>
        <a href="{{ route('superadmin.ujian.peserta-offline.index', $ujian) }}" class="btn btn-ghost">Kembali</a>
    </div>
@endsection

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">
        @if(session('success'))
            <x-alert type="success" dismissible>{{ session('success') }}</x-alert>
        @endif

        @if(session('error'))
            <x-alert type="danger" dismissible>{{ session('error') }}</x-alert>
        @endif

        @if(session('import_errors'))
            <x-alert type="warning">
                <p class="font-medium mb-1">Beberapa baris dilewati:</p>
                <ul class="list-disc list-inside text-sm space-y-0.5">
                    @foreach(session('import_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Upload File Excel/CSV</h3>
            </div>
            <div class="card-body space-y-6">
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-900">
                        <strong>Cara menggunakan:</strong> Unduh template terlebih dahulu, isi kolom 
                        <strong>Nomor Peserta</strong> dan <strong>Nama Peserta</strong>, lalu unggah kembali. 
                        Kode akses akan dibuat otomatis untuk setiap peserta.
                    </p>
                </div>

                <form action="{{ route('superadmin.ujian.peserta-offline.import', $ujian) }}" method="POST"
                      enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label for="file" class="block text-sm font-medium text-secondary-700 mb-2">
                            File Excel/CSV <span class="text-danger-500">*</span>
                        </label>
                        <div class="relative border-2 border-dashed border-secondary-300 rounded-lg p-6 text-center hover:border-primary-400 transition-colors"
                             id="dropZone">
                            <svg class="w-12 h-12 text-secondary-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-secondary-700 font-medium mb-1">Seret file ke sini atau klik untuk memilih</p>
                            <p class="text-xs text-secondary-500">Format: .xlsx, .xls, atau .csv</p>
                            <input type="file" name="file" id="file" accept=".xlsx,.xls,.csv"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                   @change="handleFileSelect"
                                   required>
                        </div>
                        <div id="fileName" class="mt-2 text-sm text-secondary-600"></div>
                        @error('file')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="btn btn-primary flex-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Impor Peserta
                        </button>
                        <a href="{{ route('superadmin.ujian.peserta-offline.template') }}" class="btn btn-secondary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Unduh Template
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Petunjuk Pengisian Template</h3>
            </div>
            <div class="card-body">
                <ol class="list-decimal list-inside space-y-2 text-sm text-secondary-700">
                    <li>Unduh template Excel dengan tombol "Unduh Template"</li>
                    <li>Buka file template di Microsoft Excel atau aplikasi spreadsheet lainnya</li>
                    <li>Isi kolom berikut:
                        <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                            <li><strong>Nomor Peserta</strong> - Nomor identitas peserta (contoh: 001, 002, dst)</li>
                            <li><strong>Nama Peserta</strong> - Nama lengkap peserta</li>
                        </ul>
                    </li>
                    <li>Simpan file dengan format .xlsx atau .csv</li>
                    <li>Upload file yang telah diisi ke halaman ini</li>
                    <li>Kode akses akan dibuat otomatis untuk setiap peserta</li>
                </ol>
            </div>
        </div>
    </div>

    <script>
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('file');
        const fileName = document.getElementById('fileName');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.add('border-primary-400', 'bg-primary-50');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.remove('border-primary-400', 'bg-primary-50');
            }, false);
        });

        dropZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            handleFileSelect();
        }, false);

        fileInput.addEventListener('change', handleFileSelect, false);

        function handleFileSelect() {
            const file = fileInput.files[0];
            if (file) {
                fileName.textContent = `File terpilih: ${file.name} (${(file.size / 1024).toFixed(2)} KB)`;
            } else {
                fileName.textContent = '';
            }
        }
    </script>
@endsection
