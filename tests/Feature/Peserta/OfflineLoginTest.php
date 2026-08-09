<?php

use App\Models\PesertaOffline;
use App\Models\Ujian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

describe('Offline Login', function () {
    it('displays the login page for an active offline exam', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas', 'status' => 'aktif']);

        $response = $this->get(route('peserta.ujian.offline.login', $ujian));

        $response->assertSuccessful();
        $response->assertViewIs('peserta.ujian.offline.login');
    });

    it('shows a "belum aktif" page for an inactive offline exam', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas', 'status' => 'draft']);

        $response = $this->get(route('peserta.ujian.offline.login', $ujian));

        $response->assertSuccessful();
        $response->assertViewIs('peserta.ujian.offline.belum-aktif');
        $response->assertSee('Ujian Belum Dimulai');
    });

    it('returns 404 for an online exam', function () {
        $ujian = Ujian::factory()->online()->create(['status' => 'aktif']);

        $response = $this->get(route('peserta.ujian.offline.login', $ujian));

        $response->assertNotFound();
    });

    it('logs in with valid credentials and sets session keys', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas', 'status' => 'aktif', 'durasi_ujian' => 90]);
        $plaintext = 'rahasia123';
        $pesertaOffline = PesertaOffline::factory()->create([
            'ujian_id' => $ujian->id,
            'kode_akses' => Hash::make($plaintext),
        ]);

        $response = $this->post(route('peserta.ujian.offline.login', $ujian), [
            'nomor_peserta' => $pesertaOffline->nomor_peserta,
            'kode_akses' => $plaintext,
        ]);

        $response->assertRedirect(route('peserta.ujian.kerjakan', $ujian));

        $pesertaOffline->refresh();
        expect($pesertaOffline->ujian_peserta_id)->not->toBeNull();

        $response->assertSessionHas('offline_peserta_id', $pesertaOffline->id);
        $response->assertSessionHas('offline_ujian_id', $ujian->id);
        $response->assertSessionHas('offline_attempt_id', $pesertaOffline->ujian_peserta_id);
    });

    it('rejects wrong credentials', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas', 'status' => 'aktif']);
        $pesertaOffline = PesertaOffline::factory()->create([
            'ujian_id' => $ujian->id,
            'kode_akses' => Hash::make('rahasia123'),
        ]);

        $response = $this->post(route('peserta.ujian.offline.login', $ujian), [
            'nomor_peserta' => $pesertaOffline->nomor_peserta,
            'kode_akses' => 'salah',
        ]);

        $response->assertSessionHasErrors('kode_akses');
    });
});
