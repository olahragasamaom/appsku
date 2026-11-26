@extends('superadmin.layouts.app')

@section('title', request('nama_kelas') ? 'Peserta Kelas — ' . request('nama_kelas') : 'Data Peserta Kelas')

@section('breadcrumb')
    <a href="{{ route('superadmin.kelas.index') }}" class="text-secondary-500 hover:text-secondary-700">Data Kelas</a>
    <span class="text-secondary-400 mx-2">/</span>
    <span class="text-secondary-900 font-medium">
        @if(request('nama_kelas'))
            Peserta Kelas: {{ request('nama_kelas') }}
        @else
            Data Peserta Kelas
        @endif
    </span>
@endsection

@section('header')
    @php
        $currentNamaKelas = request('nama_kelas');
        $currentKelasId = request('kelas_id');
        $createParams = array_filter([
            'nama_kelas' => $currentNamaKelas,
            'kelas_id' => $currentKelasId,
        ]);
    @endphp
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">
                @if($currentNamaKelas)
                    Data Peserta — {{ $currentNamaKelas }}
                @else
                    Data Peserta Kelas Offline
                @endif
            </h1>
           
        </div>
        <div class="flex flex-wrap items-center gap-2">

            <button type="button" @click="$dispatch('open-import-modal')" class="btn btn-secondary">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Impor Data Peserta
            </button>

            <a href="{{ route('superadmin.data-peserta-offline.create', $createParams) }}" class="btn btn-primary">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Peserta
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="{
        importOpen: false,
        fileName: '',
        handleFile(e) {
            const file = e.target.files[0];
            this.fileName = file ? file.name : '';
        }
    }"
    @open-import-modal.window="importOpen = true"
    class="space-y-6">

        {{-- Alerts --}}
        @if(session('success'))
            <x-alert type="success" dismissible>{{ session('success') }}</x-alert>
        @endif

        @if(session('error'))
            <x-alert type="danger" dismissible>{{ session('error') }}</x-alert>
        @endif

        @if(session('import_errors'))
            <x-alert type="warning" dismissible>
                <p class="font-medium mb-1">Catatan Baris Dilewati saat Impor:</p>
                <ul class="list-disc list-inside text-xs space-y-0.5">
                    @foreach(session('import_errors') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        {{-- Filter Card (Konsisten dengan Tabel Ujian) --}}
        <div class="card">
            <div class="card-body-sm space-y-3">
                <form method="GET" class="space-y-3">
                    <div>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="input w-full" placeholder="Cari nomor peserta, nama, kelas, no HP...">
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <select name="nama_kelas" class="input sm:w-64">
                            <option value="">Semua Kelas</option>
                            @foreach($kelasList as $kelas)
                                <option value="{{ $kelas->nama_kelas }}" @selected(request('nama_kelas') === $kelas->nama_kelas)>
                                    {{ $kelas->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                        <select name="status" class="input sm:w-40">
                            <option value="">Semua Status</option>
                            <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                            <option value="blocked" @selected(request('status') === 'blocked')>Diblokir</option>
                        </select>
                        <button type="submit" class="btn btn-secondary whitespace-nowrap">Filter</button>
                        @if(request()->anyFilled(['search', 'nama_kelas', 'status', 'kelas_id']))
                            <a href="{{ route('superadmin.data-peserta-offline.index') }}" class="btn btn-ghost whitespace-nowrap text-secondary-600">Reset</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- Main Table Card (Konsisten dengan Tabel Ujian) --}}
        <div class="card">
            <div class="card-body-sm">
                <x-table>
                    <x-slot name="header">
                        <th class="px-6 py-3 text-left">Nama Peserta</th>
                        <th class="px-6 py-3 text-left">Kelas / Gelombang</th>
                        <th class="px-6 py-3 text-left">No. Handphone</th>
                        <th class="px-6 py-3 text-center">Password</th>
                        <th class="px-6 py-3 text-center">Ujian</th>
                        <th class="px-6 py-3 text-center">Status</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </x-slot>

                    @forelse($peserta as $p)
                        <tr class="hover:bg-secondary-50">
                            <td class="px-6 py-4">
                                <p class="font-medium text-secondary-900">{{ $p->nama_peserta }}</p>
                                <p class="text-xs text-secondary-500 font-mono">{{ $p->nomor_peserta }}</p>
                            </td>
                            <td class="px-6 py-4">
                                @if($p->nama_kelas)
                                    <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700">
                                        {{ $p->nama_kelas }}
                                    </span>
                                @else
                                    <span class="text-xs text-secondary-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-secondary-600">
                                {{ $p->nomor_hp ?: '—' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg font-mono font-bold text-xs bg-slate-100 text-slate-700" title="Password / Kode Akses Peserta">
                                    {{ $p->kode_akses_plain ?? '••••••' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-secondary-100 text-secondary-800">
                                    {{ $p->ujians_count }} Ujian
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($p->is_blocked)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-danger-100 text-danger-800">
                                        Diblokir
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-success-100 text-success-800">
                                        Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    {{-- Edit Button (Kuning) --}}
                                    <a href="{{ route('superadmin.data-peserta-offline.edit', $p) }}"
                                       class="btn btn-sm bg-yellow-400 hover:bg-yellow-500 text-white border-0" title="Edit Peserta">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    {{-- Hapus Button (Merah) --}}
                                    <button type="button"
                                            @click="$dispatch('confirm-dialog', {
                                                title: 'Hapus Peserta',
                                                message: 'Apakah Anda yakin ingin menghapus peserta {{ $p->nama_peserta }} dari data master?',
                                                confirmText: 'Ya, Hapus',
                                                type: 'danger',
                                                formAction: '{{ route('superadmin.data-peserta-offline.destroy', $p) }}'
                                            })"
                                            class="btn btn-sm bg-danger-500 hover:bg-danger-600 text-white border-0" title="Hapus Peserta">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>

                                    {{-- Reset Password Button (Biru) --}}
                                    <button type="button"
                                            @click="$dispatch('confirm-dialog', {
                                                title: 'Reset Password Peserta',
                                                message: 'Reset password/kode akses peserta {{ $p->nama_peserta }}?',
                                                confirmText: 'Ya, Reset',
                                                type: 'primary',
                                                method: 'POST',
                                                formAction: '{{ route('superadmin.data-peserta-offline.reset-password', $p) }}'
                                            })"
                                            class="btn btn-primary btn-sm" title="Reset Password / Kode Akses">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-secondary-500">
                                Belum ada data peserta offline yang terdaftar
                            </td>
                        </tr>
                    @endforelse
                </x-table>

                @if($peserta->hasPages())
                    <div class="px-6 py-4 border-t border-secondary-200">
                        {{ $peserta->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Modal Impor Data Peserta ke Kelas --}}
        <div x-show="importOpen"
             x-cloak
             x-on:keydown.escape.window="importOpen = false"
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.outside="importOpen = false"
                 class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200">
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Impor Data Peserta ke Kelas</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Unggah file Excel untuk memasukkan peserta ke dalam kelas</p>
                    </div>
                    <button type="button" @click="importOpen = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('superadmin.data-peserta-offline.import') }}"
                      enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf

                    {{-- Target Kelas --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Kelas / Gelombang Target <span class="text-danger-500">*</span>
                        </label>
                        @if($currentNamaKelas)
                            <input type="hidden" name="nama_kelas" value="{{ $currentNamaKelas }}">
                            <input type="hidden" name="kelas_id" value="{{ $currentKelasId }}">
                            <div class="flex items-center gap-2 p-2.5 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-900 font-medium">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <span>{{ $currentNamaKelas }}</span>
                                <span class="ml-auto text-xs bg-blue-200 text-blue-800 px-2 py-0.5 rounded font-semibold">Terkunci</span>
                            </div>
                        @else
                            <select name="kelas_id" class="input w-full text-sm" required>
                                <option value="">-- Pilih Kelas Tujuan --</option>
                                @foreach($kelasList as $kelas)
                                    <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    {{-- File Drop Area --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            File Excel (.xlsx, .xls, .csv) <span class="text-danger-500">*</span>
                        </label>
                        <div class="relative border-2 border-dashed border-slate-300 rounded-xl p-5 text-center hover:border-blue-400 bg-slate-50 transition-colors">
                            <svg class="w-8 h-8 text-slate-400 mx-auto mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-xs font-medium text-slate-700" x-text="fileName ? fileName : 'Pilih atau seret file Excel ke sini'"></p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Format: Nomor Peserta, Nama Peserta, No HP, Password</p>
                            <input type="file" name="file" accept=".xlsx,.xls,.csv" @change="handleFile"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required>
                        </div>
                    </div>

                    {{-- Informasi Penting --}}
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-900 space-y-1">
                        <div class="flex items-center gap-1.5 font-semibold text-amber-950">
                            <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Catatan Penting:</span>
                        </div>
                        <p class="text-amber-800 leading-relaxed">
                            Data yang diimpor di sini <strong>hanya sebagai data peserta di dalam kelas</strong>, bukan berarti otomatis menjadi peserta ujian. Untuk memasukkan ke ujian, gunakan menu perakitan di Manajemen Ujian.
                        </p>
                    </div>

                    {{-- Unduh Template Link --}}
                    <div class="flex items-center justify-between text-xs pt-1">
                        <span class="text-slate-500">Belum punya format file?</span>
                        <a href="{{ route('superadmin.data-peserta-offline.template') }}"
                           class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-800 font-semibold">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Unduh Template Excel
                        </a>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="importOpen = false" class="btn btn-secondary btn-sm">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            Mulai Impor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
