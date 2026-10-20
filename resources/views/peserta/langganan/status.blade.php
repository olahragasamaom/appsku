@extends('peserta.layouts.app')

@section('title', 'Status Pembayaran')

@section('content')
    <div class="max-w-2xl mx-auto">
        {{-- Header --}}
        <div class="mb-8">
            <a href="{{ route('peserta.langganan.index') }}" class="inline-flex items-center text-sm text-primary-600 hover:text-primary-700 font-medium mb-4">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Kembali ke Paket
            </a>
            <h1 class="text-3xl font-bold text-slate-900">Status Pembayaran</h1>
        </div>

        @if (session('success'))
            <div class="bg-success-50 border border-success-200 rounded-lg p-4 mb-6 flex items-start gap-3">
                <svg class="w-5 h-5 text-success-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="font-semibold text-success-900">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Status Details --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Status Card --}}
                <div class="card">
                    <div class="card-body">
                        <div class="text-center mb-6">
                            @if ($bayar->isPending())
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-warning-100 mb-4">
                                    <svg class="w-8 h-8 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <h2 class="text-2xl font-bold text-slate-900 mb-2">Menunggu Verifikasi</h2>
                                <p class="text-slate-600">Bukti pembayaran Anda sedang diverifikasi oleh admin. Proses biasanya memakan waktu 1-24 jam.</p>
                            @elseif ($bayar->isSuccess())
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-success-100 mb-4">
                                    <svg class="w-8 h-8 text-success-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <h2 class="text-2xl font-bold text-slate-900 mb-2">Pembayaran Berhasil</h2>
                                <p class="text-slate-600">Paket Anda telah diaktifkan. Selamat belajar!</p>
                            @elseif ($bayar->isFailed())
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-danger-100 mb-4">
                                    <svg class="w-8 h-8 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <h2 class="text-2xl font-bold text-slate-900 mb-2">Pembayaran Ditolak</h2>
                                <p class="text-slate-600">Bukti pembayaran Anda tidak sesuai dengan nominal yang diminta.</p>
                            @endif
                        </div>

                        <hr class="my-6">

                        {{-- Details --}}
                        <div class="space-y-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm text-slate-600 mb-1">Nomor Pembayaran</p>
                                    <p class="font-mono font-semibold text-slate-900">{{ $bayar->nomor_pembayaran }}</p>
                                </div>
                                <button type="button" onclick="navigator.clipboard.writeText('{{ $bayar->nomor_pembayaran }}')" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                                    Salin
                                </button>
                            </div>

                            <div class="flex justify-between">
                                <div>
                                    <p class="text-sm text-slate-600 mb-1">Paket</p>
                                    <p class="font-semibold text-slate-900">{{ $bayar->paket->nama_paket }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-slate-600 mb-1">Nominal</p>
                                    <p class="font-semibold text-slate-900">Rp {{ number_format($bayar->jumlah, 0, ',', '.') }}</p>
                                </div>
                            </div>

                            <div class="flex justify-between">
                                <div>
                                    <p class="text-sm text-slate-600 mb-1">Status</p>
                                    <div class="mt-1 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                                        @if ($bayar->isPending()) bg-warning-100 text-warning-700
                                        @elseif ($bayar->isSuccess()) bg-success-100 text-success-700
                                        @elseif ($bayar->isFailed()) bg-danger-100 text-danger-700
                                        @endif
                                    ">
                                        @if ($bayar->isPending())
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 11-2 0 1 1 0 012 0zm-1 4a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                                            Menunggu Verifikasi
                                        @elseif ($bayar->isSuccess())
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            Terverifikasi
                                        @elseif ($bayar->isFailed())
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                            Ditolak
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-slate-600 mb-1">Tanggal</p>
                                    <p class="font-semibold text-slate-900">{{ $bayar->created_at->translatedFormat('d F Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bukti Pembayaran --}}
                @if ($bayar->bukti_pembayaran)
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Bukti Pembayaran</h3>
                        </div>
                        <div class="card-body">
                            <img src="{{ asset('storage/' . $bayar->bukti_pembayaran) }}" alt="Bukti Pembayaran" class="max-w-full h-auto rounded-lg border border-slate-200">
                            
                            @if ($bayar->catatan_peserta)
                                <div class="mt-4 p-4 bg-slate-50 rounded-lg border border-slate-200">
                                    <p class="text-sm text-slate-600 mb-1">Catatan Peserta:</p>
                                    <p class="text-slate-900">{{ $bayar->catatan_peserta }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Verifikasi Admin --}}
                @if ($bayar->diverifikasi_pada && $bayar->catatan_verifikasi)
                    <div class="card {{ $bayar->isSuccess() ? 'border-success-200 bg-success-50' : 'border-danger-200 bg-danger-50' }}">
                        <div class="card-header">
                            <h3 class="card-title">Catatan Verifikasi</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-slate-700 mb-3">{{ $bayar->catatan_verifikasi }}</p>
                            <p class="text-xs text-slate-600">
                                Diverifikasi pada {{ $bayar->diverifikasi_pada->translatedFormat('d F Y H:i') }} 
                                oleh {{ $bayar->bukti_pembayaran_verified_by }}
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Upload Ulang (Jika Ditolak) --}}
                @if ($bayar->isFailed())
                    <div class="card border-warning-200 bg-warning-50">
                        <div class="card-header">
                            <h3 class="card-title">Kirim Ulang Bukti</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-slate-700 mb-4">Silakan upload bukti pembayaran yang benar dan coba lagi.</p>
                            <a href="{{ route('peserta.langganan.bayar', $bayar->id) }}" class="btn btn-primary">
                                Upload Bukti Lagi
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar Info --}}
            <div class="lg:col-span-1">
                <div class="card sticky top-24">
                    <div class="card-header">
                        <h3 class="card-title">Informasi</h3>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <p class="text-sm text-slate-600 mb-2">Durasi Paket</p>
                            <p class="font-semibold text-slate-900">{{ $bayar->paket->durasi_hari }} hari</p>
                        </div>

                        <hr class="border-slate-200">

                        <div>
                            <p class="text-sm text-slate-600 mb-2">Akses Ujian</p>
                            <p class="font-semibold text-slate-900">
                                {{ $bayar->paket->kuota_ujian ?? 'Unlimited' }}
                            </p>
                        </div>

                        <hr class="border-slate-200">

                        <div class="space-y-2 text-sm">
                            @if ($bayar->paket->video_pembahasan)
                                <div class="flex items-center gap-2 text-success-700">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    <span>Video Pembahasan</span>
                                </div>
                            @endif

                            @if ($bayar->paket->analitik)
                                <div class="flex items-center gap-2 text-success-700">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    <span>Analitik Lengkap</span>
                                </div>
                            @endif

                            @if ($bayar->paket->sertifikat)
                                <div class="flex items-center gap-2 text-success-700">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    <span>Sertifikat</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
