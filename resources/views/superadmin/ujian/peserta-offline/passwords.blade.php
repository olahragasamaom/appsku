@extends('superadmin.layouts.app')

@section('title', 'Daftar Kode Akses Peserta')

@section('breadcrumb')
    <a href="{{ route('superadmin.ujian.index') }}" class="text-secondary-500 hover:text-secondary-700">Manajemen Ujian</a>
    <span class="mx-2 text-secondary-400">/</span>
    <a href="{{ route('superadmin.ujian.peserta-offline.index', $ujian) }}" class="text-secondary-500 hover:text-secondary-700">Peserta Offline</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Daftar Kode Akses</span>
@endsection

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h2 class="font-semibold text-xl text-secondary-800">Daftar Kode Akses Peserta</h2>
            <p class="text-sm text-secondary-500">{{ $ujian->nama_ujian }}</p>
        </div>
        <div class="flex gap-2">
            <button onclick="window.print()" class="btn btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak
            </button>
            <a href="{{ route('superadmin.ujian.peserta-offline.index', $ujian) }}" class="btn btn-ghost">Kembali</a>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="card">
            <div class="card-body">
                @if($peserta->isEmpty())
                    <div class="text-center py-8 text-secondary-500">
                        <p>Belum ada peserta untuk ujian ini.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b-2 border-secondary-200 bg-secondary-50">
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-secondary-700 w-12">No</th>
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-secondary-700">Nomor Peserta</th>
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-secondary-700">Nama Peserta</th>
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-secondary-700 font-mono">Kode Akses</th>
                                    <th class="text-center py-3 px-4 text-sm font-semibold text-secondary-700">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($peserta as $index => $p)
                                    <tr class="border-b border-secondary-100 hover:bg-secondary-50 print:border-secondary-200">
                                        <td class="py-3 px-4 text-sm text-secondary-700">
                                            {{ ($peserta->currentPage() - 1) * $peserta->perPage() + $index + 1 }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <strong class="text-secondary-800 font-mono">{{ $p->nomor_peserta }}</strong>
                                        </td>
                                        <td class="py-3 px-4 text-secondary-700">
                                            {{ $p->nama_peserta }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <code class="bg-slate-100 px-2 py-1 rounded text-sm font-mono text-secondary-900 select-all">{{ $p->kode_akses_plain }}</code>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if($p->is_active)
                                                <x-badge type="success">Aktif</x-badge>
                                            @else
                                                <x-badge type="secondary">Nonaktif</x-badge>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($peserta->hasPages())
                        <div class="mt-6 print:hidden">
                            {{ $peserta->links() }}
                        </div>
                    @endif

                    <div class="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-lg print:bg-white print:border-secondary-200">
                        <p class="text-sm text-amber-900 font-semibold flex items-start gap-2">
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Kode akses di atas untuk peserta mengakses ujian. Simpan dengan aman dan bagikan kepada peserta sebelum ujian dimulai.</span>
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style media="print">
        body {
            background: white;
        }
        .print\:hidden {
            display: none !important;
        }
        table {
            page-break-inside: avoid;
        }
        tr {
            page-break-inside: avoid;
        }
    </style>
@endsection
