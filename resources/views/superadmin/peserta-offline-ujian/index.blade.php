@extends('superadmin.layouts.app')

@section('title', 'Ujian — Peserta Offline')

@section('breadcrumb')
    <span class="text-secondary-500">Peserta Offline</span>
    <span class="text-secondary-400 mx-2">/</span>
    <span class="text-secondary-900 font-medium">Ujian</span>
@endsection

@section('header')
    <div>
        <h1 class="text-2xl font-bold text-secondary-900">Ujian — Peserta Offline</h1>
        <p class="text-secondary-500 mt-1">Daftar ujian dan manajemen peserta offline yang berhak mengikuti ujian</p>
    </div>
@endsection

@section('content')
    {{-- Filter Card (Konsisten dengan Tabel Ujian) --}}
    <div class="card mb-6">
        <div class="card-body-sm space-y-3">
            <form method="GET" class="space-y-3">
                <div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="input w-full" placeholder="Cari nama ujian...">
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
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
                    <button type="submit" class="btn btn-secondary whitespace-nowrap">Filter</button>
                    @if(request()->anyFilled(['search', 'tipe_ujian', 'status']))
                        <a href="{{ route('superadmin.peserta-offline-ujian.index') }}" class="btn btn-ghost whitespace-nowrap text-secondary-600">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="card">
        <div class="card-body-sm">
            <x-table>
                <x-slot name="header">
                    <th class="px-6 py-3 text-left">Nama Ujian</th>
                    <th class="px-6 py-3 text-left">Tipe</th>
                    <th class="px-6 py-3 text-left">Jenis Ujian</th>
                    <th class="px-6 py-3 text-center">Soal</th>
                    <th class="px-6 py-3 text-center">Peserta Terdaftar</th>
                    <th class="px-6 py-3 text-center">Status</th>
                </x-slot>

                @forelse($ujians as $ujian)
                    <tr class="hover:bg-secondary-50 transition-colors">
                        {{-- Nama Ujian --}}
                        <td class="px-6 py-4">
                            <a href="{{ route('superadmin.peserta-offline-ujian.peserta', $ujian) }}"
                               class="font-semibold text-secondary-900 hover:text-primary-600 transition-colors">
                                {{ $ujian->nama_ujian }}
                            </a>
                            @if($ujian->tanggal_ujian)
                                <p class="text-xs text-secondary-500 mt-0.5">
                                    {{ $ujian->tanggal_ujian->format('d M Y, H:i') }}
                                    @if($ujian->durasi_menit)
                                        <span class="text-slate-400 mx-1">•</span>{{ $ujian->durasi_menit }} Menit
                                    @endif
                                </p>
                            @endif
                        </td>

                        {{-- Tipe Ujian --}}
                        <td class="px-6 py-4">
                            @if($ujian->tipe_ujian === 'offline_kelas')
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700">
                                    Offline di Kelas
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold bg-success-100 text-success-700">
                                    Online Paket
                                </span>
                            @endif
                        </td>

                        {{-- Jenis Ujian --}}
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @forelse($ujian->jenisUjians as $jenis)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-700">
                                        {{ $jenis->nama_jenis_ujian }}
                                    </span>
                                @empty
                                    <span class="text-xs text-secondary-400">—</span>
                                @endforelse
                            </div>
                        </td>

                        {{-- Soal --}}
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center font-semibold text-secondary-700 text-xs px-2.5 py-1 bg-slate-100 rounded-lg">
                                <span class="{{ $ujian->ujian_soals_count < $ujian->jumlah_soal ? 'text-amber-600' : 'text-emerald-600' }}">
                                    {{ $ujian->ujian_soals_count }}
                                </span>
                                <span class="mx-1 text-slate-400">/</span>
                                <span>{{ $ujian->jumlah_soal }}</span>
                            </span>
                        </td>

                        {{-- Peserta Terdaftar --}}
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('superadmin.peserta-offline-ujian.peserta', $ujian) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 hover:bg-blue-100 hover:text-blue-800 transition-colors border border-blue-200"
                               title="Klik untuk melihat & mengelola peserta ujian ini">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>{{ $ujian->peserta_offline_count ?? 0 }} Peserta</span>
                            </a>
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-4 text-center">
                            @php
                                $badge = match($ujian->status) {
                                    'aktif' => 'bg-success-100 text-success-800 border-success-200',
                                    'draft' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                    'selesai' => 'bg-secondary-100 text-secondary-700 border-secondary-200',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $badge }}">
                                {{ ucfirst($ujian->status) }}
                            </span>
                        </td>

                        
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-secondary-500">
                            Belum ada data ujian yang sesuai filter.
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>

        @if($ujians->hasPages())
            <div class="px-6 py-4 border-t border-secondary-200">
                {{ $ujians->links() }}
            </div>
        @endif
    </div>
@endsection
