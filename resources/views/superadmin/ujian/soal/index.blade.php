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
            <p class="text-secondary-500 mt-1">
                Total <span class="font-semibold {{ $totalSoal < $ujian->jumlah_soal ? 'text-danger-600' : 'text-success-600' }}">{{ $totalSoal }}</span> / {{ $ujian->jumlah_soal }} soal terisi
            </p>
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

        {{-- PANEL SUB INDIKATOR: tombol per sub indikator dengan jumlah soal + aksi tambah/import --}}
        <div class="card mb-6">
            <div class="card-header">
                <h2 class="text-lg font-semibold text-secondary-900">Sub Indikator</h2>
                <p class="text-sm text-secondary-500">Tambah soal manual atau import Excel per sub indikator</p>
            </div>
            <div class="card-body space-y-5">
                @forelse($subIndikatorGroups as $subJenis)
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="font-semibold text-secondary-800">{{ $subJenis->nama_sub_jenis_ujian }}</span>
                            <span class="text-xs bg-secondary-100 text-secondary-600 px-2 py-0.5 rounded-md">
                                {{ $subJenis->sistem_penilaian === 'benar_salah' ? 'Benar-Salah' : 'Poin per Jawaban' }}
                            </span>
                        </div>

                        @if($subJenis->subIndikator->isEmpty())
                            <p class="text-sm text-secondary-400 italic ml-1">Belum ada sub indikator.</p>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($subJenis->subIndikator as $indikator)
                                    @php $jumlahDiSubIndikator = $jumlahSoalPerSubIndikator[$indikator->id] ?? 0; @endphp
                                    <div class="border border-secondary-200 rounded-lg p-4 bg-white">
                                        <div class="flex items-start justify-between gap-2 mb-3">
                                            <p class="text-sm font-medium text-secondary-800">{{ $indikator->nama_sub_indikator }}</p>
                                            <span class="flex-shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-primary-50 text-primary-700">
                                                {{ $jumlahDiSubIndikator }} soal
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('superadmin.soal.create', ['sub_indikator_id' => $indikator->id]) }}"
                                               target="_blank"
                                               class="btn btn-secondary btn-sm flex-1 inline-flex justify-center text-xs">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                Tambah
                                            </a>
                                            <button type="button"
                                                    @click="$dispatch('open-import-soal', {
                                                        subIndikatorId: {{ $indikator->id }},
                                                        subIndikatorNama: '{{ addslashes($indikator->nama_sub_indikator) }}'
                                                    })"
                                                    class="btn btn-primary btn-sm flex-1 inline-flex justify-center text-xs">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                                Import
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-secondary-500">Belum ada sub jenis ujian / sub indikator pada jenis ujian ini.</p>
                @endforelse
            </div>
        </div>

        {{-- TAB DAFTAR SOAL: Semua Soal vs Per Sub Indikator --}}
        <div x-data="{ tab: 'semua' }" class="card">
            <div class="card-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex gap-1 bg-secondary-100 p-1 rounded-lg">
                    <button type="button" @click="tab = 'semua'"
                            :class="tab === 'semua' ? 'bg-white shadow-sm text-primary-600' : 'text-secondary-500'"
                            class="px-4 py-1.5 rounded-md text-sm font-medium transition-colors">
                        Semua Soal
                    </button>
                    <button type="button" @click="tab = 'grup'"
                            :class="tab === 'grup' ? 'bg-white shadow-sm text-primary-600' : 'text-secondary-500'"
                            class="px-4 py-1.5 rounded-md text-sm font-medium transition-colors">
                        Per Sub Indikator
                    </button>
                </div>
                <button type="button" @click="$dispatch('open-bank-soal')" class="btn btn-primary btn-sm">
                    Pilih dari Bank Soal
                </button>
            </div>

            {{-- TAB 1: Semua Soal (flat) --}}
            <div x-show="tab === 'semua'" class="card-body-sm">
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

            {{-- TAB 2: Per Sub Indikator (dikelompokkan) --}}
            <div x-show="tab === 'grup'" x-cloak class="card-body space-y-6">
                @forelse($ujianSoalsPerSubIndikator as $namaSubIndikator => $soalGroup)
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <h3 class="font-semibold text-secondary-800">{{ $namaSubIndikator }}</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-primary-50 text-primary-700">
                                {{ $soalGroup->count() }} soal
                            </span>
                        </div>
                        <div class="space-y-2">
                            @foreach($soalGroup as $index => $ujianSoal)
                                <div class="flex items-start gap-3 p-3 border border-secondary-100 rounded-lg hover:bg-secondary-50">
                                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-secondary-100 text-secondary-600 text-xs font-semibold flex items-center justify-center">{{ $index + 1 }}</span>
                                    <p class="flex-1 text-sm text-secondary-800 line-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags($ujianSoal->soal->soal ?? ''), 120) }}</p>
                                    <a href="{{ route('superadmin.soal.edit', $ujianSoal->soal) }}" class="btn btn-ghost btn-sm flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-center text-secondary-500 py-8">Belum ada soal pada kategori ini</p>
                @endforelse
            </div>
        </div>

        {{-- Modal Import Excel per Sub Indikator --}}
        <div x-data="{ show: false, subIndikatorId: null, subIndikatorNama: '' }"
             x-on:open-import-soal.window="show = true; subIndikatorId = $event.detail.subIndikatorId; subIndikatorNama = $event.detail.subIndikatorNama"
             x-on:keydown.escape.window="show = false"
             x-effect="document.body.style.overflow = show ? 'hidden' : ''"
             x-show="show"
             x-cloak
             class="modal-backdrop">
            <div @click.outside="show = false" x-show="show" class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">Import Soal Excel</h3>
                    <button type="button" @click="show = false" class="modal-close">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('superadmin.ujian.soal.import', $ujian) }}" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0">
                    @csrf
                    <input type="hidden" name="sub_indikator_id" :value="subIndikatorId">
                    <div class="modal-body space-y-4">
                        <p class="text-sm text-secondary-600">
                            Import soal ke sub indikator: <span class="font-semibold text-secondary-900" x-text="subIndikatorNama"></span>
                        </p>
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-1">File Excel <span class="text-danger-500">*</span></label>
                            <input type="file" name="file" accept=".xlsx,.xls,.csv" class="input w-full" required>
                            @error('file')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="text-xs text-secondary-500 bg-secondary-50 rounded-lg p-3">
                            <p class="font-medium mb-1">Format kolom header (baris pertama):</p>
                            <code>soal, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e, kunci_jawaban, nilai_bobot_benar, pembahasan</code>
                        </div>
                    </div>
                    <div class="modal-footer flex items-center justify-end gap-3">
                        <button type="button" @click="show = false" class="btn btn-secondary">Batal</button>
                        <button type="submit" class="btn btn-primary">Import Soal</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Pilih dari Bank Soal --}}
        <div x-data="bankSoalPicker({
                optionsUrl: '{{ route('superadmin.ujian.soal.bank-options', $ujian) }}',
                jenisUjianId: {{ $activeJenisId }}
             })"
             x-on:open-bank-soal.window="open()"
             x-on:keydown.escape.window="show = false"
             x-effect="document.body.style.overflow = show ? 'hidden' : ''"
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

                <form method="POST" action="{{ route('superadmin.ujian.soal.attach', $ujian) }}" class="flex flex-col flex-1 min-h-0">
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
