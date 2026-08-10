<?php

use App\Models\LatihanSederhana;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superadmin = User::factory()->create([
        'is_superadmin' => true,
        'company_id' => null,
        'is_active' => true,
    ]);
});

describe('Latihan Sederhana Index', function () {
    it('displays the latihan sederhana list page', function () {
        $this->actingAs($this->superadmin);

        LatihanSederhana::create([
            'judul' => 'Judul Contoh',
            'kode' => 'KD-001',
            'penulis' => 'Budi',
            'keterangan' => 'Keterangan contoh',
        ]);

        $response = $this->get(route('superadmin.latihan-sederhana.index'));

        $response->assertSuccessful();
        $response->assertViewIs('superadmin.latihan-sederhana.index');
        $response->assertSee('Latihan Sederhana');
        $response->assertSee('Judul Contoh');
    });
});

describe('Latihan Sederhana Create', function () {
    it('displays the create form', function () {
        $this->actingAs($this->superadmin);

        $response = $this->get(route('superadmin.latihan-sederhana.create'));

        $response->assertSuccessful();
        $response->assertViewIs('superadmin.latihan-sederhana.create');
        $response->assertSee('Tambah Latihan Sederhana');
    });

    it('can store a new record', function () {
        $this->actingAs($this->superadmin);

        $response = $this->post(route('superadmin.latihan-sederhana.store'), [
            'judul' => 'Belajar Laravel',
            'kode' => 'KD-100',
            'penulis' => 'Andi',
            'keterangan' => 'Modul pembelajaran',
        ]);

        $response->assertRedirect(route('superadmin.latihan-sederhana.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('latihan_sederhana', [
            'judul' => 'Belajar Laravel',
            'kode' => 'KD-100',
            'penulis' => 'Andi',
            'keterangan' => 'Modul pembelajaran',
        ]);
    });

    it('validates required fields on store', function () {
        $this->actingAs($this->superadmin);

        $response = $this->post(route('superadmin.latihan-sederhana.store'), []);

        $response->assertSessionHasErrors(['judul', 'kode', 'penulis']);
    });
});

describe('Latihan Sederhana Update', function () {
    it('can update an existing record', function () {
        $this->actingAs($this->superadmin);

        $item = LatihanSederhana::create([
            'judul' => 'Judul Lama',
            'kode' => 'KD-200',
            'penulis' => 'Citra',
            'keterangan' => 'Lama',
        ]);

        $response = $this->put(route('superadmin.latihan-sederhana.update', $item), [
            'judul' => 'Judul Baru',
            'kode' => 'KD-201',
            'penulis' => 'Citra',
            'keterangan' => 'Baru',
        ]);

        $response->assertRedirect(route('superadmin.latihan-sederhana.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('latihan_sederhana', [
            'id' => $item->id,
            'judul' => 'Judul Baru',
            'kode' => 'KD-201',
        ]);
    });
});

describe('Latihan Sederhana Delete', function () {
    it('can delete a record', function () {
        $this->actingAs($this->superadmin);

        $item = LatihanSederhana::create([
            'judul' => 'Untuk Dihapus',
            'kode' => 'KD-300',
            'penulis' => 'Dedi',
            'keterangan' => 'Hapus',
        ]);

        $response = $this->delete(route('superadmin.latihan-sederhana.destroy', $item));

        $response->assertRedirect(route('superadmin.latihan-sederhana.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('latihan_sederhana', [
            'id' => $item->id,
        ]);
    });
});
