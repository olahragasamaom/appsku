<?php

use App\Models\LatihanDetail;
use App\Models\LatihanKategori;
use App\Models\LatihanProduk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superadmin = User::factory()->create([
        'is_superadmin' => true,
        'company_id' => null,
        'is_active' => true,
    ]);

    $this->kategori = LatihanKategori::create(['nama' => 'Elektronik']);
    $this->produkA = LatihanProduk::create([
        'kode_produk' => 'PRD-0001',
        'nama' => 'Laptop',
        'harga' => 10000000,
    ]);
    $this->produkB = LatihanProduk::create([
        'kode_produk' => 'PRD-0002',
        'nama' => 'Mouse',
        'harga' => 150000,
    ]);
});

describe('Latihan Detail Index', function () {
    it('displays the detail transaction list page', function () {
        $this->actingAs($this->superadmin);

        $response = $this->get(route('superadmin.latihan-detail.index'));

        $response->assertSuccessful();
        $response->assertViewIs('superadmin.latihan-detail.index');
        $response->assertSee('Latihan Detail');
    });
});

describe('Latihan Detail Create', function () {
    it('displays the create form with categories', function () {
        $this->actingAs($this->superadmin);

        $response = $this->get(route('superadmin.latihan-detail.create'));

        $response->assertSuccessful();
        $response->assertViewIs('superadmin.latihan-detail.create');
        $response->assertSee('Elektronik');
    });

    it('can store a transaction with items and auto-calculate totals', function () {
        $this->actingAs($this->superadmin);

        $response = $this->post(route('superadmin.latihan-detail.store'), [
            'nama_transaksi' => 'Pembelian Kantor',
            'latihan_kategori_id' => $this->kategori->id,
            'tanggal' => '2026-08-10',
            'catatan' => 'Catatan uji',
            'items' => [
                ['latihan_produk_id' => $this->produkA->id, 'qty' => 2, 'harga' => 10000000],
                ['latihan_produk_id' => $this->produkB->id, 'qty' => 3, 'harga' => 150000],
            ],
        ]);

        $response->assertRedirect(route('superadmin.latihan-detail.index'));
        $response->assertSessionHas('success');

        // Header tersimpan dengan total = (2*10jt) + (3*150rb) = 20.450.000
        $this->assertDatabaseHas('latihan_detail', [
            'nama_transaksi' => 'Pembelian Kantor',
            'latihan_kategori_id' => $this->kategori->id,
            'total' => 20450000,
        ]);

        $detail = LatihanDetail::first();

        // Nomor auto-generate dengan prefix DET
        expect($detail->nomor)->toStartWith('DET');
        expect($detail->items)->toHaveCount(2);

        // Subtotal per item dihitung otomatis lewat booted() hook
        $this->assertDatabaseHas('latihan_detail_items', [
            'latihan_detail_id' => $detail->id,
            'latihan_produk_id' => $this->produkA->id,
            'qty' => 2,
            'subtotal' => 20000000,
        ]);
    });

    it('validates that at least one item is required', function () {
        $this->actingAs($this->superadmin);

        $response = $this->post(route('superadmin.latihan-detail.store'), [
            'nama_transaksi' => 'Tanpa Item',
            'latihan_kategori_id' => $this->kategori->id,
            'tanggal' => '2026-08-10',
        ]);

        $response->assertSessionHasErrors('items');
    });

    it('validates required header fields', function () {
        $this->actingAs($this->superadmin);

        $response = $this->post(route('superadmin.latihan-detail.store'), []);

        $response->assertSessionHasErrors(['nama_transaksi', 'latihan_kategori_id', 'tanggal', 'items']);
    });
});

describe('Latihan Detail Update', function () {
    it('replaces items and recalculates total on update', function () {
        $this->actingAs($this->superadmin);

        // Buat transaksi awal via endpoint store
        $this->post(route('superadmin.latihan-detail.store'), [
            'nama_transaksi' => 'Transaksi Awal',
            'latihan_kategori_id' => $this->kategori->id,
            'tanggal' => '2026-08-10',
            'items' => [
                ['latihan_produk_id' => $this->produkA->id, 'qty' => 1, 'harga' => 10000000],
            ],
        ]);

        $detail = LatihanDetail::first();

        // Update dengan item baru
        $response = $this->put(route('superadmin.latihan-detail.update', $detail), [
            'nama_transaksi' => 'Transaksi Diubah',
            'latihan_kategori_id' => $this->kategori->id,
            'tanggal' => '2026-08-11',
            'items' => [
                ['latihan_produk_id' => $this->produkB->id, 'qty' => 4, 'harga' => 150000],
            ],
        ]);

        $response->assertRedirect(route('superadmin.latihan-detail.index'));
        $response->assertSessionHas('success');

        // Total baru = 4 * 150.000 = 600.000, item lama diganti
        $this->assertDatabaseHas('latihan_detail', [
            'id' => $detail->id,
            'nama_transaksi' => 'Transaksi Diubah',
            'total' => 600000,
        ]);

        expect($detail->fresh()->items)->toHaveCount(1);

        $this->assertDatabaseMissing('latihan_detail_items', [
            'latihan_detail_id' => $detail->id,
            'latihan_produk_id' => $this->produkA->id,
        ]);
    });
});

describe('Latihan Detail Delete', function () {
    it('cascade deletes items when header is deleted', function () {
        $this->actingAs($this->superadmin);

        $this->post(route('superadmin.latihan-detail.store'), [
            'nama_transaksi' => 'Untuk Dihapus',
            'latihan_kategori_id' => $this->kategori->id,
            'tanggal' => '2026-08-10',
            'items' => [
                ['latihan_produk_id' => $this->produkA->id, 'qty' => 1, 'harga' => 10000000],
            ],
        ]);

        $detail = LatihanDetail::first();

        $response = $this->delete(route('superadmin.latihan-detail.destroy', $detail));

        $response->assertRedirect(route('superadmin.latihan-detail.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('latihan_detail', ['id' => $detail->id]);
        $this->assertDatabaseMissing('latihan_detail_items', ['latihan_detail_id' => $detail->id]);
    });
});

describe('Latihan Detail Search Produk (Modal Picker API)', function () {
    it('returns matching products as json by name', function () {
        $this->actingAs($this->superadmin);

        $response = $this->getJson(route('superadmin.latihan-detail.search-produk', ['q' => 'Laptop']));

        $response->assertSuccessful();
        $response->assertJsonFragment(['kode_produk' => 'PRD-0001']);
        $response->assertJsonMissing(['kode_produk' => 'PRD-0002']);
    });

    it('returns matching products as json by kode produk', function () {
        $this->actingAs($this->superadmin);

        $response = $this->getJson(route('superadmin.latihan-detail.search-produk', ['q' => 'PRD-0002']));

        $response->assertSuccessful();
        $response->assertJsonFragment(['kode_produk' => 'PRD-0002']);
    });
});
