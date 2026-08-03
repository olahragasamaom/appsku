<?php

use App\Models\SubIndikator;
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

describe('Sub Indikator Index', function () {
    it('displays sub indikator list page', function () {
        SubIndikator::factory()->count(2)->create();

        $response = $this->get('/superadmin/sub-indikator');

        $response->assertSuccessful();
        $response->assertViewIs('superadmin.sub-indikator.index');
        $response->assertSee('Kelola Sub Indikator');
    });
});

describe('Sub Indikator Create', function () {
    it('can create a sub indikator and denormalizes jenis_ujian_id', function () {
        $subJenis = SubJenisUjian::factory()->create();

        $response = $this->post('/superadmin/sub-indikator', [
            'sub_jenis_ujian_id' => $subJenis->id,
            'nama_sub_indikator' => 'Hukum Perdata',
        ]);

        $response->assertRedirect('/superadmin/sub-indikator');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('panritta_sub_indikator', [
            'sub_jenis_ujian_id' => $subJenis->id,
            'jenis_ujian_id' => $subJenis->jenis_ujian_id,
            'nama_sub_indikator' => 'Hukum Perdata',
        ]);
    });

    it('validates required fields', function () {
        $response = $this->post('/superadmin/sub-indikator', []);

        $response->assertSessionHasErrors(['sub_jenis_ujian_id', 'nama_sub_indikator']);
    });
});

describe('Sub Indikator Update & Delete', function () {
    it('can update a sub indikator', function () {
        $subIndikator = SubIndikator::factory()->create();

        $response = $this->put("/superadmin/sub-indikator/{$subIndikator->id}", [
            'sub_jenis_ujian_id' => $subIndikator->sub_jenis_ujian_id,
            'nama_sub_indikator' => 'Pidana Khusus',
        ]);

        $response->assertRedirect('/superadmin/sub-indikator');
        $this->assertDatabaseHas('panritta_sub_indikator', [
            'id' => $subIndikator->id,
            'nama_sub_indikator' => 'Pidana Khusus',
        ]);
    });

    it('can delete a sub indikator', function () {
        $subIndikator = SubIndikator::factory()->create();

        $response = $this->delete("/superadmin/sub-indikator/{$subIndikator->id}");

        $response->assertRedirect('/superadmin/sub-indikator');
        $this->assertDatabaseMissing('panritta_sub_indikator', ['id' => $subIndikator->id]);
    });
});
