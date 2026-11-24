@extends('pengawas.layouts.app')

@section('title', 'Kelola Absensi Peserta')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-secondary-900">Kelola Absensi Peserta</h1>
        <p class="text-secondary-500 mt-2">Kelola kehadiran peserta ujian</p>
    </div>

    @if($ujians->isEmpty())
        <div class="card">
            <div class="card-body text-center py-12">
                <svg class="w-16 h-16 text-secondary-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <h3 class="text-lg font-semibold text-secondary-900 mb-2">Tidak ada ujian aktif</h3>
                <p class="text-secondary-500">Belum ada ujian yang dapat dikelola absensinya</p>
            </div>
        </div>
    @else
        <div class="space-y-6">
            @foreach($ujians as $ujian)
                <div class="card hover:shadow-lg transition">
                    <div class="card-body">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-xl font-semibold text-secondary-900">{{ $ujian->nama }}</h3>
                                <p class="text-secondary-500 text-sm mt-1">{{ $ujian->deskripsi ?? 'Tanpa deskripsi' }}</p>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-success-100 text-success-700">
                                Aktif
                            </span>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 py-4 border-y border-secondary-200">
                            <div>
                                <p class="text-secondary-500 text-sm">Tanggal Ujian</p>
                                <p class="text-secondary-900 font-semibold">
                                    {{ $ujian->tanggal_ujian->format('d M Y') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-secondary-500 text-sm">Durasi</p>
                                <p class="text-secondary-900 font-semibold">{{ $ujian->durasi }} menit</p>
                            </div>
                            <div>
                                <p class="text-secondary-500 text-sm">Total Peserta</p>
                                <p class="text-secondary-900 font-semibold">{{ $ujian->pesertaOffline->count() }}</p>
                            </div>
                            <div>
                                <p class="text-secondary-500 text-sm">Hadir</p>
                                <p class="text-secondary-900 font-semibold">
                                    {{ $ujian->pesertaOffline->where('is_active', true)->count() }}
                                </p>
                            </div>
                        </div>

                        <a href="{{ route('pengawas.absensi.activation', $ujian) }}"
                           class="btn btn-primary inline-flex gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Kelola Absensi
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
