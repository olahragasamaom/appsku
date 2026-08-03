@extends('peserta.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1 class="text-2xl font-bold text-slate-800 mb-6">Selamat datang, {{ auth()->user()->name }}</h1>

    <section class="mb-8">
        <h2 class="text-lg font-semibold text-slate-700 mb-3">Ujian Tersedia</h2>
        @php $adaUjian = $allocations->isNotEmpty() || $onlineUjians->isNotEmpty(); @endphp

        @if(! $adaUjian)
            <div class="card"><div class="card-body text-center text-slate-500">Belum ada ujian aktif yang dapat diikuti.</div></div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($allocations as $allocation)
                    <div class="card">
                        <div class="card-body">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-secondary-100 text-secondary-700 mb-2">Offline</span>
                            <h3 class="font-semibold text-slate-800">{{ $allocation->ujian->nama_ujian }}</h3>
                            @if($allocation->ujian->tanggal_ujian)
                                <p class="text-xs text-slate-500 mt-1">{{ $allocation->ujian->tanggal_ujian->format('d M Y H:i') }} &middot; {{ $allocation->ujian->durasi_ujian }} menit</p>
                            @endif
                            <div class="mt-4">
                                @if($allocation->status === 'diblokir')
                                    <span class="text-sm text-danger-600">Akun diblokir pengawas</span>
                                @elseif($allocation->status === 'selesai')
                                    <a href="{{ route('peserta.ujian.hasil', $allocation->ujian) }}" class="btn btn-secondary btn-sm w-full">Lihat Hasil</a>
                                @else
                                    <a href="{{ route('peserta.ujian.show', $allocation->ujian) }}" class="btn btn-primary btn-sm w-full">Mulai Ujian</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                @foreach($onlineUjians as $ujian)
                    <div class="card">
                        <div class="card-body">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-700 mb-2">Online</span>
                            <h3 class="font-semibold text-slate-800">{{ $ujian->nama_ujian }}</h3>
                            <p class="text-xs text-slate-500 mt-1">{{ $ujian->jumlah_soal }} soal</p>
                            <div class="mt-4">
                                <a href="{{ route('peserta.ujian.show', $ujian) }}" class="btn btn-primary btn-sm w-full">Mulai Ujian</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section>
        <h2 class="text-lg font-semibold text-slate-700 mb-3">Riwayat Ujian</h2>
        @if($riwayat->isEmpty())
            <div class="card"><div class="card-body text-center text-slate-500">Belum ada riwayat ujian.</div></div>
        @else
            <div class="card">
                <div class="card-body-sm">
                    <x-table>
                        <x-slot name="header">
                            <th class="px-6 py-3 text-left">Ujian</th>
                            <th class="px-6 py-3 text-center">Nilai</th>
                            <th class="px-6 py-3 text-center">Status</th>
                            <th class="px-6 py-3 text-center">Aksi</th>
                        </x-slot>
                        @foreach($riwayat as $item)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 font-medium text-slate-800">{{ $item->ujian->nama_ujian }}</td>
                                <td class="px-6 py-4 text-center">{{ $item->total_nilai ?? '-' }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if($item->lulus === true)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-700">Lulus</span>
                                    @elseif($item->lulus === false)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-danger-100 text-danger-700">Tidak Lulus</span>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($item->ujian->tampilkan_hasil)
                                        <a href="{{ route('peserta.ujian.hasil', $item->ujian) }}" class="text-primary-600 text-sm hover:underline">Detail</a>
                                    @else
                                        <span class="text-xs text-slate-400">Tersembunyi</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </x-table>
                </div>
            </div>
        @endif
    </section>
@endsection
