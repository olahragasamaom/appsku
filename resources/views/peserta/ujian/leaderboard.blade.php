@extends('peserta.layouts.app')

@section('title', 'Peringkat Ujian')

@section('content')
    <div class="max-w-3xl mx-auto">
        <a href="{{ route('peserta.ujian.hasil', $ujian) }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Kembali ke Hasil</a>

        <div class="card mt-4">
            <div class="card-body text-center">
                <h1 class="text-xl font-bold text-slate-800">Peringkat — {{ $ujian->nama_ujian }}</h1>
                <p class="text-slate-500 text-sm mt-1">{{ $posisi['total'] }} peserta telah menyelesaikan ujian ini</p>

                @if($posisi['rank'])
                    <div class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary-50 border border-primary-100">
                        <span class="text-sm text-primary-700">Peringkat Anda</span>
                        <span class="text-2xl font-black text-primary-700">#{{ $posisi['rank'] }}</span>
                        <span class="text-sm text-primary-400">/ {{ $posisi['total'] }}</span>
                    </div>
                @endif
            </div>
        </div>

        @php
            $isSelfRow = fn ($item) => $item->id === $peserta->id;
            $medals = [1 => 'bg-amber-100 text-amber-700 border-amber-200', 2 => 'bg-slate-200 text-slate-700 border-slate-300', 3 => 'bg-orange-100 text-orange-700 border-orange-200'];
        @endphp

        <div class="card mt-6">
            <div class="card-body-sm">
                <x-table>
                    <x-slot name="header">
                        <th class="px-6 py-3 text-center w-20">Peringkat</th>
                        <th class="px-6 py-3 text-left">Peserta</th>
                        <th class="px-6 py-3 text-center">Nilai</th>
                        <th class="px-6 py-3 text-center">Status</th>
                    </x-slot>

                    @forelse($ranking as $index => $item)
                        @php $rank = $index + 1; @endphp
                        <tr class="{{ $isSelfRow($item) ? 'bg-primary-50/70 ring-1 ring-inset ring-primary-200' : 'hover:bg-slate-50' }}">
                            <td class="px-6 py-4 text-center">
                                @if(isset($medals[$rank]))
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full border font-bold text-sm {{ $medals[$rank] }}">{{ $rank }}</span>
                                @else
                                    <span class="text-slate-500 font-semibold">{{ $rank }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-medium text-slate-800">
                                    {{ $item->user?->name ?? $item->pesertaOffline?->nama_peserta ?? 'Peserta #'.$item->id }}
                                </span>
                                @if($isSelfRow($item))
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-primary-100 text-primary-700">Anda</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-slate-800 tabular-nums">{{ $item->total_nilai ?? 0 }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($item->lulus === true)
                                    <span class="text-success-600 text-sm font-medium">Lulus</span>
                                @elseif($item->lulus === false)
                                    <span class="text-danger-600 text-sm font-medium">Tidak Lulus</span>
                                @else
                                    <span class="text-slate-400 text-sm">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">Belum ada peserta yang menyelesaikan ujian ini</td>
                        </tr>
                    @endforelse
                </x-table>
            </div>
        </div>
    </div>
@endsection
