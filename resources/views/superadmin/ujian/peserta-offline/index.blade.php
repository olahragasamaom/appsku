@extends('superadmin.layouts.app')

@section('title', 'Peserta Offline')

@section('breadcrumb')
    <a href="{{ route('superadmin.ujian.index') }}" class="text-secondary-500 hover:text-secondary-700">Manajemen Ujian</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Peserta Offline</span>
@endsection

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h2 class="font-semibold text-xl text-secondary-800">Peserta Offline</h2>
            <p class="text-sm text-secondary-500">{{ $ujian->nama_ujian }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('superadmin.ujian.peserta-offline.export', $ujian) }}" class="btn btn-secondary">
                Cetak Kartu
            </a>
            <a href="{{ route('superadmin.ujian.index') }}" class="btn btn-ghost">Kembali</a>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">
        @if(session('kode_akses'))
            <x-alert type="success">
                Peserta <strong>{{ session('nomor_peserta') }}</strong> berhasil dibuat.
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

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tambah Peserta</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('superadmin.ujian.peserta-offline.store', $ujian) }}" method="POST"
                      class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    @csrf
                    <div>
                        <label for="nomor_peserta" class="block text-sm font-medium text-secondary-700 mb-1">
                            Nomor Peserta <span class="text-danger-500">*</span>
                        </label>
                        <input type="text" name="nomor_peserta" id="nomor_peserta"
                               value="{{ old('nomor_peserta') }}"
                               class="input w-full @error('nomor_peserta') border-danger-500 @enderror" required>
                    </div>
                    <div>
                        <label for="nama_peserta" class="block text-sm font-medium text-secondary-700 mb-1">
                            Nama Peserta <span class="text-danger-500">*</span>
                        </label>
                        <input type="text" name="nama_peserta" id="nama_peserta"
                               value="{{ old('nama_peserta') }}"
                               class="input w-full @error('nama_peserta') border-danger-500 @enderror" required>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary w-full">Tambah</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3 class="card-title">Impor Peserta dari Excel</h3>
                <a href="{{ route('superadmin.ujian.peserta-offline.template') }}" class="btn btn-secondary btn-sm">
                    Unduh Template
                </a>
            </div>
            <div class="card-body">
                <p class="text-sm text-secondary-500 mb-4">
                    Unduh template terlebih dahulu, isi kolom <strong>Nomor Peserta</strong> dan
                    <strong>Nama Peserta</strong>, lalu unggah kembali. Kode akses akan dibuat otomatis
                    untuk setiap peserta.
                </p>
                <form action="{{ route('superadmin.ujian.peserta-offline.import', $ujian) }}" method="POST"
                      enctype="multipart/form-data"
                      class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    @csrf
                    <div class="md:col-span-2">
                        <label for="file" class="block text-sm font-medium text-secondary-700 mb-1">
                            File Excel/CSV <span class="text-danger-500">*</span>
                        </label>
                        <input type="file" name="file" id="file" accept=".xlsx,.xls,.csv"
                               class="input w-full @error('file') border-danger-500 @enderror" required>
                        @error('file')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary w-full">Impor</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card" x-data="pesertaOfflineTable({{ Js::from($peserta->map(fn ($p) => [
            'id' => $p->id,
            'nomor_peserta' => $p->nomor_peserta,
            'nama_peserta' => $p->nama_peserta,
            'kode_akses' => $p->kode_akses_plain ?? '—',
        ])) }})">
            <div class="card-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h3 class="card-title">Daftar Peserta (<span x-text="filtered.length"></span>)</h3>
                <div class="flex items-center gap-2">
                    <input type="text" x-model="search" placeholder="Cari nomor / nama..."
                           class="input w-full sm:w-64">
                    <button type="button" x-show="selected.length > 0" x-cloak
                            @click="confirmBulkDelete()"
                            class="btn btn-danger btn-sm whitespace-nowrap">
                        Hapus (<span x-text="selected.length"></span>)
                    </button>
                </div>
            </div>
            <div class="card-body">
                <x-table>
                    <x-slot name="header">
                        <th class="w-10">
                            <input type="checkbox" @change="toggleAll($event)" :checked="allChecked"
                                   class="rounded border-secondary-300 text-primary-600">
                        </th>
                        <th class="cursor-pointer select-none" @click="sortBy('nomor_peserta')">
                            Nomor Peserta <span x-text="sortIcon('nomor_peserta')"></span>
                        </th>
                        <th class="cursor-pointer select-none" @click="sortBy('nama_peserta')">
                            Nama Peserta <span x-text="sortIcon('nama_peserta')"></span>
                        </th>
                        <th>Kode Akses</th>
                        <th class="text-right">Aksi</th>
                    </x-slot>

                    <template x-for="item in paginated" :key="item.id">
                        <tr>
                            <td>
                                <input type="checkbox" :value="item.id" x-model.number="selected"
                                       class="rounded border-secondary-300 text-primary-600">
                            </td>
                            <td x-text="item.nomor_peserta"></td>
                            <td x-text="item.nama_peserta"></td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-primary-50 text-primary-700 font-mono font-bold text-sm tracking-wider border border-primary-100"
                                      x-text="item.kode_akses"></span>
                            </td>
                            <td class="text-right">
                                <button type="button"
                                        @click="confirmDelete(item)"
                                        class="btn btn-ghost btn-sm text-danger-600">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    </template>

                    <tr x-show="filtered.length === 0">
                        <td colspan="5" class="text-center text-secondary-500 py-6">
                            Tidak ada peserta yang cocok.
                        </td>
                    </tr>
                </x-table>

                {{-- Pagination --}}
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-4" x-show="filtered.length > 0">
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
            </div>

            {{-- Hidden bulk delete form --}}
            <form x-ref="bulkForm" method="POST"
                  action="{{ route('superadmin.ujian.peserta-offline.bulk-destroy', $ujian) }}" class="hidden">
                @csrf
                <template x-for="id in selected" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
            </form>
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
                                String(r.nama_peserta).toLowerCase().includes(q)
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
                            title: 'Hapus Peserta',
                            message: `Yakin ingin menghapus peserta ${item.nama_peserta}?`,
                            confirmText: 'Ya, Hapus',
                            type: 'danger',
                            formAction: '{{ url('superadmin/ujian/'.$ujian->id.'/peserta-offline') }}/' + item.id,
                            method: 'DELETE',
                        });
                    },
                    confirmBulkDelete() {
                        this.$dispatch('confirm-dialog', {
                            title: 'Hapus Peserta Terpilih',
                            message: `Yakin ingin menghapus ${this.selected.length} peserta terpilih?`,
                            confirmText: 'Ya, Hapus Semua',
                            type: 'danger',
                            onConfirm: () => this.$refs.bulkForm.submit(),
                        });
                    },
                };
            }
        </script>
        @endpush
    </div>
@endsection
