@extends('superadmin.layouts.app')

@section('title', 'Kelola Kehadiran')

@section('breadcrumb')
    <a href="{{ route('superadmin.ujian.index') }}" class="text-secondary-500 hover:text-secondary-700">Manajemen Ujian</a>
    <span class="mx-2 text-secondary-400">/</span>
    <a href="{{ route('superadmin.ujian.peserta-offline.index', $ujian) }}" class="text-secondary-500 hover:text-secondary-700">Peserta Offline</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Kelola Kehadiran</span>
@endsection

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h2 class="font-semibold text-xl text-secondary-800">Kelola Kehadiran</h2>
            <p class="text-sm text-secondary-500">{{ $ujian->nama_ujian }}</p>
        </div>
        <a href="{{ route('superadmin.ujian.peserta-offline.index', $ujian) }}" class="btn btn-ghost">Kembali</a>
    </div>
@endsection

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">
        @if(session('success'))
            <x-alert type="success">{{ session('success') }}</x-alert>
        @endif

        @if(session('error'))
            <x-alert type="danger">{{ session('error') }}</x-alert>
        @endif

        <div class="card">
            <div class="card-body">
                @if($pesertaOffline->isEmpty())
                    <div class="text-center py-8 text-secondary-500">
                        <p>Belum ada peserta untuk ujian ini.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-secondary-200">
                                    <th class="text-left py-3 px-4 text-sm font-medium text-secondary-700">Nomor Peserta</th>
                                    <th class="text-left py-3 px-4 text-sm font-medium text-secondary-700">Nama Peserta</th>
                                    <th class="text-center py-3 px-4 text-sm font-medium text-secondary-700">Status</th>
                                    <th class="text-center py-3 px-4 text-sm font-medium text-secondary-700">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pesertaOffline as $peserta)
                                    @php
                                        $kehadiran = $peserta->kehadiran->first();
                                        $isHadir = $kehadiran && $kehadiran->status_kehadiran === 'hadir';
                                    @endphp
                                    <tr class="border-b border-secondary-100 hover:bg-secondary-50">
                                        <td class="py-3 px-4">
                                            <strong class="text-secondary-800">{{ $peserta->nomor_peserta }}</strong>
                                        </td>
                                        <td class="py-3 px-4 text-secondary-700">
                                            {{ $peserta->nama_peserta }}
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if($isHadir)
                                                <x-badge type="success">✓ Hadir</x-badge>
                                            @else
                                                <x-badge type="secondary">Belum Hadir</x-badge>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="inline-flex gap-2">
                                                <form method="POST"
                                                      action="{{ route('superadmin.ujian.peserta-offline.kehadiran.update', [$ujian, $peserta]) }}"
                                                      class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status_kehadiran" value="hadir">
                                                    <button type="submit"
                                                            class="btn btn-sm {{ $isHadir ? 'btn-success' : 'btn-ghost' }}">
                                                        Hadir
                                                    </button>
                                                </form>

                                                <form method="POST"
                                                      action="{{ route('superadmin.ujian.peserta-offline.kehadiran.update', [$ujian, $peserta]) }}"
                                                      class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status_kehadiran" value="tidak_hadir">
                                                    <button type="submit"
                                                            class="btn btn-sm {{ !$isHadir ? 'btn-secondary' : 'btn-ghost' }}">
                                                        Tidak Hadir
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($pesertaOffline->hasPages())
                        <div class="mt-4">
                            {{ $pesertaOffline->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection
