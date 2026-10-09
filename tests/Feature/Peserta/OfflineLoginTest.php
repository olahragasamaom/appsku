<?php

use App\Models\PesertaOffline;
use App\Models\Ujian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

describe('Offline Login (portal)', function () {
    it('displays the login/portal page', function () {
        $response = $this->get(route('peserta.ujian.offline.portal'));

        $response->assertSuccessful();
        $response->assertViewIs('peserta.ujian.offline.portal');
    });

    it('logs in with valid credentials and redirects to exams list', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas', 'status' => 'aktif', 'durasi_ujian' => 90]);
        $plaintext = 'rahasia123';
        $pesertaOffline = PesertaOffline::factory()->create([
            'ujian_id' => $ujian->id,
            'kode_akses' => Hash::make($plaintext),
            'is_active' => true,
        ]);

        $response = $this->post(route('peserta.ujian.offline.login'), [
            'nomor_peserta' => $pesertaOffline->nomor_peserta,
            'kode_akses' => $plaintext,
        ]);

        $response->assertRedirect(route('peserta.offline.exams'));
        $response->assertSessionHas('offline_peserta_id', $pesertaOffline->id);
    });

    it('rejects wrong credentials', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas', 'status' => 'aktif']);
        $pesertaOffline = PesertaOffline::factory()->create([
            'ujian_id' => $ujian->id,
            'kode_akses' => Hash::make('rahasia123'),
        ]);

        $response = $this->post(route('peserta.ujian.offline.login'), [
            'nomor_peserta' => $pesertaOffline->nomor_peserta,
            'kode_akses' => 'salah',
        ]);

        $response->assertSessionHasErrors('kode_akses');
    });
});
