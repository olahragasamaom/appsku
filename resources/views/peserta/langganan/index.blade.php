@extends('peserta.layouts.app')

@section('title', 'Paket Belajar')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-800">Paket Belajar Saya</h1>
        <p class="text-slate-500 mt-1">Kelola langganan dan pilih paket tryout untuk mulai berlatih.</p>
    </div>

    {{-- Alert Status Paket Aktif --}}
    @if($aktif && $aktif->isActive())
        <div class="bg-primary-50 border border-primary-200 rounded-2xl p-6 sm:p-8 mb-10 flex flex-col sm:flex-row items-center justify-between gap-6 shadow-sm">
            <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-success-100 text-success-700 mb-3">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Paket Aktif
                </div>
                <h2 class="text-xl font-bold text-slate-900 mb-1">{{ $aktif->paket?->nama_paket ?? 'Paket' }}</h2>
                <p class="text-slate-600 text-sm">
                    Berlaku hingga: <span class="font-semibold text-slate-800">{{ $aktif->berakhir_pada ? $aktif->berakhir_pada->translatedFormat('d F Y') : 'Selamanya' }}</span>
                    &middot; Kuota: <span class="font-semibold text-slate-800">{{ $aktif->sisa_kuota_ujian === null ? 'Tidak Terbatas' : $aktif->sisa_kuota_ujian . ' sesi' }}</span>
                </p>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ route('peserta.dashboard') }}" class="btn btn-primary shadow-md shadow-primary-500/20">
                    Mulai Tryout Sekarang &rarr;
                </a>
            </div>
        </div>
    @endif

    {{-- Daftar Paket (Mirip Landing Page) --}}
    <h2 class="text-xl font-bold text-slate-800 mb-6">Pilih Paket Tersedia</h2>
    
    @if($pakets->isEmpty())
        <div class="card border-dashed">
            <div class="card-body text-center py-12">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <p class="text-slate-500">Belum ada paket yang tersedia saat ini.</p>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 mb-12">
            @foreach($pakets as $paket)
                @php
                    $isAktif = $aktif && $aktif->paket_id === $paket->id && $aktif->isActive();
                @endphp
                <div class="card flex flex-col shadow-sm hover:shadow-xl transition-all duration-300 relative overflow-hidden border-2 {{ $isAktif ? 'border-success-500 ring-4 ring-success-50' : ($loop->iteration === 2 ? 'border-primary-500 lg:scale-105 z-10' : 'border-transparent') }}">
                    
                    @if($isAktif)
                        <div class="absolute top-0 right-0 bg-success-500 text-white text-[10px] font-bold uppercase tracking-wider py-1 px-3 rounded-bl-lg shadow-sm">
                            Sedang Aktif
                        </div>
                    @elseif($loop->iteration === 2)
                        <div class="absolute top-0 inset-x-0 bg-primary-500 text-white text-center text-[10px] font-bold uppercase tracking-wider py-1.5 shadow-sm">
                            Paling Populer
                        </div>
                    @endif
                    
                    <div class="card-body p-6 lg:p-8 flex flex-col flex-1 {{ $loop->iteration === 2 ? 'pt-10' : '' }}">
                        <h3 class="text-lg lg:text-xl font-bold text-slate-900 mb-2">{{ $paket->nama_paket }}</h3>
                        <p class="text-sm text-slate-500 mb-6 min-h-[40px]">{{ $paket->deskripsi ?? 'Akses persiapan ujian lengkap.' }}</p>
                        
                        <div class="mb-6 flex items-baseline gap-1">
                            <span class="text-3xl lg:text-4xl font-extrabold text-slate-900">
                                {{ $paket->isGratis() ? 'Gratis' : 'Rp' . number_format($paket->harga, 0, ',', '.') }}
                            </span>
                            @if(! $paket->isGratis())
                                <span class="text-slate-500 font-medium">/{{ $paket->durasi_hari }} Hari</span>
                            @endif
                        </div>

                        <ul class="space-y-4 mb-8 flex-1">
                            <li class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-success-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span class="text-slate-600 text-sm">
                                    Akses {{ $paket->kuota_ujian ? $paket->kuota_ujian . ' kali Ujian' : 'Ujian Sepuasnya (Unlimited)' }}
                                </span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-success-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span class="text-slate-600 text-sm">Update Bank Soal Terbaru</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-success-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span class="text-slate-600 text-sm">Simulasi CAT Real-time</span>
                            </li>
                            
                            @if($paket->video_pembahasan)
                            <li class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-success-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span class="text-slate-600 text-sm font-medium">Video Pembahasan Soal</span>
                            </li>
                            @endif
                            
                            @if($paket->analitik)
                            <li class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-success-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span class="text-slate-600 text-sm font-medium">Grafik Analitik Kelulusan</span>
                            </li>
                            @endif
                        </ul>

                        @if($isAktif)
                            <button disabled class="btn bg-success-50 text-success-700 w-full justify-center py-3 border-success-200 cursor-not-allowed">
                                Paket Saat Ini
                            </button>
                        @else
                            <form action="{{ route('peserta.langganan.pilih', $paket) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn {{ $loop->iteration === 2 ? 'btn-primary shadow-lg shadow-primary-500/20' : 'btn-secondary' }} w-full justify-center py-3 transition-transform hover:-translate-y-0.5">
                                    {{ $paket->isGratis() ? 'Pilih Paket Gratis' : 'Beli Paket Ini' }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Riwayat Langganan & Tagihan --}}
    @if($riwayat->isNotEmpty())
        <section>
            <h2 class="text-xl font-bold text-slate-800 mb-6">Riwayat & Tagihan</h2>
            <div class="card">
                <div class="card-body-sm">
                    <x-table>
                        <x-slot name="header">
                            <th class="px-6 py-3 text-left">Paket</th>
                            <th class="px-6 py-3 text-left">Tanggal</th>
                            <th class="px-6 py-3 text-center">Status</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </x-slot>
                        @foreach($riwayat as $item)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <span class="font-medium text-slate-900">{{ $item->paket?->nama_paket ?? 'Paket Terhapus' }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ $item->created_at->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($item->status === 'active')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-success-100 text-success-700">Aktif</span>
                                    @elseif($item->status === 'pending')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-warning-100 text-warning-700">Menunggu Pembayaran</span>
                                    @elseif($item->status === 'expired')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600">Kedaluwarsa</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-danger-100 text-danger-700">{{ $item->status }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($item->status === 'pending' && $item->pembayaran->isNotEmpty())
                                        @php $pembayaranPending = $item->pembayaran->where('status', 'pending')->first(); @endphp
                                        @if($pembayaranPending)
                                            <a href="{{ route('peserta.langganan.bayar', $pembayaranPending->id) }}" class="btn btn-primary btn-sm">Bayar</a>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </x-table>
                </div>
            </div>
        </section>
    @endif

@endsection