@extends('superadmin.layouts.app')

@section('title', 'Verifikasi Pembayaran Peserta')

@section('content')
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900">Verifikasi Pembayaran</h1>
        <p class="text-slate-600 mt-2">Kelola dan verifikasi pembayaran dari peserta online</p>
    </div>

    {{-- Filter dan Search --}}
    <div class="card mb-6">
        <div class="card-body">
            <form method="GET" class="flex flex-col sm:flex-row gap-4">
                <input type="text" name="search" placeholder="Cari nama, email, atau nomor pembayaran..." 
                       value="{{ request('search') }}" class="input flex-1">
                
                <select name="status" class="input w-full sm:w-40">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                    <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Terverifikasi</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Ditolak</option>
                </select>

                <button type="submit" class="btn btn-primary whitespace-nowrap">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Cari
                </button>
            </form>
        </div>
    </div>

    {{-- Tabel Pembayaran --}}
    <div class="card">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">No. Pembayaran</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Peserta</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Paket</th>
                        <th class="px-6 py-3 text-right text-sm font-semibold text-slate-700">Nominal</th>
                        <th class="px-6 py-3 text-center text-sm font-semibold text-slate-700">Status</th>
                        <th class="px-6 py-3 text-center text-sm font-semibold text-slate-700">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembayaran as $item)
                        <tr class="border-b border-slate-200 hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <div class="font-mono text-sm font-semibold text-slate-900">{{ $item->nomor_pembayaran }}</div>
                                <div class="text-xs text-slate-500 mt-1">{{ $item->created_at->translatedFormat('d F Y') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">{{ $item->user->name }}</div>
                                <div class="text-sm text-slate-600">{{ $item->user->email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-slate-900">{{ $item->paket->nama_paket }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="font-semibold text-slate-900">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($item->isPending())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-warning-100 text-warning-700">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 11-2 0 1 1 0 012 0zm-1 4a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                                        Menunggu
                                    </span>
                                @elseif($item->isSuccess())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-success-100 text-success-700">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        Terverifikasi
                                    </span>
                                @elseif($item->isFailed())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-danger-100 text-danger-700">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                        Ditolak
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('superadmin.pembayaran.show', $item) }}" class="btn btn-sm btn-ghost">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <p class="text-slate-500 font-medium">Tidak ada pembayaran ditemukan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($pembayaran->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-between">
                <p class="text-sm text-slate-600">
                    Menampilkan <strong>{{ ($pembayaran->currentPage()-1)*$pembayaran->perPage()+1 }}</strong> hingga 
                    <strong>{{ min($pembayaran->currentPage()*$pembayaran->perPage(), $pembayaran->total()) }}</strong> 
                    dari <strong>{{ $pembayaran->total() }}</strong> pembayaran
                </p>
                <div class="flex gap-2">
                    {{ $pembayaran->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
