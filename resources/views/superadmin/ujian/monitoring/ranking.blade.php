@extends('superadmin.layouts.app')

@section('title', 'Perankingan')

@section('breadcrumb')
    <a href="{{ route('superadmin.ujian.index') }}" class="text-secondary-500 hover:text-secondary-700">Manajemen Ujian</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Perankingan</span>
@endsection

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Perankingan — {{ $ujian->nama_ujian }}</h1>
            <p class="text-secondary-500 mt-1">Diurutkan berdasarkan nilai kumulatif tertinggi</p>
        </div>
        <a href="{{ route('superadmin.ujian.monitoring.live', $ujian) }}" class="btn btn-secondary btn-sm">Live Scoring</a>
    </div>
@endsection

@section('content')
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
                    <tr class="hover:bg-secondary-50">
                        <td class="px-6 py-4 font-semibold text-secondary-700">{{ $index + 1 }}</td>
                        <td class="px-6 py-4">
                            <span class="font-medium text-secondary-900">{{ $item->user?->name ?? '-' }}</span>
                            <span class="block text-xs text-secondary-400">{{ $item->user?->username }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">{{ $item->total_nilai ?? '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            @if($item->lulus === true)
                                <span class="text-success-600 text-sm font-medium">Lulus</span>
                            @elseif($item->lulus === false)
                                <span class="text-danger-600 text-sm font-medium">Tidak Lulus</span>
                            @else
                                <span class="text-secondary-400 text-sm">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('superadmin.ujian.monitoring.review', ['ujian' => $ujian, 'peserta' => $item->id]) }}" class="text-primary-600 text-sm hover:underline">Review Jawaban</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-secondary-500">Belum ada peserta</td></tr>
                @endforelse
            </x-table>
        </div>
    </div>
@endsection
