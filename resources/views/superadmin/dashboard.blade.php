@extends('superadmin.layouts.app')

@section('title', 'Dashboard')

@section('header')
    <h1 class="text-2xl font-bold text-secondary-900">Dashboard</h1>
    <p class="text-secondary-500 mt-1">Overview sistem Panritta CPNS</p>
@endsection

@section('content')
    {{-- STATISTIK UTAMA --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Total Soal --}}
        <div class="card overflow-hidden">
            <div class="card-body p-6 relative">
                <div class="absolute right-0 top-0 opacity-[0.04] text-primary-500 -mt-6 -mr-4 pointer-events-none">
                    <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>
                </div>
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <h3 class="text-sm font-semibold text-secondary-500 uppercase tracking-wider">Bank Soal</h3>
                    <div class="w-10 h-10 rounded-xl bg-primary-50 border border-primary-100 flex items-center justify-center text-primary-600 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2 relative z-10">
                    <span class="text-3xl font-bold text-secondary-900">{{ number_format($stats['total_soal']) }}</span>
                    <span class="text-sm text-secondary-500 font-medium">butir</span>
                </div>
            </div>
            <div class="bg-primary-500 h-1 w-full"></div>
        </div>

        {{-- Ujian Aktif --}}
        <div class="card overflow-hidden">
            <div class="card-body p-6 relative">
                <div class="absolute right-0 top-0 opacity-[0.04] text-success-500 -mt-6 -mr-4 pointer-events-none">
                    <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                </div>
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <h3 class="text-sm font-semibold text-secondary-500 uppercase tracking-wider">Ujian Aktif</h3>
                    <div class="w-10 h-10 rounded-xl bg-success-50 border border-success-100 flex items-center justify-center text-success-600 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2 relative z-10">
                    <span class="text-3xl font-bold text-secondary-900">{{ number_format($stats['ujian_aktif']) }}</span>
                    <span class="text-sm text-secondary-500 font-medium">sesi</span>
                </div>
            </div>
            <div class="bg-success-500 h-1 w-full"></div>
        </div>

        {{-- Ujian Selesai --}}
        <div class="card overflow-hidden">
            <div class="card-body p-6 relative">
                <div class="absolute right-0 top-0 opacity-[0.04] text-amber-500 -mt-6 -mr-4 pointer-events-none">
                    <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                </div>
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <h3 class="text-sm font-semibold text-secondary-500 uppercase tracking-wider">Telah Selesai</h3>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2 relative z-10">
                    <span class="text-3xl font-bold text-secondary-900">{{ number_format($stats['ujian_selesai']) }}</span>
                    <span class="text-sm text-secondary-500 font-medium">sesi</span>
                </div>
            </div>
            <div class="bg-amber-500 h-1 w-full"></div>
        </div>

        {{-- Total Peserta --}}
        <div class="card overflow-hidden">
            <div class="card-body p-6 relative">
                <div class="absolute right-0 top-0 opacity-[0.04] text-blue-500 -mt-6 -mr-4 pointer-events-none">
                    <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 20 20"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/></svg>
                </div>
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <h3 class="text-sm font-semibold text-secondary-500 uppercase tracking-wider">Member Terdaftar</h3>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-3-6.65"/></svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2 relative z-10">
                    <span class="text-3xl font-bold text-secondary-900">{{ number_format($stats['total_peserta']) }}</span>
                    <span class="text-sm text-secondary-500 font-medium">orang</span>
                </div>
            </div>
            <div class="bg-blue-500 h-1 w-full"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- KIRI: Jadwal & Aktivitas Terkini --}}
        <div class="space-y-8">
            
            {{-- Jadwal Ujian Mendatang --}}
            <div class="card">
                <div class="card-header flex items-center justify-between bg-slate-50 border-b border-slate-200">
                    <h3 class="font-bold text-secondary-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Jadwal Ujian Terdekat
                    </h3>
                    <a href="{{ route('superadmin.ujian.index') }}" class="text-sm text-primary-600 font-medium hover:underline">Lihat Semua</a>
                </div>
                <div class="card-body-sm p-0">
                    <ul class="divide-y divide-slate-100">
                        @forelse($jadwalUjian as $ujian)
                            <li class="p-4 hover:bg-slate-50 transition-colors">
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0 text-center w-12 pt-1">
                                        <span class="block text-xs font-bold text-danger-500 uppercase">{{ $ujian->tanggal_ujian->translatedFormat('M') }}</span>
                                        <span class="block text-2xl font-black text-secondary-800">{{ $ujian->tanggal_ujian->format('d') }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-semibold text-secondary-900 truncate">{{ $ujian->nama_ujian }}</h4>
                                        <p class="text-sm text-secondary-500 mt-0.5 flex items-center gap-2">
                                            <span>Pukul {{ $ujian->tanggal_ujian->format('H:i') }} WIB</span>
                                            <span>&bull;</span>
                                            <span>{{ $ujian->durasi_ujian }} Menit</span>
                                        </p>
                                    </div>
                                    <div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium uppercase tracking-wider {{ $ujian->status === 'aktif' ? 'bg-success-100 text-success-700' : 'bg-warning-100 text-warning-700' }}">
                                            {{ $ujian->status }}
                                        </span>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="p-8 text-center text-secondary-500">
                                Tidak ada jadwal ujian mendesak.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>

            {{-- Aktivitas Pengerjaan Ujian Terbaru --}}
            <div class="card">
                <div class="card-header bg-slate-50 border-b border-slate-200">
                    <h3 class="font-bold text-secondary-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Aktivitas Ujian Terbaru
                    </h3>
                </div>
                <div class="card-body-sm p-0">
                    <ul class="divide-y divide-slate-100">
                        @forelse($recentAttempts as $attempt)
                            <li class="p-4 flex items-center gap-4 hover:bg-slate-50 transition-colors">
                                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-500 flex-shrink-0">
                                    {{ substr($attempt->user?->name ?? $attempt->pesertaOffline?->nama_peserta ?? 'P', 0, 1) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-secondary-900 truncate">
                                        {{ $attempt->user?->name ?? $attempt->pesertaOffline?->nama_peserta ?? 'Peserta Terhapus' }}
                                    </p>
                                    <p class="text-xs text-secondary-500 truncate mt-0.5">
                                        Mengerjakan: {{ $attempt->ujian?->nama_ujian ?? 'Ujian Terhapus' }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $attempt->status === 'selesai' ? 'bg-secondary-100 text-secondary-600' : 'bg-primary-100 text-primary-700' }}">
                                        {{ str_replace('_', ' ', $attempt->status) }}
                                    </span>
                                    <p class="text-[10px] text-secondary-400 mt-1">{{ $attempt->waktu_mulai?->diffForHumans() }}</p>
                                </div>
                            </li>
                        @empty
                            <li class="p-8 text-center text-secondary-500">
                                Belum ada aktivitas pengerjaan ujian.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>

        </div>

        {{-- KANAN: Hall of Fame (Nilai Tertinggi) --}}
        <div>
            <div class="card h-full">
                <div class="card-header bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-t-xl border-0">
                    <h3 class="font-bold flex items-center gap-2">
                        <svg class="w-5 h-5 text-yellow-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2.5a.5.5 0 01.408.27l2.25 4.562 5.033.731a.5.5 0 01.277.854l-3.642 3.55.86 5.014a.5.5 0 01-.725.528L10 15.656l-4.502 2.366a.5.5 0 01-.725-.528l.86-5.014-3.642-3.55a.5.5 0 01.277-.854l5.033-.731 2.25-4.562A.5.5 0 0110 2.5z" clip-rule="evenodd"/></svg>
                        Hall of Fame (Nilai Tertinggi)
                    </h3>
                </div>
                <div class="card-body-sm p-0">
                    <div class="bg-primary-50 px-4 py-3 text-xs text-primary-700 font-medium border-b border-primary-100">
                        Top 5 Peserta dengan skor tertinggi di semua ujian selesai
                    </div>
                    <ul class="divide-y divide-slate-100">
                        @forelse($topPeserta as $index => $peserta)
                            <li class="p-4 flex items-center gap-4 hover:bg-slate-50 transition-colors">
                                <div class="w-8 h-8 flex items-center justify-center font-black text-lg flex-shrink-0 {{ $index === 0 ? 'text-yellow-500' : ($index === 1 ? 'text-slate-400' : ($index === 2 ? 'text-amber-600' : 'text-slate-300')) }}">
                                    #{{ $index + 1 }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-secondary-900 truncate">
                                        {{ $peserta->user?->name ?? $peserta->pesertaOffline?->nama_peserta ?? 'Peserta' }}
                                    </p>
                                    <p class="text-[11px] text-secondary-500 truncate mt-0.5">
                                        {{ $peserta->ujian?->nama_ujian ?? '-' }}
                                    </p>
                                </div>
                                <div class="text-right flex flex-col items-end">
                                    <div class="text-lg font-black text-primary-600 leading-none mb-1">{{ number_format((float) $peserta->total_nilai, 1) }}</div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $peserta->lulus ? 'bg-success-100 text-success-700' : 'bg-danger-100 text-danger-700' }}">
                                        {{ $peserta->lulus ? 'LULUS' : 'GAGAL' }}
                                    </span>
                                </div>
                            </li>
                        @empty
                            <li class="p-12 flex flex-col items-center justify-center text-secondary-400 text-center">
                                <svg class="w-12 h-12 mb-3 text-secondary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                <p>Belum ada ujian yang selesai dinilai.</p>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
