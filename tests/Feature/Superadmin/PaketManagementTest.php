<?php

use App\Models\Paket;
use App\Models\User;

beforeEach(function () {
    $this->superadmin = User::factory()->create([
        'is_superadmin' => true,
        'company_id' => null,
        'is_active' => true,
    ]);

    $this->actingAs($this->superadmin);
});

describe('PaketRequest — kuota_ujian validation (M-AU-4)', function () {
    it('accepts null kuota_ujian as unlimited', function () {
        $response = $this->post('/superadmin/paket', [
            'nama_paket' => 'Paket Unlimited',
            'harga' => 0,
            'durasi_hari' => 30,
            'kuota_ujian' => null,
        ]);

        $response->assertSessionDoesntHaveErrors('kuota_ujian');
    });

    it('accepts a positive integer kuota_ujian', function () {
        $response = $this->post('/superadmin/paket', [
            'nama_paket' => 'Paket Terbatas',
            'harga' => 0,
            'durasi_hari' => 30,
            'kuota_ujian' => 5,
        ]);

        $response->assertSessionDoesntHaveErrors('kuota_ujian');
    });

    it('rejects kuota_ujian of zero', function () {
        $response = $this->post('/superadmin/paket', [
            'nama_paket' => 'Paket Nol',
            'harga' => 0,
            'durasi_hari' => 30,
            'kuota_ujian' => 0,
        ]);

        $response->assertSessionHasErrors('kuota_ujian');
    });

    it('rejects a negative kuota_ujian', function () {
        $response = $this->post('/superadmin/paket', [
            'nama_paket' => 'Paket Negatif',
            'harga' => 0,
            'durasi_hari' => 30,
            'kuota_ujian' => -1,
        ]);

        $response->assertSessionHasErrors('kuota_ujian');
    });
});

describe('Paket views render', function () {
    it('renders the create page with the form', function () {
        $response = $this->get(route('superadmin.paket.create'));

        $response->assertSuccessful();
        $response->assertViewIs('superadmin.paket.create');
        $response->assertSee('Tambah Paket Member');
        $response->assertSee('name="nama_paket"', false);
        $response->assertSee('name="harga"', false);
        $response->assertSee('Simpan Paket');
    });

    it('renders the edit page prefilled with paket data', function () {
        $paket = Paket::create([
            'nama_paket' => 'Paket Premium',
            'slug' => 'paket-premium',
            'harga' => 150000,
            'durasi_hari' => 90,
            'kuota_ujian' => 10,
            'urutan' => 1,
            'is_active' => true,
        ]);

        $response = $this->get(route('superadmin.paket.edit', $paket));

        $response->assertSuccessful();
        $response->assertViewIs('superadmin.paket.edit');
        $response->assertSee('Edit Paket Member');
        $response->assertSee('Paket Premium');
        $response->assertSee('Perbarui Paket');
    });
});
