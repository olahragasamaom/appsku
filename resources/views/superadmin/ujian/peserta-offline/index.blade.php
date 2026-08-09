@extends('superadmin.layouts.app')

@section('title', 'Peserta Offline')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h2 class="font-semibold text-xl text-secondary-800">Peserta Offline</h2>
            <p class="text-sm text-secondary-500">{{ $ujian->nama_ujian }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('superadmin.ujian.peserta-offline.export', $ujian) }}" class="btn btn-secondary">
                Cetak Kartu
            </a>
            <a href="{{ route('superadmin.ujian.index') }}" class="btn btn-ghost">Kembali</a>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">
        @if(session('kode_akses'))
            <x-alert type="success">
                Peserta <strong>{{ session('nomor_peserta') }}</strong> berhasil dibuat.
                Kode Akses: <strong>{{ session('kode_akses') }}</strong>
                (catat sekarang, kode ini tidak akan ditampilkan kembali).
            </x-alert>
        @endif

        @if($errors->any())
            <x-alert type="danger">{{ $errors->first() }}</x-alert>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tambah Peserta</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('superadmin.ujian.peserta-offline.store', $ujian) }}" method="POST"
                      class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    @csrf
                    <div>
                        <label for="nomor_peserta" class="block text-sm font-medium text-secondary-700 mb-1">
                            Nomor Peserta <span class="text-danger-500">*</span>
                        </label>
                        <input type="text" name="nomor_peserta" id="nomor_peserta"
                               value="{{ old('nomor_peserta') }}"
                               class="input w-full @error('nomor_peserta') border-danger-500 @enderror" required>
                    </div>
                    <div>
                        <label for="nama_peserta" class="block text-sm font-medium text-secondary-700 mb-1">
                            Nama Peserta <span class="text-danger-500">*</span>
                        </label>
                        <input type="text" name="nama_peserta" id="nama_peserta"
                               value="{{ old('nama_peserta') }}"
                               class="input w-full @error('nama_peserta') border-danger-500 @enderror" required>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary w-full">Tambah</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <x-table>
                    <x-slot name="header">
                        <th>Nomor Peserta</th>
                        <th>Nama Peserta</th>
                        <th>Kode Akses</th>
                        <th class="text-right">Aksi</th>
                    </x-slot>
                    @forelse($peserta as $item)
                        <tr>
                            <td>{{ $item->nomor_peserta }}</td>
                            <td>{{ $item->nama_peserta }}</td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-primary-50 text-primary-700 font-mono font-bold text-sm tracking-wider border border-primary-100">
                                    {{ $item->kode_akses_plain ?? '—' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <button type="button"
                                        @click="$dispatch('confirm-dialog', {
                                            title: 'Hapus Peserta',
                                            message: 'Yakin ingin menghapus peserta {{ $item->nama_peserta }}?',
                                            confirmText: 'Ya, Hapus',
                                            type: 'danger',
                                            formAction: '{{ route('superadmin.ujian.peserta-offline.destroy', [$ujian, $item]) }}'
                                        })"
                                        class="btn btn-ghost btn-sm text-danger-600">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-secondary-500 py-6">Belum ada peserta offline.</td>
                        </tr>
                    @endforelse
                </x-table>
            </div>
        </div>
    </div>
@endsection
