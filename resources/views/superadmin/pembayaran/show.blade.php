@extends('superadmin.layouts.app')

@section('title', 'Detail Pembayaran')

@section('content')
    <div class="mb-8">
        <a href="{{ route('superadmin.pembayaran.index') }}" class="inline-flex items-center text-sm text-primary-600 hover:text-primary-700 font-medium mb-4">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
        <h1 class="text-3xl font-bold text-slate-900">Detail Pembayaran</h1>
    </div>

    @if (session('success'))
        <div class="bg-success-50 border border-success-200 rounded-lg p-4 mb-6">
            <p class="text-success-700 font-semibold">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-danger-50 border border-danger-200 rounded-lg p-4 mb-6">
            <p class="text-danger-700 font-semibold">{{ session('error') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Informasi Pembayaran --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Informasi Pembayaran</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-slate-600 mb-1">Nomor Pembayaran</p>
                            <p class="font-mono font-semibold text-slate-900">{{ $pembayaran->nomor_pembayaran }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-600 mb-1">Tanggal</p>
                            <p class="font-semibold text-slate-900">{{ $pembayaran->created_at->translatedFormat('d F Y H:i') }}</p>
                        </div>
                    </div>

                    <hr class="border-slate-200">

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-slate-600 mb-1">Nama Peserta</p>
                            <p class="font-semibold text-slate-900">{{ $pembayaran->user->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-600 mb-1">Email</p>
                            <p class="text-slate-900 break-all">{{ $pembayaran->user->email }}</p>
                        </div>
                    </div>

                    <hr class="border-slate-200">

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-slate-600 mb-1">Paket</p>
                            <p class="font-semibold text-slate-900">{{ $pembayaran->paket->nama_paket }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-600 mb-1">Nominal</p>
                            <p class="text-lg font-bold text-primary-600">Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bukti Pembayaran --}}
            @if ($pembayaran->bukti_pembayaran)
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Bukti Pembayaran</h2>
                    </div>
                    <div class="card-body">
                        <img src="{{ asset('storage/' . $pembayaran->bukti_pembayaran) }}" alt="Bukti Pembayaran" class="max-w-full h-auto rounded-lg border border-slate-200 mb-4">
                        
                        @if ($pembayaran->catatan_peserta)
                            <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                                <p class="text-sm text-slate-600 mb-2">Catatan dari Peserta:</p>
                                <p class="text-slate-900">{{ $pembayaran->catatan_peserta }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Status dan Aksi --}}
            @if ($pembayaran->isPending())
                <div class="card border-warning-200 bg-warning-50">
                    <div class="card-header">
                        <h2 class="card-title text-warning-900">Verifikasi Pembayaran</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <p class="text-warning-800">Periksa bukti pembayaran di atas dengan cermat sebelum memverifikasi atau menolak pembayaran ini.</p>

                        {{-- Verifikasi Form --}}
                        <form action="{{ route('superadmin.pembayaran.verifikasi', $pembayaran) }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label for="catatan_verifikasi" class="block text-sm font-medium text-slate-700 mb-2">
                                    Catatan Verifikasi (Opsional)
                                </label>
                                <textarea 
                                    name="catatan_verifikasi" 
                                    id="catatan_verifikasi" 
                                    rows="3"
                                    class="input w-full"
                                    placeholder="Contoh: Nominal sesuai, transfer dari BCA"
                                    maxlength="500"
                                ></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-full">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Verifikasi & Aktifkan Langganan
                            </button>
                        </form>

                        {{-- Tolak Form --}}
                        <form action="{{ route('superadmin.pembayaran.tolak', $pembayaran) }}" method="POST" class="space-y-4 pt-4 border-t border-warning-200">
                            @csrf
                            <div>
                                <label for="alasan" class="block text-sm font-medium text-slate-700 mb-2">
                                    Alasan Penolakan <span class="text-danger-500">*</span>
                                </label>
                                <textarea 
                                    name="alasan" 
                                    id="alasan" 
                                    rows="3"
                                    class="input w-full"
                                    placeholder="Contoh: Nominal tidak sesuai, terjadi kesalahan transfer"
                                    maxlength="500"
                                    required
                                ></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger w-full" onclick="return confirm('Yakin ingin menolak pembayaran ini? Peserta dapat mengirim ulang bukti.');">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Tolak Pembayaran
                            </button>
                        </form>
                    </div>
                </div>
            @elseif ($pembayaran->isSuccess())
                <div class="card border-success-200 bg-success-50">
                    <div class="card-header">
                        <h2 class="card-title text-success-900">Pembayaran Terverifikasi</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-success-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="font-semibold text-success-900">Pembayaran telah diverifikasi dan langganan sudah diaktifkan</p>
                                <p class="text-success-800 text-sm mt-1">Peserta dapat langsung mengakses paket yang dibeli</p>
                            </div>
                        </div>

                        @if ($pembayaran->catatan_verifikasi)
                            <div class="bg-white rounded-lg p-4 border border-success-200 mt-4">
                                <p class="text-sm text-slate-600 mb-2">Catatan Verifikasi:</p>
                                <p class="text-slate-900">{{ $pembayaran->catatan_verifikasi }}</p>
                            </div>
                        @endif

                        <p class="text-xs text-success-700">
                            Diverifikasi pada {{ $pembayaran->diverifikasi_pada->translatedFormat('d F Y H:i') }} 
                            oleh {{ $pembayaran->bukti_pembayaran_verified_by }}
                        </p>
                    </div>
                </div>
            @elseif ($pembayaran->isFailed())
                <div class="card border-danger-200 bg-danger-50">
                    <div class="card-header">
                        <h2 class="card-title text-danger-900">Pembayaran Ditolak</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-danger-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="font-semibold text-danger-900">Pembayaran telah ditolak</p>
                                <p class="text-danger-800 text-sm mt-1">Peserta dapat mengirim ulang bukti pembayaran yang sesuai</p>
                            </div>
                        </div>

                        @if ($pembayaran->catatan_verifikasi)
                            <div class="bg-white rounded-lg p-4 border border-danger-200 mt-4">
                                <p class="text-sm text-slate-600 mb-2">Alasan Penolakan:</p>
                                <p class="text-slate-900">{{ $pembayaran->catatan_verifikasi }}</p>
                            </div>
                        @endif

                        <p class="text-xs text-danger-700">
                            Ditolak pada {{ $pembayaran->diverifikasi_pada->translatedFormat('d F Y H:i') }} 
                            oleh {{ $pembayaran->bukti_pembayaran_verified_by }}
                        </p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="lg:col-span-1">
            {{-- Status Card --}}
            <div class="card sticky top-24 mb-6">
                <div class="card-body">
                    <p class="text-sm text-slate-600 mb-2">Status Pembayaran</p>
                    @if($pembayaran->isPending())
                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold bg-warning-100 text-warning-700">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 11-2 0 1 1 0 012 0zm-1 4a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                            Menunggu
                        </div>
                    @elseif($pembayaran->isSuccess())
                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold bg-success-100 text-success-700">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Terverifikasi
                        </div>
                    @elseif($pembayaran->isFailed())
                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold bg-danger-100 text-danger-700">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            Ditolak
                        </div>
                    @endif
                </div>
            </div>

            {{-- Paket Info --}}
            <div class="card sticky top-80">
                <div class="card-header">
                    <h3 class="card-title">Informasi Paket</h3>
                </div>
                <div class="card-body space-y-4">
                    <div>
                        <p class="text-xs text-slate-600 mb-1">Durasi</p>
                        <p class="font-semibold text-slate-900">{{ $pembayaran->paket->durasi_hari }} Hari</p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-600 mb-1">Akses Ujian</p>
                        <p class="font-semibold text-slate-900">{{ $pembayaran->paket->kuota_ujian ?? 'Unlimited' }}</p>
                    </div>

                    <hr class="border-slate-200">

                    <div class="space-y-2 text-sm">
                        @if ($pembayaran->paket->video_pembahasan)
                            <div class="flex items-center gap-2 text-success-700">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                Video Pembahasan
                            </div>
                        @endif

                        @if ($pembayaran->paket->analitik)
                            <div class="flex items-center gap-2 text-success-700">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                Analitik Lengkap
                            </div>
                        @endif

                        @if ($pembayaran->paket->sertifikat)
                            <div class="flex items-center gap-2 text-success-700">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                Sertifikat
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
