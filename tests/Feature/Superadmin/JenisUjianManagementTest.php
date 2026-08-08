<?php

use App\Models\JenisUjian;
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

describe('Jenis Ujian Index', function () {
    it('displays jenis ujian list page', function () {
        $this->actingAs($this->superadmin);

        JenisUjian::factory()->count(3)->create();

        $response = $this->get('/superadmin/jenis-ujian');

        $response->assertSuccessful();
        $response->assertViewIs('superadmin.jenis-ujian.index');
        $response->assertSee('Kelola Jenis Ujian');
    });

    it('shows existing jenis ujian in the list', function () {
        $this->actingAs($this->superadmin);

        JenisUjian::factory()->create([
            'nama_jenis_ujian' => 'Ujian Kompetensi',
        ]);

        $response = $this->get('/superadmin/jenis-ujian');

        $response->assertSuccessful();
        $response->assertSee('Ujian Kompetensi');
    });
});

describe('Jenis Ujian Create', function () {
    it('displays the modal form on the index page', function () {
        $this->actingAs($this->superadmin);

        $response = $this->get('/superadmin/jenis-ujian');

        $response->assertSuccessful();
        $response->assertSee('Tambah Jenis Ujian');
        $response->assertSee('nama_jenis_ujian');
    });

    it('can create a new jenis ujian', function () {
        $this->actingAs($this->superadmin);

        $response = $this->post('/superadmin/jenis-ujian', [
            'nama_jenis_ujian' => 'Ujian Praktik',
            'keterangan' => 'Keterangan ujian praktik',
        ]);

        $response->assertRedirect('/superadmin/jenis-ujian');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('panritta_jenis_ujian', [
            'nama_jenis_ujian' => 'Ujian Praktik',
            'keterangan' => 'Keterangan ujian praktik',
        ]);
    });

    it('validates required fields on create', function () {
        $this->actingAs($this->superadmin);

        $response = $this->post('/superadmin/jenis-ujian', []);

        $response->assertSessionHasErrors('nama_jenis_ujian');
    });

    it('validates unique nama jenis ujian', function () {
        $this->actingAs($this->superadmin);

        JenisUjian::factory()->create([
            'nama_jenis_ujian' => 'Ujian Nasional',
        ]);

        $response = $this->post('/superadmin/jenis-ujian', [
            'nama_jenis_ujian' => 'Ujian Nasional',
        ]);

        $response->assertSessionHasErrors('nama_jenis_ujian');
    });
});

describe('Jenis Ujian Edit', function () {
    it('provides edit data to the modal on the index page', function () {
        $this->actingAs($this->superadmin);

        $jenisUjian = JenisUjian::factory()->create([
            'nama_jenis_ujian' => 'Ujian Lama',
        ]);

        $response = $this->get('/superadmin/jenis-ujian');

        $response->assertSuccessful();
        $response->assertSee('Ujian Lama');
        $response->assertSee(route('superadmin.jenis-ujian.update', $jenisUjian), false);
    });

    it('can update a jenis ujian', function () {
        $this->actingAs($this->superadmin);

        $jenisUjian = JenisUjian::factory()->create([
            'nama_jenis_ujian' => 'Ujian Lama',
        ]);

        $response = $this->put("/superadmin/jenis-ujian/{$jenisUjian->id}", [
            'nama_jenis_ujian' => 'Ujian Baru',
            'keterangan' => 'Keterangan baru',
        ]);

        $response->assertRedirect('/superadmin/jenis-ujian');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('panritta_jenis_ujian', [
            'id' => $jenisUjian->id,
            'nama_jenis_ujian' => 'Ujian Baru',
            'keterangan' => 'Keterangan baru',
        ]);
    });
});

describe('Jenis Ujian Delete', function () {
    it('can delete a jenis ujian', function () {
        $this->actingAs($this->superadmin);

        $jenisUjian = JenisUjian::factory()->create();

        $response = $this->delete("/superadmin/jenis-ujian/{$jenisUjian->id}");

        $response->assertRedirect('/superadmin/jenis-ujian');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('panritta_jenis_ujian', [
            'id' => $jenisUjian->id,
        ]);
    });
});
