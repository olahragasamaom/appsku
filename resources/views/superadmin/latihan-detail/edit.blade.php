@extends('superadmin.layouts.app')
@section('title', 'Edit Latihan Detail')

@section('breadcrumb')
    <a href="{{ route('superadmin.dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Dashboard</a>
    <span class="text-secondary-400">/</span>
    <a href="{{ route('superadmin.latihan-detail.index') }}" class="text-secondary-500 hover:text-secondary-700">Latihan Detail</a>
    <span class="text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Edit Transaksi</span>
@endsection

@section('header')
    <div>
        <h1 class="text-2xl font-bold text-secondary-900">Edit Transaksi Detail</h1>
        <p class="text-secondary-500 mt-1">Edit transaksi {{ $detail->nomor }}</p>
    </div>
@endsection

@section('content')
<div x-data="{
    items: {{ Js::from($detail->items->map(fn($item) => [
        'latihan_produk_id' => $item->latihan_produk_id,
        'nama_produk' => $item->produk->nama,
        'kode_produk' => $item->produk->kode_produk,
        'qty' => $item->qty,
        'harga' => $item->harga
    ])->values()) }},
    modalOpen: false,
    searchQuery: '',
    searchResults: [],
    searching: false,

    init() {
        this.$watch('items', () => this.calculateTotal());
        this.$nextTick(() => this.calculateTotal());
    },

    calculateTotal() {
        const total = this.items.reduce((sum, item) => sum + (item.qty * item.harga), 0);
        this.$refs.totalDisplay.textContent = 'Rp ' + total.toLocaleString('id-ID');
        this.$refs.totalInput.value = total;
    },

    addItem(produk) {
        this.items.push({
            latihan_produk_id: produk.id,
            nama_produk: produk.nama,
            kode_produk: produk.kode_produk,
            qty: 1,
            harga: produk.harga
        });
        this.modalOpen = false;
        this.searchQuery = '';
        this.searchResults = [];
    },

    removeItem(index) {
        this.items.splice(index, 1);
    },

    async searchProduk() {
        if (this.searchQuery.length < 2) {
            this.searchResults = [];
            return;
        }
        this.searching = true;
        try {
            const response = await fetch('{{ route('superadmin.latihan-detail.search-produk') }}?q=' + encodeURIComponent(this.searchQuery));
            this.searchResults = await response.json();
        } catch (error) {
            console.error('Search error:', error);
        }
        this.searching = false;
    }
}">

    <form action="{{ route('superadmin.latihan-detail.update', $detail) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-body space-y-6">
                {{-- Input 1: Nama Transaksi --}}
                <div>
                    <label for="nama_transaksi" class="block text-sm font-medium text-secondary-700 mb-1">
                        Nama Transaksi <span class="text-danger-500">*</span>
                    </label>
                    <input type="text" 
                           name="nama_transaksi" 
                           id="nama_transaksi" 
                           value="{{ old('nama_transaksi', $detail->nama_transaksi) }}"
                           class="input w-full @error('nama_transaksi') border-danger-500 @enderror"
                           placeholder="Contoh: Pembelian Barang Januari 2026"
                           required>
                    @error('nama_transaksi')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Input 2: Kategori (Dropdown) --}}
                <div>
                    <label for="latihan_kategori_id" class="block text-sm font-medium text-secondary-700 mb-1">
                        Kategori <span class="text-danger-500">*</span>
                    </label>
                    <select name="latihan_kategori_id" 
                            id="latihan_kategori_id"
                            class="input w-full @error('latihan_kategori_id') border-danger-500 @enderror"
                            required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($kategoris as $kategori)
                            <option value="{{ $kategori->id }}" 
                                    {{ old('latihan_kategori_id', $detail->latihan_kategori_id) == $kategori->id ? 'selected' : '' }}>
                                {{ $kategori->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('latihan_kategori_id')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Input 3: Tanggal --}}
                <div>
                    <label for="tanggal" class="block text-sm font-medium text-secondary-700 mb-1">
                        Tanggal <span class="text-danger-500">*</span>
                    </label>
                    <input type="date" 
                           name="tanggal" 
                           id="tanggal" 
                           value="{{ old('tanggal', $detail->tanggal->format('Y-m-d')) }}"
                           class="input w-full @error('tanggal') border-danger-500 @enderror"
                           required>
                    @error('tanggal')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Input 4: Catatan --}}
                <div>
                    <label for="catatan" class="block text-sm font-medium text-secondary-700 mb-1">
                        Catatan
                    </label>
                    <textarea name="catatan" 
                              id="catatan" 
                              rows="3"
                              class="input w-full @error('catatan') border-danger-500 @enderror"
                              placeholder="Catatan tambahan (opsional)">{{ old('catatan', $detail->catatan) }}</textarea>
                    @error('catatan')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- SECTION: Item Produk (Dynamic Rows) --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-medium text-secondary-700">
                            Item Produk <span class="text-danger-500">*</span>
                        </label>
                        <button type="button"
                                @click="modalOpen = true"
                                class="btn btn-sm btn-primary">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tambah Item
                        </button>
                    </div>

                    @error('items')
                        <p class="mb-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror

                    <div class="overflow-x-auto border border-secondary-200 rounded-lg">
                        <table class="w-full">
                            <thead class="bg-secondary-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-secondary-700 uppercase">Produk</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-secondary-700 uppercase w-24">Qty</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-secondary-700 uppercase w-40">Harga</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-secondary-700 uppercase w-40">Subtotal</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-secondary-700 uppercase w-16">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in items" :key="index">
                                    <tr class="border-t border-secondary-200">
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-medium text-secondary-900" x-text="item.nama_produk"></div>
                                            <div class="text-xs text-secondary-500" x-text="item.kode_produk"></div>
                                            <input type="hidden" :name="'items[' + index + '][latihan_produk_id]'" :value="item.latihan_produk_id">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" 
                                                   :name="'items[' + index + '][qty]'"
                                                   x-model.number="item.qty"
                                                   @input="calculateTotal()"
                                                   min="1"
                                                   class="input w-full"
                                                   required>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" 
                                                   :name="'items[' + index + '][harga]'"
                                                   x-model.number="item.harga"
                                                   @input="calculateTotal()"
                                                   step="0.01"
                                                   min="0"
                                                   class="input w-full"
                                                   required>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="text-sm font-medium text-secondary-900" x-text="'Rp ' + (item.qty * item.harga).toLocaleString('id-ID')"></div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <button type="button"
                                                    @click="removeItem(index)"
                                                    class="text-danger-600 hover:text-danger-700">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>

                                <tr x-show="items.length === 0">
                                    <td colspan="5" class="px-4 py-8 text-center text-secondary-500">
                                        Belum ada item. Klik tombol "Tambah Item" untuk memilih produk.
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-secondary-50 border-t-2 border-secondary-300">
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right font-bold text-secondary-900">
                                        TOTAL
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="text-lg font-bold text-primary-600" x-ref="totalDisplay">Rp 0</div>
                                        <input type="hidden" name="total" x-ref="totalInput" value="0">
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Footer dengan tombol aksi --}}
            <div class="card-footer flex items-center justify-end gap-3">
                <a href="{{ route('superadmin.latihan-detail.index') }}" class="btn btn-secondary">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
    {{-- MODAL: Product Picker --}}
    <div x-show="modalOpen" 
         x-cloak
         style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 99999;"
         @keydown.escape.window="modalOpen = false">
        
        {{-- Backdrop --}}
        <div @click="modalOpen = false"
             style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5);"></div>

        {{-- Modal Content --}}
        <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; padding: 1rem; pointer-events: none;">
            <div style="pointer-events: auto; width: 100%; max-width: 48rem; background-color: white; border-radius: 1rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); max-height: 90vh; display: flex; flex-direction: column;">
                
                {{-- Modal Header --}}
                <div style="padding: 1.5rem; border-bottom: 1px solid #e2e8f0;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <h3 style="font-size: 1.25rem; font-weight: 700; color: #1e293b;">Pilih Produk</h3>
                        <button type="button" 
                                @click="modalOpen = false"
                                style="color: #64748b; padding: 0.5rem; border-radius: 0.5rem; transition: all 0.2s;"
                                onmouseover="this.style.backgroundColor='#f1f5f9'"
                                onmouseout="this.style.backgroundColor='transparent'">
                            <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Modal Body --}}
                <div style="padding: 1.5rem; overflow-y: auto; flex: 1;">
                    {{-- Search Input --}}
                    <div style="margin-bottom: 1rem;">
                        <input type="text"
                               x-model="searchQuery"
                               @input.debounce.300ms="searchProduk()"
                               placeholder="Cari produk berdasarkan nama atau kode..."
                               style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #cbd5e1; border-radius: 0.5rem; font-size: 0.875rem;"
                               autofocus>
                    </div>

                    {{-- Loading State --}}
                    <div x-show="searching" style="text-align: center; padding: 2rem; color: #64748b;">
                        <svg style="width: 2rem; height: 2rem; margin: 0 auto; animation: spin 1s linear infinite;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <p style="margin-top: 0.5rem;">Mencari produk...</p>
                    </div>

                    {{-- Products List --}}
                    <div x-show="!searching && searchResults.length > 0" style="display: grid; gap: 0.5rem;">
                        <template x-for="produk in searchResults" :key="produk.id">
                            <div @click="addItem(produk)"
                                 style="padding: 1rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer; transition: all 0.2s;"
                                 onmouseover="this.style.backgroundColor='#f8fafc'; this.style.borderColor='#3b82f6'"
                                 onmouseout="this.style.backgroundColor='white'; this.style.borderColor='#e2e8f0'">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div style="flex: 1;">
                                        <div style="font-weight: 600; color: #1e293b;" x-text="produk.nama"></div>
                                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;" x-text="produk.kode_produk"></div>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="font-weight: 600; color: #3b82f6;" x-text="'Rp ' + parseFloat(produk.harga).toLocaleString('id-ID')"></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Empty State --}}
                    <div x-show="!searching && searchQuery.length >= 2 && searchResults.length === 0" 
                         style="text-align: center; padding: 3rem; color: #64748b;">
                        <svg style="width: 3rem; height: 3rem; margin: 0 auto; color: #cbd5e1;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        <p style="margin-top: 1rem; font-weight: 500;">Produk tidak ditemukan</p>
                        <p style="margin-top: 0.25rem; font-size: 0.875rem;">Coba kata kunci lain</p>
                    </div>

                    {{-- Initial State --}}
                    <div x-show="!searching && searchQuery.length < 2" 
                         style="text-align: center; padding: 3rem; color: #64748b;">
                        <svg style="width: 3rem; height: 3rem; margin: 0 auto; color: #cbd5e1;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <p style="margin-top: 1rem; font-weight: 500;">Mulai pencarian</p>
                        <p style="margin-top: 0.25rem; font-size: 0.875rem;">Ketik minimal 2 karakter untuk mencari produk</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection