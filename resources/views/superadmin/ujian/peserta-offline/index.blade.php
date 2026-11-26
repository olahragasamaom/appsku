@extends('superadmin.layouts.app')

@section('title', 'Peserta Offline')

@php
    $backRoute = route('superadmin.peserta-offline-ujian.index');
    $attachRoute = route('superadmin.peserta-offline-ujian.attach', $ujian);
    $bulkDestroyRoute = route('superadmin.peserta-offline-ujian.bulk-destroy', $ujian);
    $bulkResetRoute = route('superadmin.peserta-offline-ujian.bulk-reset-kode', $ujian);
    $exportRoute = route('superadmin.peserta-offline-ujian.export', $ujian);
    $actionPrefix = url('/superadmin/peserta-offline-ujian/' . $ujian->id . '/peserta');
@endphp

@section('breadcrumb')
    <span class="text-secondary-500">Peserta Offline</span>
    <span class="mx-2 text-secondary-400">/</span>
    <a href="{{ $backRoute }}" class="text-secondary-500 hover:text-secondary-700">Ujian</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Peserta: {{ $ujian->nama_ujian }}</span>
@endsection

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Peserta Offline — {{ $ujian->nama_ujian }}</h1>
            <p class="text-secondary-500 mt-1">Kelola dan masukkan peserta offline dari bank data ke ujian ini</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
           
            <button type="button" @click="$dispatch('open-bank-peserta')" class="btn btn-primary">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                Bank Data Peserta
            </button>
            <a href="{{ $exportRoute }}" class="btn btn-secondary">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak Kartu
            </a>
            <form action="{{ $bulkResetRoute }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-warning"
                        onclick="return confirm('Yakin ingin reset kode akses SEMUA peserta di ujian ini? Kode lama akan tidak berlaku lagi!')">
                    Reset Semua Kode
                </button>
            </form>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">
        @if(session('kode_akses'))
            <x-alert type="success">
                Peserta <strong>{{ session('nomor_peserta') }}</strong> berhasil ditambahkan.
                Kode Akses: <strong>{{ session('kode_akses') }}</strong>
                (catat sekarang, kode ini tidak akan ditampilkan kembali).
            </x-alert>
        @endif

        @if($errors->any())
            <x-alert type="danger">{{ $errors->first() }}</x-alert>
        @endif

        @if(session('error'))
            <x-alert type="danger">{{ session('error') }}</x-alert>
        @endif

        @if(session('import_errors'))
            <x-alert type="warning">
                <p class="font-medium mb-1">Beberapa baris dilewati:</p>
                <ul class="list-disc list-inside text-sm space-y-0.5">
                    @foreach(session('import_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        {{-- Table Peserta Terdaftar di Ujian Ini --}}
        <div class="card" x-data="pesertaOfflineTable({{ Js::from($peserta->map(fn ($p) => [
            'id' => $p->id,
            'nomor_peserta' => $p->nomor_peserta,
            'nama_peserta' => $p->nama_peserta,
            'nama_kelas' => $p->nama_kelas ?? '—',
            'nomor_hp' => $p->nomor_hp ?? '—',
            'kode_akses' => $p->kode_akses_plain ?? '—',
        ])) }})">
            <div class="card-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="card-title">Daftar Peserta Ujian Ini (<span x-text="filtered.length"></span>)</h3>
                    <p class="text-xs text-secondary-500 mt-0.5">Peserta yang terdaftar untuk mengikuti ujian ini</p>
                </div>
                <div class="flex items-center gap-2">
                    <input type="text" x-model="search" placeholder="Cari nomor / nama / kelas..."
                           class="input w-full sm:w-64">
                    <button type="button" x-show="selected.length > 0" x-cloak
                            @click="confirmBulkDelete()"
                            class="btn btn-danger btn-sm whitespace-nowrap">
                        Lepas Terpilih (<span x-text="selected.length"></span>)
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <x-table>
                    <x-slot name="header">
                        <th class="w-10">
                            <input type="checkbox" @change="toggleAll($event)" :checked="allChecked"
                                   class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500">
                        </th>
                        <th class="cursor-pointer select-none px-6 py-3 text-left" @click="sortBy('nomor_peserta')">
                            Nomor Peserta <span x-text="sortIcon('nomor_peserta')"></span>
                        </th>
                        <th class="cursor-pointer select-none px-6 py-3 text-left" @click="sortBy('nama_peserta')">
                            Nama Peserta <span x-text="sortIcon('nama_peserta')"></span>
                        </th>
                        <th class="cursor-pointer select-none px-6 py-3 text-left" @click="sortBy('nama_kelas')">
                            Kelas / Gelombang <span x-text="sortIcon('nama_kelas')"></span>
                        </th>
                        <th class="px-6 py-3 text-left">No. HP</th>
                        <th class="px-6 py-3 text-center">Kode Akses</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </x-slot>

                    <template x-for="item in paginated" :key="item.id">
                        <tr class="hover:bg-secondary-50 transition-colors">
                            <td class="px-4 py-4">
                                <input type="checkbox" :value="item.id" x-model.number="selected"
                                       class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500">
                            </td>
                            <td class="px-6 py-4 font-mono font-medium text-secondary-900" x-text="item.nomor_peserta"></td>
                            <td class="px-6 py-4 font-medium text-secondary-900" x-text="item.nama_peserta"></td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200"
                                      x-text="item.nama_kelas"></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-secondary-600" x-text="item.nomor_hp"></td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-primary-50 text-primary-700 font-mono font-bold text-xs tracking-wider border border-primary-100"
                                      x-text="item.kode_akses"></span>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="inline-flex gap-1 items-center">
                                    <form :action="`{{ $actionPrefix }}/${item.id}/reset-kode`"
                                          method="POST" class="inline"
                                          @submit="return confirm('Yakin ingin reset kode akses peserta ini?')">
                                        @csrf
                                        <button type="submit"
                                                class="btn btn-ghost btn-xs text-primary-600 hover:text-primary-700"
                                                title="Reset kode akses">
                                            Reset Kode
                                        </button>
                                    </form>
                                    <button type="button"
                                            @click="confirmDelete(item)"
                                            class="btn btn-ghost btn-xs text-danger-600 hover:text-danger-800"
                                            title="Lepas dari ujian ini">
                                        Lepas
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <tr x-show="filtered.length === 0">
                        <td colspan="7" class="text-center text-secondary-500 py-10">
                            Belum ada peserta di ujian ini. Klik tombol <strong>+ Tambah dari Bank Peserta</strong> di atas untuk memasukkan peserta.
                        </td>
                    </tr>
                </x-table>
            </div>

            {{-- Pagination --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-4 border-t border-secondary-200" x-show="filtered.length > 0">
                <div class="text-sm text-secondary-500">
                    Menampilkan <span x-text="rangeStart"></span>–<span x-text="rangeEnd"></span>
                    dari <span x-text="filtered.length"></span> peserta
                </div>
                <div class="flex items-center gap-1">
                    <button type="button" class="btn btn-ghost btn-sm" @click="prevPage()" :disabled="page === 1">
                        Sebelumnya
                    </button>
                    <template x-for="p in pageNumbers" :key="p">
                        <button type="button" class="btn btn-sm"
                                :class="p === page ? 'btn-primary' : 'btn-ghost'"
                                @click="page = p" x-text="p"></button>
                    </template>
                    <button type="button" class="btn btn-ghost btn-sm" @click="nextPage()" :disabled="page === totalPages">
                        Berikutnya
                    </button>
                </div>
            </div>

            {{-- Hidden bulk delete form --}}
            <form x-ref="bulkForm" method="POST"
                  action="{{ $bulkDestroyRoute }}" class="hidden">
                @csrf
                <template x-for="id in selected" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
            </form>
        </div>

        {{-- Modal Bank Data Peserta Offline --}}
        <div x-data="bankPesertaModal({{ Js::from($bankPeserta->map(fn ($p) => [
                 'id' => $p->id,
                 'nomor_peserta' => $p->nomor_peserta,
                 'nama_peserta' => $p->nama_peserta,
                 'nama_kelas' => $p->nama_kelas ?? 'Tanpa Kelas',
                 'nomor_hp' => $p->nomor_hp ?? '—',
             ])) }})"
             x-on:open-bank-peserta.window="open()"
             x-on:keydown.escape.window="show = false"
             x-show="show"
             x-cloak
             class="modal-backdrop">
            <div @click.outside="show = false" x-show="show" class="modal max-w-3xl">
                <div class="modal-header">
                    <div>
                        <h3 class="modal-title">Bank Data Peserta Offline</h3>
                        <p class="text-xs text-secondary-500 mt-0.5">Pilih peserta offline yang akan dimasukkan ke ujian ini</p>
                    </div>
                    <button type="button" @click="show = false" class="modal-close">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ $attachRoute }}" class="flex flex-col flex-1 min-h-0">
                    @csrf
                    <div class="modal-body space-y-4">
                        {{-- Filters --}}
                        <div class="flex flex-col sm:flex-row gap-3">
                            <div class="flex-1">
                                <input type="text" x-model="search" placeholder="Cari nama atau nomor peserta..."
                                       class="input w-full">
                            </div>
                            <div class="w-full sm:w-64">
                                <select x-model="selectedKelas" class="input w-full">
                                    <option value="">-- Semua Kelas / Gelombang --</option>
                                    @foreach($kelasList as $kelas)
                                        <option value="{{ $kelas }}">{{ $kelas }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Selection Controls --}}
                        <div class="flex items-center justify-between text-xs text-secondary-600 bg-secondary-50 p-2.5 rounded-lg border border-secondary-200">
                            <label class="flex items-center gap-2 cursor-pointer select-none font-medium">
                                <input type="checkbox" @change="toggleSelectAll($event)" :checked="isAllSelected"
                                       class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500">
                                <span>Pilih Semua yang Ditampilkan (<span x-text="filteredCandidates.length"></span>)</span>
                            </label>
                            <span class="font-semibold text-primary-700">
                                <span x-text="selectedIds.length"></span> peserta dipilih
                            </span>
                        </div>

                        {{-- Candidate List --}}
                        <div class="max-h-96 overflow-y-auto space-y-2 border border-secondary-200 rounded-lg p-2 bg-slate-50/50">
                            <template x-if="filteredCandidates.length === 0">
                                <div class="text-center py-8 text-secondary-500 text-sm">
                                    <p>Tidak ada peserta yang cocok atau semua peserta sudah terdaftar di ujian ini.</p>
                                    <a href="{{ route('superadmin.data-peserta-offline.create') }}" target="_blank" class="text-primary-600 underline text-xs mt-1 inline-block">
                                        + Tambah Peserta Baru di Master Data
                                    </a>
                                </div>
                            </template>

                            <template x-for="candidate in filteredCandidates" :key="candidate.id">
                                <label class="flex items-center justify-between p-3 rounded-lg border border-secondary-200 bg-white hover:bg-primary-50/40 hover:border-primary-300 cursor-pointer transition-colors">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" name="peserta_ids[]" :value="candidate.id" x-model.number="selectedIds"
                                               class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-secondary-900 text-sm" x-text="candidate.nama_peserta"></span>
                                                <span class="font-mono text-xs text-secondary-500 bg-secondary-100 px-1.5 py-0.5 rounded" x-text="candidate.nomor_peserta"></span>
                                            </div>
                                            <div class="text-xs text-secondary-500 mt-0.5">
                                                No. HP: <span x-text="candidate.nomor_hp"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200"
                                              x-text="candidate.nama_kelas"></span>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </div>

                    <div class="modal-footer flex items-center justify-between gap-3">
                        <div class="text-xs text-secondary-500">
                            Total kandidat: <span class="font-semibold" x-text="candidates.length"></span> peserta
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="show = false" class="btn btn-secondary">Batal</button>
                            <button type="submit" class="btn btn-primary" :disabled="selectedIds.length === 0">
                                Tambah
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @push('scripts')
        <script>
            function pesertaOfflineTable(rows) {
                return {
                    rows: rows,
                    search: '',
                    selected: [],
                    sortField: 'nomor_peserta',
                    sortDir: 'asc',
                    page: 1,
                    perPage: 15,

                    get filtered() {
                        let data = this.rows;
                        const q = this.search.trim().toLowerCase();
                        if (q) {
                            data = data.filter(r =>
                                String(r.nomor_peserta).toLowerCase().includes(q) ||
                                String(r.nama_peserta).toLowerCase().includes(q) ||
                                String(r.nama_kelas).toLowerCase().includes(q) ||
                                String(r.nomor_hp).toLowerCase().includes(q)
                            );
                        }
                        const dir = this.sortDir === 'asc' ? 1 : -1;
                        return [...data].sort((a, b) =>
                            String(a[this.sortField]).localeCompare(String(b[this.sortField]), 'id', { numeric: true }) * dir
                        );
                    },
                    get totalPages() {
                        return Math.max(1, Math.ceil(this.filtered.length / this.perPage));
                    },
                    get paginated() {
                        const start = (this.page - 1) * this.perPage;
                        return this.filtered.slice(start, start + this.perPage);
                    },
                    get pageNumbers() {
                        return Array.from({ length: this.totalPages }, (_, i) => i + 1);
                    },
                    get rangeStart() {
                        return this.filtered.length === 0 ? 0 : (this.page - 1) * this.perPage + 1;
                    },
                    get rangeEnd() {
                        return Math.min(this.page * this.perPage, this.filtered.length);
                    },
                    get allChecked() {
                        const ids = this.paginated.map(r => r.id);
                        return ids.length > 0 && ids.every(id => this.selected.includes(id));
                    },
                    sortBy(field) {
                        if (this.sortField === field) {
                            this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
                        } else {
                            this.sortField = field;
                            this.sortDir = 'asc';
                        }
                        this.page = 1;
                    },
                    sortIcon(field) {
                        if (this.sortField !== field) return '↕';
                        return this.sortDir === 'asc' ? '↑' : '↓';
                    },
                    toggleAll(event) {
                        const ids = this.paginated.map(r => r.id);
                        if (event.target.checked) {
                            this.selected = [...new Set([...this.selected, ...ids])];
                        } else {
                            this.selected = this.selected.filter(id => !ids.includes(id));
                        }
                    },
                    prevPage() { if (this.page > 1) this.page--; },
                    nextPage() { if (this.page < this.totalPages) this.page++; },
                    confirmDelete(item) {
                        this.$dispatch('confirm-dialog', {
                            title: 'Lepas Peserta dari Ujian',
                            message: `Lepas peserta ${item.nama_peserta} dari ujian ini? Data master peserta tetap tersimpan di bank data.`,
                            confirmText: 'Ya, Lepas',
                            type: 'danger',
                            formAction: '{{ $actionPrefix }}/' + item.id,
                            method: 'DELETE',
                        });
                    },
                    confirmBulkDelete() {
                        this.$dispatch('confirm-dialog', {
                            title: 'Lepas Peserta Terpilih',
                            message: `Yakin ingin melepas ${this.selected.length} peserta terpilih dari ujian ini? Data master tetap tersimpan.`,
                            confirmText: 'Ya, Lepas Semua',
                            type: 'danger',
                            onConfirm: () => this.$refs.bulkForm.submit(),
                        });
                    },
                };
            }

            function bankPesertaModal(candidates) {
                return {
                    show: false,
                    candidates: candidates,
                    search: '',
                    selectedKelas: '',
                    selectedIds: [],
                    open() {
                        this.show = true;
                        this.search = '';
                        this.selectedKelas = '';
                        this.selectedIds = [];
                    },
                    get filteredCandidates() {
                        return this.candidates.filter(c => {
                            const matchSearch = !this.search ||
                                c.nama_peserta.toLowerCase().includes(this.search.toLowerCase()) ||
                                c.nomor_peserta.toLowerCase().includes(this.search.toLowerCase()) ||
                                c.nomor_hp.toLowerCase().includes(this.search.toLowerCase());

                            const matchKelas = !this.selectedKelas || c.nama_kelas === this.selectedKelas;

                            return matchSearch && matchKelas;
                        });
                    },
                    get isAllSelected() {
                        const visible = this.filteredCandidates;
                        return visible.length > 0 && visible.every(c => this.selectedIds.includes(c.id));
                    },
                    toggleSelectAll(event) {
                        const visibleIds = this.filteredCandidates.map(c => c.id);
                        if (event.target.checked) {
                            this.selectedIds = [...new Set([...this.selectedIds, ...visibleIds])];
                        } else {
                            this.selectedIds = this.selectedIds.filter(id => !visibleIds.includes(id));
                        }
                    }
                };
            }
        </script>
        @endpush
    </div>
@endsection
