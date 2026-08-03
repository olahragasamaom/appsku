@extends('superadmin.layouts.app')

@section('title', 'Peserta Ujian')

@section('breadcrumb')
    <a href="{{ route('superadmin.ujian.index') }}" class="text-secondary-500 hover:text-secondary-700">Manajemen Ujian</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Peserta</span>
@endsection

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Peserta — {{ $ujian->nama_ujian }}</h1>
            <p class="text-secondary-500 mt-1">Kelola alokasi & absensi peserta ujian</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('superadmin.ujian.peserta.export.excel', $ujian) }}" class="btn btn-secondary btn-sm">Cetak Akun (Excel)</a>
            <a href="{{ route('superadmin.ujian.peserta.export.pdf', $ujian) }}" class="btn btn-secondary btn-sm">Cetak Akun (PDF)</a>
            <a href="{{ route('superadmin.ujian.peserta.available', $ujian) }}" class="btn btn-primary btn-sm">Tambah Peserta</a>
        </div>
    </div>
@endsection

@section('content')
    <div class="card mb-6">
        <div class="card-body-sm">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="search" value="{{ request('search') }}"
                       class="input flex-1" placeholder="Cari nama / username...">
                <button type="submit" class="btn btn-secondary">Cari</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body-sm">
            <x-table>
                <x-slot name="header">
                    <th class="px-6 py-3 text-left">Nama</th>
                    <th class="px-6 py-3 text-left">Username</th>
                    <th class="px-6 py-3 text-center">Status</th>
                    <th class="px-6 py-3 text-center">Nilai</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </x-slot>

                @forelse($peserta as $item)
                    <tr class="hover:bg-secondary-50">
                        <td class="px-6 py-4 font-medium text-secondary-900">{{ $item->user?->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-secondary-700">{{ $item->user?->username ?? '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $badge = match($item->status) {
                                    'diblokir' => 'bg-danger-100 text-danger-700',
                                    'sedang_ujian' => 'bg-warning-100 text-warning-700',
                                    'selesai' => 'bg-success-100 text-success-700',
                                    default => 'bg-secondary-100 text-secondary-600',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">{{ ucfirst(str_replace('_', ' ', $item->status)) }}</span>
                        </td>
                        <td class="px-6 py-4 text-center text-secondary-700">{{ $item->total_nilai ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <form method="POST" action="{{ route('superadmin.ujian.peserta.blokir', ['ujian' => $ujian, 'peserta' => $item->id]) }}">
                                    @csrf
                                    @method('PATCH')
                                    @if($item->status === 'diblokir')
                                        <button type="submit" class="btn btn-ghost btn-sm text-success-600" title="Aktifkan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-ghost btn-sm text-danger-600" title="Blokir">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        </button>
                                    @endif
                                </form>
                                <button type="button"
                                        @click="$dispatch('confirm-dialog', {
                                            title: 'Keluarkan Peserta',
                                            message: 'Keluarkan {{ $item->user?->name }} dari ujian ini?',
                                            confirmText: 'Ya, Keluarkan',
                                            type: 'danger',
                                            formAction: '{{ route('superadmin.ujian.peserta.destroy', ['ujian' => $ujian, 'peserta' => $item->id]) }}'
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
                        <td colspan="5" class="px-6 py-12 text-center text-secondary-500">
                            Belum ada peserta pada ujian ini
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>

        @if($peserta->hasPages())
            <div class="card-footer">
                {{ $peserta->links() }}
            </div>
        @endif
    </div>
@endsection
