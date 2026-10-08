@extends('superadmin.layouts.app')

@section('title', 'Daftar Peserta - '.$ujian->nama_ujian)

@section('breadcrumb')
    <a href="{{ route('superadmin.hasil-ujian.index') }}" class="text-secondary-500 hover:text-secondary-700">Hasil Ujian</a>
    <span class="mx-2 text-secondary-400">/</span>
    <a href="{{ route('superadmin.hasil-ujian.show', $ujian) }}" class="text-secondary-500 hover:text-secondary-700">{{ $ujian->nama_ujian }}</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Daftar Peserta</span>
@endsection

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h2 class="font-semibold text-xl text-secondary-800">Daftar Peserta</h2>
            <p class="text-sm text-secondary-500">{{ $ujian->nama_ujian }}</p>
        </div>
        <a href="{{ route('superadmin.hasil-ujian.show', $ujian) }}" class="btn btn-ghost">Kembali</a>
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">
        @if(session('success'))
            <x-alert type="success" dismissible>{{ session('success') }}</x-alert>
        @endif
        @if(session('info'))
            <x-alert type="info" dismissible>{{ session('info') }}</x-alert>
        @endif

        {{-- Statistics --}}
        @php
            $selesaiCount = $pesertaOfflineList->where('status', 'selesai')->count();
            $sedangCount = $pesertaOfflineList->where('status', 'sedang_ujian')->count();
            $belumCount = $pesertaOfflineList->where('status', 'belum_ujian')->count();
        @endphp
        <div class="grid grid-cols-3 gap-4">
            <div class="card bg-success-50 border-success-100">
                <div class="card-body text-center">
                    <p class="text-sm text-success-700">Selesai</p>
                    <p class="text-3xl font-bold text-success-900">{{ $selesaiCount }}</p>
                </div>
            </div>
            <div class="card bg-warning-50 border-warning-100">
                <div class="card-body text-center">
                    <p class="text-sm text-warning-700">Sedang Ujian</p>
                    <p class="text-3xl font-bold text-warning-900">{{ $sedangCount }}</p>
                </div>
            </div>
            <div class="card bg-slate-50 border-slate-200">
                <div class="card-body text-center">
                    <p class="text-sm text-slate-700">Belum Ujian</p>
                    <p class="text-3xl font-bold text-slate-900">{{ $belumCount }}</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body-sm">
                <x-table>
                    <x-slot name="header">
                        <th class="px-6 py-3 text-left">Nomor Peserta</th>
                        <th class="px-6 py-3 text-left">Nama</th>
                        <th class="px-6 py-3 text-center">Status</th>
                        <th class="px-6 py-3 text-center">Waktu Mulai</th>
                        <th class="px-6 py-3 text-center">Nilai</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </x-slot>
                    @forelse($pesertaOfflineList as $p)
                        <tr class="hover:bg-secondary-50">
                            <td class="px-6 py-4 font-mono font-semibold text-secondary-900">{{ $p['nomor_peserta'] }}</td>
                            <td class="px-6 py-4 text-secondary-700">{{ $p['nama_peserta'] }}</td>
                            <td class="px-6 py-4 text-center">
                                <x-badge type="{{ $p['status_color'] }}">{{ $p['status_label'] }}</x-badge>
                            </td>
                            <td class="px-6 py-4 text-center text-sm text-secondary-600">
                                {{ $p['waktu_mulai']?->format('d M Y H:i') ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-center font-semibold text-secondary-900">
                                {{ $p['total_nilai'] !== null ? number_format($p['total_nilai'], 2) : '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($p['status'] === 'selesai')
                                    <a href="{{ route('superadmin.hasil-ujian.review-peserta', ['ujian' => $ujian, 'peserta' => $p['attempt']->id]) }}" class="text-primary-600 text-sm hover:underline font-medium">Review Jawaban</a>
                                @elseif($p['status'] === 'sedang_ujian')
                                    <form action="{{ route('superadmin.hasil-ujian.force-finalize', ['ujian' => $ujian, 'peserta' => $p['attempt']->id]) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-warning-600 text-sm hover:underline font-medium"
                                                onclick="return confirm('Yakin ingin finalisasi ujian peserta ini? Jawaban yang sudah tersimpan akan dihitung sebagai hasil akhir.')">
                                            Finalisasi Paksa
                                        </button>
                                    </form>
                                @else
                                    <span class="text-secondary-400 text-sm">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-12 text-center text-secondary-500">Belum ada peserta terdaftar</td></tr>
                    @endforelse
                </x-table>
            </div>
        </div>

        @if($sedangCount > 0)
            <div class="card bg-warning-50/50 border-warning-200">
                <div class="card-body">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-warning-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <p class="font-semibold text-warning-900 mb-1">Info Finalisasi Paksa</p>
                            <p class="text-sm text-warning-800">
                                Jika ada peserta yang stuck di status "Sedang Ujian" (misalnya karena layar mati, 
                                internet terputus), Anda dapat memaksa finalisasi dengan tombol di atas. 
                                Semua jawaban yang sudah tersimpan akan dihitung sebagai hasil akhir.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
