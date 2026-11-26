@extends('superadmin.layouts.app')

@section('title', 'Data Kelas')

@section('breadcrumb')
    <span class="text-secondary-500">Peserta Offline</span>
    <span class="text-secondary-400 mx-2">/</span>
    <span class="text-secondary-900 font-medium">Data Kelas</span>
@endsection

@section('content')
<div x-data="kelasManager()" class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Data Kelas & Gelombang</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola master kelas / gelombang pelatihan untuk peserta offline</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('superadmin.data-peserta-offline.index') }}" class="btn btn-secondary text-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12H9m6 0a6 6 0 11-12 0 6 6 0 0112 0z"/>
                </svg>
                Data Peserta Offline
            </a>
            <button type="button" @click="openCreateModal()" class="btn btn-primary text-sm shadow-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Kelas
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card p-4 flex items-center gap-4 bg-white border border-slate-200 rounded-xl shadow-xs">
            <div class="w-12 h-12 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Kelas</p>
                <p class="text-2xl font-bold text-slate-800">{{ number_format($totalKelas) }}</p>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-4 bg-white border border-slate-200 rounded-xl shadow-xs">
            <div class="w-12 h-12 rounded-lg bg-success-50 text-success-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Kelas Aktif</p>
                <p class="text-2xl font-bold text-success-600">{{ number_format($totalAktif) }}</p>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-4 bg-white border border-slate-200 rounded-xl shadow-xs">
            <div class="w-12 h-12 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Peserta Terdaftar</p>
                <p class="text-2xl font-bold text-purple-600">{{ number_format($totalPeserta) }}</p>
            </div>
        </div>
    </div>

    {{-- Filter & Search Card --}}
    <div class="card bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
        <form method="GET" action="{{ route('superadmin.kelas.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <div class="relative flex-1">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari nama kelas atau deskripsi..."
                       class="input input-sm w-full pl-9 pr-4 text-sm rounded-lg border-slate-300">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <div class="w-full sm:w-48">
                <select name="status" class="select select-sm w-full text-sm rounded-lg border-slate-300" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                @if(request()->anyFilled(['search', 'status']))
                    <a href="{{ route('superadmin.kelas.index') }}" class="btn btn-ghost btn-sm text-slate-500">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Classes Table --}}
    <div class="card bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-600">
                    <tr>
                        <th class="px-6 py-3.5">Nama Kelas / Gelombang</th>
                        <th class="px-6 py-3.5 text-center">Jumlah Peserta</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5">Tanggal Dibuat</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($kelasList as $kelas)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2.5">
                                   
                                    <div>
                                        <p class="font-semibold text-slate-800">{{ $kelas->nama_kelas }}</p>
                                    </div>
                                </div>
                            </td>
                           
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('superadmin.data-peserta-offline.index', ['nama_kelas' => $kelas->nama_kelas, 'kelas_id' => $kelas->id]) }}"
                                   class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-purple-50 text-purple-700 hover:bg-purple-100 transition-colors"
                                   title="Lihat peserta kelas {{ $kelas->nama_kelas }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12H9m6 0a6 6 0 11-12 0 6 6 0 0112 0z"/>
                                    </svg>
                                    {{ $kelas->peserta_offline_count }} Peserta
                                </a>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($kelas->status === 'aktif')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-success-50 text-success-700 border border-success-200">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-xs">
                                {{ $kelas->created_at ? $kelas->created_at->format('d M Y, H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    {{-- Tambah Peserta ke Kelas Ini (Biru) --}}
                                    <a href="{{ route('superadmin.data-peserta-offline.create', ['kelas_id' => $kelas->id, 'nama_kelas' => $kelas->nama_kelas]) }}"
                                       class="btn btn-sm bg-blue-600 hover:bg-blue-700 text-white border-0"
                                       title="Tambah Peserta ke Kelas {{ $kelas->nama_kelas }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                        </svg>
                                    </a>

                                    {{-- Edit Kelas (Kuning) --}}
                                    <button type="button"
                                            @click="openEditModal({{ Js::from($kelas) }})"
                                            class="btn btn-sm bg-yellow-400 hover:bg-yellow-500 text-white border-0"
                                            title="Edit Kelas">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>

                                    {{-- Hapus Kelas (Merah) --}}
                                    <form method="POST"
                                          action="{{ route('superadmin.kelas.destroy', $kelas) }}"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelas ini? Peserta di kelas ini akan tetap ada namun status kelasnya menjadi tidak terhubung.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm bg-danger-500 hover:bg-danger-600 text-white border-0"
                                                title="Hapus Kelas">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <p class="text-base font-semibold text-slate-600">Belum ada data kelas</p>
                                <p class="text-xs text-slate-400 mt-1">Klik tombol "Tambah Kelas" untuk menambahkan kelas baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($kelasList->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $kelasList->links() }}
            </div>
        @endif
    </div>

    {{-- Alpine Modal Form (Create / Edit) --}}
    <div x-show="showModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div @click.outside="showModal = false"
             class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden transform transition-all border border-slate-100"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <div>
                    <h3 class="text-lg font-bold text-slate-800" x-text="isEdit ? 'Edit Data Kelas' : 'Tambah Kelas Baru'"></h3>
                    <p class="text-xs text-slate-500 mt-0.5" x-text="isEdit ? 'Perbarui informasi kelas pelatihan' : 'Buat kelas / gelombang baru untuk peserta offline'"></p>
                </div>
                <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Modal Form --}}
            <form :action="formAction" method="POST">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="p-6 space-y-4">
                    <div>
                        <label for="modal_nama_kelas" class="block text-sm font-semibold text-slate-700 mb-1">
                            Nama Kelas / Gelombang <span class="text-danger-500">*</span>
                        </label>
                        <input type="text"
                               id="modal_nama_kelas"
                               name="nama_kelas"
                               x-model="formData.nama_kelas"
                               required
                               placeholder="Contoh: Pelatihan CPNS Gelombang 1"
                               class="input w-full text-sm rounded-lg border-slate-300">
                    </div>

                    <div>
                        <label for="modal_deskripsi" class="block text-sm font-semibold text-slate-700 mb-1">
                            Deskripsi / Keterangan (Opsional)
                        </label>
                        <textarea id="modal_deskripsi"
                                  name="deskripsi"
                                  x-model="formData.deskripsi"
                                  rows="3"
                                  placeholder="Keterangan jadwal, lokasi bimbel, atau catatan lainnya..."
                                  class="textarea w-full text-sm rounded-lg border-slate-300"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Status Kelas</label>
                        <div class="flex items-center gap-4">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="status" value="aktif" x-model="formData.status" class="radio radio-primary">
                                <span class="text-sm font-medium text-slate-700">Aktif</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="status" value="nonaktif" x-model="formData.status" class="radio">
                                <span class="text-sm font-medium text-slate-700">Nonaktif</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="showModal = false" class="btn btn-secondary text-sm">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary text-sm shadow-sm" x-text="isEdit ? 'Simpan Perubahan' : 'Tambah Kelas'">
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function kelasManager() {
    return {
        showModal: false,
        isEdit: false,
        formAction: '{{ route('superadmin.kelas.store') }}',
        formData: {
            nama_kelas: '',
            deskripsi: '',
            status: 'aktif'
        },
        openCreateModal() {
            this.isEdit = false;
            this.formAction = '{{ route('superadmin.kelas.store') }}';
            this.formData = {
                nama_kelas: '',
                deskripsi: '',
                status: 'aktif'
            };
            this.showModal = true;
        },
        openEditModal(kelas) {
            this.isEdit = true;
            this.formAction = '/superadmin/kelas/' + kelas.id;
            this.formData = {
                nama_kelas: kelas.nama_kelas || '',
                deskripsi: kelas.deskripsi || '',
                status: kelas.status || 'aktif'
            };
            this.showModal = true;
        }
    };
}
</script>
@endsection
