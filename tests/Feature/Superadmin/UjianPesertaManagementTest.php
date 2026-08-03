<?php

use App\Models\Ujian;
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

    $this->ujian = Ujian::factory()->create(['dibuat_oleh' => $this->superadmin->id]);
});

describe('Ujian Peserta Index', function () {
    it('displays allocated peserta', function () {
        $peserta = User::factory()->create(['name' => 'Peserta Alokasi', 'is_peserta' => true]);
        $this->ujian->peserta()->create(['user_id' => $peserta->id, 'status' => 'terdaftar']);

        $response = $this->get(route('superadmin.ujian.peserta.index', $this->ujian));

        $response->assertSuccessful();
        $response->assertSee('Peserta Alokasi');
    });
});

describe('Available Peserta', function () {
    it('excludes already allocated peserta', function () {
        $allocated = User::factory()->create(['name' => 'Sudah Alokasi', 'is_peserta' => true]);
        $available = User::factory()->create(['name' => 'Belum Alokasi', 'is_peserta' => true]);

        $this->ujian->peserta()->create(['user_id' => $allocated->id, 'status' => 'terdaftar']);

        $response = $this->get(route('superadmin.ujian.peserta.available', $this->ujian));

        $response->assertSuccessful();
        $response->assertSee('Belum Alokasi');
        $response->assertDontSee('Sudah Alokasi');
    });
});

describe('Store Peserta', function () {
    it('allocates selected peserta to the ujian', function () {
        $p1 = User::factory()->create(['is_peserta' => true]);
        $p2 = User::factory()->create(['is_peserta' => true]);

        $response = $this->post(route('superadmin.ujian.peserta.store', $this->ujian), [
            'user_id' => [$p1->id, $p2->id],
        ]);

        $response->assertRedirect(route('superadmin.ujian.peserta.index', $this->ujian));
        expect($this->ujian->peserta()->count())->toBe(2);
    });

    it('does not allocate non peserta users', function () {
        $karyawan = User::factory()->create(['is_peserta' => false]);

        $this->post(route('superadmin.ujian.peserta.store', $this->ujian), [
            'user_id' => [$karyawan->id],
        ]);

        expect($this->ujian->peserta()->count())->toBe(0);
    });

    it('does not duplicate an already allocated peserta', function () {
        $peserta = User::factory()->create(['is_peserta' => true]);
        $this->ujian->peserta()->create(['user_id' => $peserta->id, 'status' => 'terdaftar']);

        $this->post(route('superadmin.ujian.peserta.store', $this->ujian), [
            'user_id' => [$peserta->id],
        ]);

        expect($this->ujian->peserta()->where('user_id', $peserta->id)->count())->toBe(1);
    });
});

describe('Blokir & Reaktivasi', function () {
    it('toggles a peserta between blocked and registered', function () {
        $peserta = User::factory()->create(['is_peserta' => true]);
        $ujianPeserta = $this->ujian->peserta()->create(['user_id' => $peserta->id, 'status' => 'terdaftar']);

        $this->patch(route('superadmin.ujian.peserta.blokir', ['ujian' => $this->ujian, 'peserta' => $ujianPeserta->id]));
        expect($ujianPeserta->fresh()->status)->toBe('diblokir');

        $this->patch(route('superadmin.ujian.peserta.blokir', ['ujian' => $this->ujian, 'peserta' => $ujianPeserta->id]));
        expect($ujianPeserta->fresh()->status)->toBe('terdaftar');
    });
});

describe('Remove Peserta', function () {
    it('removes a peserta from the ujian', function () {
        $peserta = User::factory()->create(['is_peserta' => true]);
        $ujianPeserta = $this->ujian->peserta()->create(['user_id' => $peserta->id, 'status' => 'terdaftar']);

        $response = $this->delete(route('superadmin.ujian.peserta.destroy', ['ujian' => $this->ujian, 'peserta' => $ujianPeserta->id]));

        $response->assertRedirect(route('superadmin.ujian.peserta.index', $this->ujian));
        $this->assertDatabaseMissing('panritta_ujian_peserta', ['id' => $ujianPeserta->id]);
    });
});

describe('Cetak Akun', function () {
    it('exports peserta accounts as pdf', function () {
        $peserta = User::factory()->create(['is_peserta' => true]);
        $this->ujian->peserta()->create(['user_id' => $peserta->id, 'status' => 'terdaftar']);

        $response = $this->get(route('superadmin.ujian.peserta.export.pdf', $this->ujian));

        $response->assertSuccessful();
        expect($response->headers->get('content-type'))->toContain('application/pdf');
    });

    it('exports peserta accounts as excel', function () {
        $peserta = User::factory()->create(['is_peserta' => true]);
        $this->ujian->peserta()->create(['user_id' => $peserta->id, 'status' => 'terdaftar']);

        $response = $this->get(route('superadmin.ujian.peserta.export.excel', $this->ujian));

        $response->assertSuccessful();
    });
});
