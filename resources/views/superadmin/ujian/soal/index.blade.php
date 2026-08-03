@extends('superadmin.layouts.app')

@section('title', 'Kelola Soal Ujian')

@section('breadcrumb')
    <a href="{{ route('superadmin.ujian.index') }}" class="text-secondary-500 hover:text-secondary-700">Manajemen Ujian</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Kelola Soal</span>
@endsection

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Kelola Soal — {{ $ujian->nama_ujian }}</h1>
            <p class="text-secondary-500 mt-1">Total {{ $totalSoal }} / {{ $ujian->jumlah_soal }} soal terisi</p>
        </div>
        <a href="{{ route('superadmin.ujian.edit', $ujian) }}" class="btn btn-secondary">Kembali ke Ujian</a>
    </div>
@endsection

@section('content')
    @if($jenisUjians->isEmpty())
        <div class="card">
            <div class="card-body text-center text-secondary-500">
                Ujian ini belum memiliki jenis ujian. Tambahkan jenis ujian terlebih dahulu pada
                <a href="{{ route('superadmin.ujian.edit', $ujian) }}" class="text-primary-600 underline">form ujian</a>.
            </div>
        </div>
    @else
        {{-- Tab Navigasi Jenis Ujian --}}
        <div class="flex flex-wrap gap-2 mb-6 border-b border-secondary-200">
            @foreach($jenisUjians as $jenis)
                <a href="{{ route('superadmin.ujian.soal.index', ['ujian' => $ujian, 'jenis_ujian_id' => $jenis->id]) }}"
                   class="px-4 py-2 -mb-px text-sm font-medium border-b-2 transition-colors {{ $activeJenisId === $jenis->id ? 'border-primary-600 text-primary-600' : 'border-transparent text-secondary-500 hover:text-secondary-700' }}">
                    {{ $jenis->nama_jenis_ujian }}
                </a>
            @endforeach
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <h2 class="text-lg font-semibold text-secondary-900">
                Daftar Soal {{ $jenisUjians->firstWhere('id', $activeJenisId)?->nama_jenis_ujian }}
            </h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('superadmin.soal.create', ['jenis_ujian_id' => $activeJenisId]) }}"
                   class="btn btn-secondary btn-sm">Tambah Soal Manual</a>
                <button type="button" @click="$dispatch('open-bank-soal')" class="btn btn-primary btn-sm">
                    Pilih dari Bank Soal
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body-sm">
                <x-table>
                    <x-slot name="header">
                        <th class="px-6 py-3 text-left w-16">No</th>
                        <th class="px-6 py-3 text-left">Soal</th>
                        <th class="px-6 py-3 text-left">Sub Indikator</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </x-slot>

                    @forelse($ujianSoals as $index => $ujianSoal)
                        <tr class="hover:bg-secondary-50">
                            <td class="px-6 py-4 text-secondary-500">{{ $index + 1 }}</td>
                            <td class="px-6 py-4">
                                <p class="text-secondary-900 line-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags($ujianSoal->soal->soal ?? ''), 100) }}</p>
                            </td>
                            <td class="px-6 py-4 text-secondary-700">
                                {{ $ujianSoal->soal->subIndikator?->nama_sub_indikator ?? '-' }}
                                <span class="block text-xs text-secondary-400">{{ $ujianSoal->soal->subIndikator?->subJenisUjian?->nama_sub_jenis_ujian }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('superadmin.soal.edit', $ujianSoal->soal) }}" class="btn btn-ghost btn-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <button type="button"
                                            @click="$dispatch('confirm-dialog', {
                                                title: 'Hapus Soal dari Ujian',
                                                message: 'Hapus soal ini dari ujian? Soal tetap ada di bank soal.',
                                                confirmText: 'Ya, Hapus',
                                                type: 'danger',
                                                formAction: '{{ route('superadmin.ujian.soal.detach', ['ujian' => $ujian, 'ujianSoal' => $ujianSoal->id]) }}',
                                                method: 'DELETE'
                                            })"
                                            class="btn btn-ghost btn-sm text-danger-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-secondary-500">
                                Belum ada soal pada kategori ini
                            </td>
                        </tr>
                    @endforelse
                </x-table>
            </div>
        </div>

        {{-- Modal Pilih dari Bank Soal --}}
        <div x-data="bankSoalPicker({
                optionsUrl: '{{ route('superadmin.ujian.soal.bank-options', $ujian) }}',
                jenisUjianId: {{ $activeJenisId }}
             })"
             x-on:open-bank-soal.window="open()"
             x-on:keydown.escape.window="show = false"
             x-show="show"
             x-cloak
             class="modal-backdrop">
            <div @click.outside="show = false" x-show="show" class="modal max-w-2xl">
                <div class="modal-header">
                    <h3 class="modal-title">Pilih Soal dari Bank Soal</h3>
                    <button type="button" @click="show = false" class="modal-close">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('superadmin.ujian.soal.attach', $ujian) }}">
                    @csrf
                    <input type="hidden" name="jenis_ujian_id" :value="jenisUjianId">
                    <div class="modal-body">
                        <div class="flex gap-2 mb-4">
                            <input type="text" x-model="search" @input.debounce.400ms="load()"
                                   class="input w-full" placeholder="Cari soal...">
                        </div>

                        <div class="max-h-80 overflow-y-auto space-y-2">
                            <template x-if="loading">
                                <p class="text-sm text-secondary-500 text-center py-6">Memuat...</p>
                            </template>
                            <template x-if="!loading && items.length === 0">
                                <p class="text-sm text-secondary-500 text-center py-6">Tidak ada soal tersedia</p>
                            </template>
                            <template x-for="item in items" :key="item.id">
                                <label class="flex items-start gap-3 p-3 border border-secondary-200 rounded-lg hover:bg-secondary-50 cursor-pointer">
                                    <input type="checkbox" name="soal_id[]" :value="item.id" class="mt-1 rounded border-secondary-300 text-primary-600">
                                    <span class="flex-1">
                                        <span class="block text-sm text-secondary-800" x-text="item.soal"></span>
                                        <span class="block text-xs text-secondary-400" x-text="(item.sub_jenis_ujian || '') + ' — ' + (item.sub_indikator || '')"></span>
                                    </span>
                                </label>
                            </template>
                        </div>
                    </div>

                    <div class="modal-footer flex items-center justify-end gap-3">
                        <button type="button" @click="show = false" class="btn btn-secondary">Batal</button>
                        <button type="submit" class="btn btn-primary">Tambahkan Soal Terpilih</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    function bankSoalPicker(config) {
        return {
            show: false,
            loading: false,
            search: '',
            items: [],
            jenisUjianId: config.jenisUjianId,
            optionsUrl: config.optionsUrl,
            open() {
                this.show = true;
                this.load();
            },
            async load() {
                this.loading = true;
                const url = new URL(this.optionsUrl, window.location.origin);
                url.searchParams.set('jenis_ujian_id', this.jenisUjianId);
                if (this.search) url.searchParams.set('search', this.search);
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                this.items = res.ok ? await res.json() : [];
                this.loading = false;
            }
        };
    }
</script>
@endpush
