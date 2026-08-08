<?php

use App\Models\JenisUjian;
use App\Models\SubJenisUjian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superadmin = User::factory()->create([
        'is_superadmin' => true,
        'company_id' => null,
        'is_active' => true,
    ]);
    $this->actingAs($this->superadmin);
});

describe('Sub Jenis Ujian Index', function () {
    it('displays sub jenis ujian list page', function () {
        SubJenisUjian::factory()->count(3)->create();

        $response = $this->get('/superadmin/sub-jenis-ujian');

        $response->assertSuccessful();
        $response->assertViewIs('superadmin.sub-jenis-ujian.index');
        $response->assertSee('Kelola Sub Jenis Ujian');
    });

    it('groups sub jenis ujian under their jenis ujian ordered by urutan', function () {
        $jenisUjian = JenisUjian::factory()->create(['nama_jenis_ujian' => 'Ujian Kedinasan']);

        SubJenisUjian::factory()->create([
            'jenis_ujian_id' => $jenisUjian->id,
            'nama_sub_jenis_ujian' => 'TKP',
            'urutan' => 2,
        ]);
        SubJenisUjian::factory()->create([
            'jenis_ujian_id' => $jenisUjian->id,
            'nama_sub_jenis_ujian' => 'TIU',
            'urutan' => 1,
        ]);

        $response = $this->get('/superadmin/sub-jenis-ujian');

        $response->assertSuccessful();
        $response->assertSee('Ujian Kedinasan');
        $response->assertSeeInOrder(['Ujian Kedinasan', 'TIU', 'TKP']);
    });
});

describe('Sub Jenis Ujian Create', function () {
    it('can create a new sub jenis ujian', function () {
        $jenisUjian = JenisUjian::factory()->create();

        $response = $this->post('/superadmin/sub-jenis-ujian', [
            'jenis_ujian_id' => $jenisUjian->id,
            'nama_sub_jenis_ujian' => 'Psikotes',
            'keterangan' => 'Tes psikologi calon peserta',
            'urutan' => 2,
            'sistem_penilaian' => 'tiap_jawaban_ada_poin',
            'jumlah_jawaban_pilihan_ganda' => 5,
            'nilai_benar' => 5,
        ]);

        $response->assertRedirect('/superadmin/sub-jenis-ujian');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('panritta_sub_jenis_ujian', [
            'nama_sub_jenis_ujian' => 'Psikotes',
            'keterangan' => 'Tes psikologi calon peserta',
            'urutan' => 2,
            'sistem_penilaian' => 'tiap_jawaban_ada_poin',
        ]);
    });

    it('validates required fields', function () {
        $response = $this->post('/superadmin/sub-jenis-ujian', []);

        $response->assertSessionHasErrors(['jenis_ujian_id', 'nama_sub_jenis_ujian', 'sistem_penilaian']);
    });

    it('rejects invalid sistem penilaian', function () {
        $jenisUjian = JenisUjian::factory()->create();

        $response = $this->post('/superadmin/sub-jenis-ujian', [
            'jenis_ujian_id' => $jenisUjian->id,
            'nama_sub_jenis_ujian' => 'Test',
            'sistem_penilaian' => 'invalid',
            'jumlah_jawaban_pilihan_ganda' => 5,
            'nilai_benar' => 5,
        ]);

        $response->assertSessionHasErrors('sistem_penilaian');
    });
});

describe('Sub Jenis Ujian Update & Delete', function () {
    it('can update a sub jenis ujian', function () {
        $subJenis = SubJenisUjian::factory()->create();

        $response = $this->put("/superadmin/sub-jenis-ujian/{$subJenis->id}", [
            'jenis_ujian_id' => $subJenis->jenis_ujian_id,
            'nama_sub_jenis_ujian' => 'Nama Baru',
            'sistem_penilaian' => 'benar_salah',
            'jumlah_jawaban_pilihan_ganda' => 4,
            'nilai_benar' => 10,
        ]);

        $response->assertRedirect('/superadmin/sub-jenis-ujian');
        $this->assertDatabaseHas('panritta_sub_jenis_ujian', [
            'id' => $subJenis->id,
            'nama_sub_jenis_ujian' => 'Nama Baru',
            'jumlah_jawaban_pilihan_ganda' => 4,
        ]);
    });

    it('can delete a sub jenis ujian', function () {
        $subJenis = SubJenisUjian::factory()->create();

        $response = $this->delete("/superadmin/sub-jenis-ujian/{$subJenis->id}");

        $response->assertRedirect('/superadmin/sub-jenis-ujian');
        $this->assertDatabaseMissing('panritta_sub_jenis_ujian', ['id' => $subJenis->id]);
    });
});
