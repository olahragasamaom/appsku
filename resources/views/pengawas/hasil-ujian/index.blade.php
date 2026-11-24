@extends('pengawas.layouts.app')

@section('title', 'Hasil Ujian')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-secondary-900">Hasil Ujian</h1>
        <p class="text-secondary-500 mt-2">Pantau hasil dan analisis ujian peserta</p>
    </div>

    @if($ujians->isEmpty())
        <div class="card">
            <div class="card-body text-center py-12">
                <svg class="w-16 h-16 text-secondary-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <h3 class="text-lg font-semibold text-secondary-900 mb-2">Tidak ada hasil ujian</h3>
                <p class="text-secondary-500">Belum ada ujian dengan hasil yang dapat ditampilkan</p>
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
                                <p class="text-secondary-500 text-sm">Total Peserta</p>
                                <p class="text-secondary-900 font-semibold">{{ $ujian->peserta->count() }}</p>
                            </div>
                            <div>
                                <p class="text-secondary-500 text-sm">Lulus</p>
                                <p class="text-secondary-900 font-semibold">
                                    {{ $ujian->peserta->where('lulus', true)->count() }}
                                </p>
                            </div>
                            <div>
                                <p class="text-secondary-500 text-sm">Rata-rata Nilai</p>
                                <p class="text-secondary-900 font-semibold">
                                    {{ $ujian->peserta->count() > 0 ? number_format($ujian->peserta->avg('nilai_total'), 2) : '-' }}
                                </p>
                            </div>
                        </div>

                        <a href="{{ route('pengawas.hasil-ujian.show', $ujian) }}"
                           class="btn btn-primary inline-flex gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Lihat Detail
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
