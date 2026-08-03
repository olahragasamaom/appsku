@extends('superadmin.layouts.app')

@section('title', 'Manajemen Ujian')

@section('breadcrumb')
    <span class="text-secondary-900 font-medium">Manajemen Ujian</span>
@endsection

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Manajemen Ujian</h1>
            <p class="text-secondary-500 mt-1">Buat dan atur jadwal serta konfigurasi ujian</p>
        </div>
        <a href="{{ route('superadmin.ujian.create') }}" class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Ujian
        </a>
    </div>
@endsection

@section('content')
    <div class="card mb-6">
        <div class="card-body-sm">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="search" value="{{ request('search') }}"
                       class="input flex-1" placeholder="Cari nama ujian...">
                <select name="tipe_ujian" class="input sm:w-48">
                    <option value="">Semua Tipe</option>
                    <option value="offline_kelas" @selected(request('tipe_ujian') === 'offline_kelas')>Offline di Kelas</option>
                    <option value="online_paket" @selected(request('tipe_ujian') === 'online_paket')>Online Paket</option>
                </select>
                <select name="status" class="input sm:w-40">
                    <option value="">Semua Status</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                    <option value="selesai" @selected(request('status') === 'selesai')>Selesai</option>
                </select>
                <button type="submit" class="btn btn-secondary">Filter</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body-sm">
            <x-table>
                <x-slot name="header">
                    <th class="px-6 py-3 text-left">Nama Ujian</th>
                    <th class="px-6 py-3 text-left">Tipe</th>
                    <th class="px-6 py-3 text-left">Jenis Ujian</th>
                    <th class="px-6 py-3 text-center">Soal</th>
                    <th class="px-6 py-3 text-center">Peserta</th>
                    <th class="px-6 py-3 text-center">Status</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </x-slot>

                @forelse($ujians as $ujian)
                    <tr class="hover:bg-secondary-50">
                        <td class="px-6 py-4">
                            <p class="font-medium text-secondary-900">{{ $ujian->nama_ujian }}</p>
                            @if($ujian->tanggal_ujian)
                                <p class="text-xs text-secondary-500">{{ $ujian->tanggal_ujian->format('d M Y H:i') }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-secondary-600">
                            {{ $ujian->tipe_ujian === 'offline_kelas' ? 'Offline di Kelas' : 'Online Paket' }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach($ujian->jenisUjians as $jenis)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-700">{{ $jenis->nama_jenis_ujian }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-secondary-600">{{ $ujian->ujian_soals_count }}/{{ $ujian->jumlah_soal }}</td>
                        <td class="px-6 py-4 text-center text-sm text-secondary-600">{{ $ujian->peserta_count }}</td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $badge = match($ujian->status) {
                                    'aktif' => 'bg-success-100 text-success-700',
                                    'selesai' => 'bg-secondary-100 text-secondary-700',
                                    default => 'bg-warning-100 text-warning-700',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">{{ ucfirst($ujian->status) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('superadmin.ujian.soal.index', $ujian) }}" class="btn btn-ghost btn-sm" title="Kelola Soal">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                    </svg>
                                </a>
                                <a href="{{ route('superadmin.ujian.peserta.index', $ujian) }}" class="btn btn-ghost btn-sm" title="Peserta">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-3-6.65"/>
                                    </svg>
                                </a>
                                <a href="{{ route('superadmin.ujian.monitoring.live', $ujian) }}" class="btn btn-ghost btn-sm" title="Live Scoring">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                    </svg>
                                </a>
                                <a href="{{ route('superadmin.ujian.edit', $ujian) }}" class="btn btn-ghost btn-sm" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <button type="button"
                                        @click="$dispatch('confirm-dialog', {
                                            title: 'Hapus Ujian',
                                            message: 'Apakah Anda yakin ingin menghapus ujian {{ $ujian->nama_ujian }}?',
                                            confirmText: 'Ya, Hapus',
                                            type: 'danger',
                                            formAction: '{{ route('superadmin.ujian.destroy', $ujian) }}'
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
                        <td colspan="7" class="px-6 py-12 text-center text-secondary-500">
                            Belum ada ujian yang dibuat
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>

        @if($ujians->hasPages())
            <div class="card-footer">
                {{ $ujians->links() }}
            </div>
        @endif
    </div>
@endsection
