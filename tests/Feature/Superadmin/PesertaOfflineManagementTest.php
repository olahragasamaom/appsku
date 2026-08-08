<?php

use App\Models\Ujian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superadmin = User::factory()->create([
        'is_superadmin' => true,
        'company_id' => null,
        'is_active' => true,
    ]);

    $this->actingAs($this->superadmin);
});

describe('Peserta Offline Index', function () {
    it('displays the peserta offline page', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);

        $response = $this->get(route('superadmin.ujian.peserta-offline.index', $ujian));

        $response->assertSuccessful();
        $response->assertViewIs('superadmin.ujian.peserta-offline.index');
    });
});

describe('Peserta Offline Store', function () {
    it('creates a peserta and flashes the plaintext kode akses once', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);

        $response = $this->post(route('superadmin.ujian.peserta-offline.store', $ujian), [
            'nomor_peserta' => 'P-001',
            'nama_peserta' => 'Budi',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('kode_akses');

        $this->assertDatabaseHas('panritta_peserta_offline', [
            'ujian_id' => $ujian->id,
            'nomor_peserta' => 'P-001',
            'nama_peserta' => 'Budi',
        ]);
    });

    it('stores a hashed kode akses, not the plaintext', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);

        $this->post(route('superadmin.ujian.peserta-offline.store', $ujian), [
            'nomor_peserta' => 'P-002',
            'nama_peserta' => 'Sari',
        ]);

        $plaintext = session('kode_akses');
        $stored = $ujian->pesertaOffline()->where('nomor_peserta', 'P-002')->first()->kode_akses;

        expect($stored)->not->toBe($plaintext);
        expect(Hash::check($plaintext, $stored))->toBeTrue();
    });

    it('rejects a duplicate nomor peserta within the same ujian', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);
        $ujian->pesertaOffline()->create([
            'nomor_peserta' => 'P-001',
            'nama_peserta' => 'Budi',
            'kode_akses' => Hash::make('x'),
        ]);

        $response = $this->post(route('superadmin.ujian.peserta-offline.store', $ujian), [
            'nomor_peserta' => 'P-001',
            'nama_peserta' => 'Andi',
        ]);

        $response->assertSessionHasErrors('nomor_peserta');
    });

    it('rejects creating a peserta on a non-offline exam', function () {
        $ujian = Ujian::factory()->online()->create();

        $response = $this->post(route('superadmin.ujian.peserta-offline.store', $ujian), [
            'nomor_peserta' => 'P-001',
            'nama_peserta' => 'Budi',
        ]);

        $response->assertSessionHasErrors('ujian');
    });
});

describe('Peserta Offline Destroy', function () {
    it('deletes a peserta offline', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);
        $peserta = $ujian->pesertaOffline()->create([
            'nomor_peserta' => 'P-001',
            'nama_peserta' => 'Budi',
            'kode_akses' => Hash::make('x'),
        ]);

        $response = $this->delete(route('superadmin.ujian.peserta-offline.destroy', [$ujian, $peserta]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('panritta_peserta_offline', ['id' => $peserta->id]);
    });
});

describe('Peserta Offline Export', function () {
    it('exports the credential sheet as PDF', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);
        $ujian->pesertaOffline()->create([
            'nomor_peserta' => 'P-001',
            'nama_peserta' => 'Budi',
            'kode_akses' => Hash::make('x'),
        ]);

        $response = $this->get(route('superadmin.ujian.peserta-offline.export', $ujian));

        $response->assertSuccessful();
        expect($response->headers->get('content-type'))->toContain('application/pdf');
    });
});
