@extends('superadmin.layouts.app')

@section('title', 'Paket Member')

@section('breadcrumb')
    <span class="text-secondary-900 font-medium">Paket Member</span>
@endsection

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Paket Member</h1>
            <p class="text-secondary-500 mt-1">Kelola paket langganan peserta</p>
        </div>
        <a href="{{ route('superadmin.paket.create') }}" class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Paket
        </a>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-body-sm">
            <x-table>
                <x-slot name="header">
                    <th class="px-6 py-3 text-left">Paket</th>
                    <th class="px-6 py-3 text-left">Harga &amp; Durasi</th>
                    <th class="px-6 py-3 text-center">Fitur</th>
                    <th class="px-6 py-3 text-center">Status</th>
                    <th class="px-6 py-3 text-center">Pelanggan</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </x-slot>

                @forelse($pakets as $paket)
                    <tr class="hover:bg-secondary-50">
                        <td class="px-6 py-4">
                            <p class="font-bold text-secondary-900">{{ $paket->nama_paket }}</p>
                            <p class="text-xs text-secondary-500">Urutan: {{ $paket->urutan }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-semibold text-primary-600">Rp {{ number_format($paket->harga, 0, ',', '.') }}</p>
                            <p class="text-xs text-secondary-500">{{ $paket->durasi_hari }} Hari</p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1 text-xs text-secondary-600">
                                <span>Kuota: {{ $paket->kuota_ujian ?? '∞' }}</span>
                                @if($paket->video_pembahasan) <span class="text-success-600">&check; Video</span> @endif
                                @if($paket->analitik) <span class="text-success-600">&check; Analitik</span> @endif
                                @if($paket->sertifikat) <span class="text-success-600">&check; Sertifikat</span> @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($paket->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-700">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary-100 text-secondary-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center text-sm font-medium text-secondary-700">{{ $paket->langganan_count }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('superadmin.paket.edit', $paket) }}" class="btn btn-ghost btn-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <button type="button"
                                        @click="$dispatch('confirm-dialog', {
                                            title: 'Hapus Paket',
                                            message: 'Apakah Anda yakin ingin menghapus paket {{ $paket->nama_paket }}?',
                                            confirmText: 'Ya, Hapus',
                                            type: 'danger',
                                            formAction: '{{ route('superadmin.paket.destroy', $paket) }}'
                                        })"
                                        class="btn btn-ghost btn-sm text-danger-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-secondary-500">Belum ada paket member</td></tr>
                @endforelse
            </x-table>
        </div>
        @if($pakets->hasPages())<div class="card-footer">{{ $pakets->links() }}</div>@endif
    </div>
@endsection
