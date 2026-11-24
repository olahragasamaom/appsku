@extends('superadmin.layouts.app')

@section('title', 'Detail Pengerjaan - '.$peserta->user->name.' - '.$ujian->nama_ujian)

@section('breadcrumb')
    <a href="{{ route('superadmin.hasil-ujian.index') }}" class="text-secondary-500 hover:text-secondary-700">Hasil Ujian</a>
    <span class="mx-2 text-secondary-400">/</span>
    <a href="{{ route('superadmin.hasil-ujian.show', $ujian) }}" class="text-secondary-500 hover:text-secondary-700">{{ $ujian->nama_ujian }}</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Detail Pengerjaan</span>
@endsection

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h2 class="font-semibold text-xl text-secondary-800">Detail Pengerjaan Soal</h2>
            <p class="text-sm text-secondary-500">{{ $peserta->user->name }} - {{ $ujian->nama_ujian }}</p>
        </div>
        <a href="{{ route('superadmin.hasil-ujian.show', $ujian) }}" class="btn btn-ghost">Kembali</a>
    </div>
@endsection

@section('content')
<div class="max-w-7xl mx-auto space-y-6"
     x-data="detailPengerjaan({
         pesertaId: {{ $peserta->id }},
         ujianId: {{ $ujian->id }},
         refreshUrl: '{{ route('superadmin.hasil-ujian.detail-pengerjaan-data', [$ujian, $peserta]) }}'
     })"
     x-init="init()"
     @destroy="stopRefresh()">

    {{-- Status Summary --}}
    <div class="card">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-sm text-secondary-500">Status</p>
                    <p class="text-lg font-bold text-secondary-900">
                        <span x-text="summary.status" class="capitalize"></span>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-secondary-500">Total Soal</p>
                    <p class="text-lg font-bold text-secondary-900" x-text="summary.total_soal || '-'"></p>
                </div>
                <div>
                    <p class="text-sm text-secondary-500">Soal Dijawab</p>
                    <p class="text-lg font-bold text-success-600" x-text="summary.soal_dijawab || '0'"></p>
                </div>
                <div>
                    <p class="text-sm text-secondary-500">Total Nilai</p>
                    <p class="text-lg font-bold" :class="summary.total_nilai >= summary.passing_grade ? 'text-success-600' : 'text-danger-600'">
                        <span x-text="summary.total_nilai !== null ? summary.total_nilai.toFixed(2) : '-'"></span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Soal Table --}}
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-secondary-900">Daftar Soal</h3>
                <p class="text-sm text-secondary-500 mt-1">Grid jawaban peserta, status benar/salah, dan skor</p>
            </div>
            <button @click="refreshData()" class="btn btn-ghost btn-sm">Refresh</button>
        </div>
        <div class="card-body-sm overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-secondary-50 border-b border-secondary-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-secondary-900">No.</th>
                        <th class="px-4 py-3 text-left font-semibold text-secondary-900">Sub Indikator</th>
                        <th class="px-4 py-3 text-center font-semibold text-secondary-900">Jawaban</th>
                        <th class="px-4 py-3 text-center font-semibold text-secondary-900">Status</th>
                        <th class="px-4 py-3 text-center font-semibold text-secondary-900">Skor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-secondary-100">
                    <template x-for="(soal, idx) in soals" :key="soal.id">
                        <tr class="hover:bg-secondary-50 transition-colors">
                            <td class="px-4 py-3 font-mono font-semibold text-secondary-900" x-text="idx + 1"></td>
                            <td class="px-4 py-3 text-secondary-700">
                                <span x-text="soal.sub_indikator"></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold"
                                      :class="soal.jawaban ? 'bg-primary-100 text-primary-700' : 'bg-secondary-100 text-secondary-500'"
                                      x-text="soal.jawaban || '-'"></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <template x-if="soal.jawaban === null">
                                    <span class="text-secondary-500 text-xs">Belum dijawab</span>
                                </template>
                                <template x-if="soal.benar === true">
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-success-700">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        Benar
                                    </span>
                                </template>
                                <template x-if="soal.benar === false">
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-danger-700">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                        Salah
                                    </span>
                                </template>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold"
                                      :class="soal.nilai > 0 ? 'bg-success-100 text-success-700' : 'bg-secondary-100 text-secondary-500'"
                                      x-text="soal.nilai !== null ? soal.nilai.toFixed(2) : '-'"></span>
                            </td>
                        </tr>
                    </template>
                    <template x-if="soals.length === 0">
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-secondary-500">
                                Tidak ada data
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function detailPengerjaan(config) {
    return {
        pesertaId: config.pesertaId,
        ujianId: config.ujianId,
        refreshUrl: config.refreshUrl,
        soals: [],
        summary: {
            status: '-',
            total_soal: 0,
            soal_dijawab: 0,
            total_nilai: null,
            passing_grade: 0
        },
        refreshTimer: null,

        init() {
            this.refreshData();
            this.startAutoRefresh();
        },

        startAutoRefresh() {
            this.refreshTimer = setInterval(() => this.refreshData(), 3000);
        },

        stopRefresh() {
            if (this.refreshTimer) {
                clearInterval(this.refreshTimer);
            }
        },

        async refreshData() {
            try {
                const response = await fetch(this.refreshUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (!response.ok) return;

                const data = await response.json();
                this.soals = data.soals || [];
                this.summary = data.summary || {};
            } catch (e) {
                console.error('Gagal refresh data:', e);
            }
        }
    };
}
</script>
@endpush
