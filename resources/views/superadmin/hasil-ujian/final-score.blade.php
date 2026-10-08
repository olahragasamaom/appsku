@extends('superadmin.layouts.app')

@section('title', 'Skor Akhir - '.$ujian->nama_ujian)

@section('breadcrumb')
    <a href="{{ route('superadmin.hasil-ujian.index') }}" class="text-secondary-500 hover:text-secondary-700">Hasil Ujian</a>
    <span class="mx-2 text-secondary-400">/</span>
    <a href="{{ route('superadmin.hasil-ujian.show', $ujian) }}" class="text-secondary-500 hover:text-secondary-700">{{ $ujian->nama_ujian }}</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Skor Akhir</span>
@endsection

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h2 class="font-semibold text-xl text-secondary-800">Skor Akhir & Ranking</h2>
            <p class="text-sm text-secondary-500">{{ $ujian->nama_ujian }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('superadmin.ujian.monitoring.ranking.export.excel', $ujian) }}" class="btn btn-secondary">Export Excel</a>
            <a href="{{ route('superadmin.ujian.monitoring.ranking.export.pdf', $ujian) }}" class="btn btn-secondary">Export PDF</a>
            <a href="{{ route('superadmin.hasil-ujian.show', $ujian) }}" class="btn btn-ghost">Kembali</a>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto">
        <div class="card">
            <div class="card-body-sm">
                <x-table>
                    <x-slot name="header">
                        <th class="px-6 py-3 text-left w-16">Rank</th>
                        <th class="px-6 py-3 text-left">Nama</th>
                        <th class="px-6 py-3 text-center">Nilai</th>
                        <th class="px-6 py-3 text-center">Kelulusan</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </x-slot>
                    @forelse($ranking as $index => $item)
                        <tr class="hover:bg-secondary-50 {{ $item->lulus ? 'bg-success-50/30' : '' }}">
                            <td class="px-6 py-4">
                                @if($index === 0)
                                    <span class="inline-flex items-center gap-1 font-bold text-yellow-600">🥇 {{ $index + 1 }}</span>
                                @elseif($index === 1)
                                    <span class="inline-flex items-center gap-1 font-bold text-slate-500">🥈 {{ $index + 1 }}</span>
                                @elseif($index === 2)
                                    <span class="inline-flex items-center gap-1 font-bold text-amber-700">🥉 {{ $index + 1 }}</span>
                                @else
                                    <span class="font-semibold text-secondary-700">{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-medium text-secondary-900">{{ $item->user?->name ?? $item->pesertaOffline?->nama_peserta ?? '-' }}</span>
                                <span class="block text-xs text-secondary-400">{{ $item->user?->username ?? $item->pesertaOffline?->nomor_peserta ?? '' }}</span>
                            </td>
                            <td class="px-6 py-4 text-center font-semibold text-secondary-900">
                                {{ $item->total_nilai !== null ? number_format($item->total_nilai, 2) : '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($item->lulus === true)
                                    <x-badge type="success">✓ Lulus</x-badge>
                                @elseif($item->lulus === false)
                                    <x-badge type="danger">✕ Tidak Lulus</x-badge>
                                @else
                                    <x-badge type="secondary">-</x-badge>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('superadmin.hasil-ujian.review-peserta', ['ujian' => $ujian, 'peserta' => $item->id]) }}" class="text-primary-600 text-sm hover:underline font-medium">Review Jawaban</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-secondary-500">Belum ada peserta yang selesai ujian</td></tr>
                    @endforelse
                </x-table>
            </div>
        </div>
    </div>
@endsection
