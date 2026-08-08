<?php

use App\Models\JenisUjian;
use App\Models\Soal;
use App\Models\SubIndikator;
use App\Models\SubJenisUjian;
use App\Models\Ujian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function buildPembahasanUjian(array $ujianAttributes = []): array
{
    $admin = User::factory()->create(['is_superadmin' => true, 'company_id' => null]);
    $jenis = JenisUjian::factory()->create(['nama_jenis_ujian' => 'SKD']);

    $ujian = Ujian::factory()->create(array_merge([
        'dibuat_oleh' => $admin->id,
        'status' => 'aktif',
        'durasi_ujian' => 60,
        'tampilkan_hasil' => true,
        'tipe_ujian' => 'offline_kelas',
    ], $ujianAttributes));

    $ujian->jenisUjians()->attach($jenis->id, ['passing_grade' => 4]);

    $subJenis = SubJenisUjian::factory()->create(['jenis_ujian_id' => $jenis->id, 'sistem_penilaian' => 'benar_salah', 'nilai_benar' => 5]);
    $subIndikator = SubIndikator::factory()->create(['sub_jenis_ujian_id' => $subJenis->id, 'jenis_ujian_id' => $jenis->id]);

    $soalBenar = Soal::factory()->create(['sub_indikator_id' => $subIndikator->id, 'kunci_jawaban' => 'B', 'nilai_bobot_benar' => 5]);
    $soalSalah = Soal::factory()->create(['sub_indikator_id' => $subIndikator->id, 'kunci_jawaban' => 'A', 'nilai_bobot_benar' => 5]);

    $usBenar = $ujian->ujianSoals()->create(['soal_id' => $soalBenar->id, 'jenis_ujian_id' => $jenis->id, 'urutan' => 1]);
    $usSalah = $ujian->ujianSoals()->create(['soal_id' => $soalSalah->id, 'jenis_ujian_id' => $jenis->id, 'urutan' => 2]);

    return compact('admin', 'jenis', 'ujian', 'soalBenar', 'soalSalah', 'usBenar', 'usSalah');
}

function finishedAttempt(Ujian $ujian, array $usAnswers): \App\Models\UjianPeserta
{
    $attempt = $ujian->peserta()->create([
        'user_id' => null,
        'status' => 'selesai',
        'waktu_mulai' => now()->subHour(),
        'waktu_selesai' => now(),
    ]);

    foreach ($usAnswers as $ujianSoal => $data) {
        $attempt->jawaban()->create(array_merge([
            'ujian_soal_id' => $data['ujian_soal']->id,
            'soal_id' => $data['ujian_soal']->soal_id,
            'jenis_ujian_id' => $data['ujian_soal']->jenis_ujian_id,
        ], $data['values']));
    }

    return $attempt;
}

describe('Peserta Pembahasan (offline)', function () {
    it('shows the pembahasan page for a finished offline attempt', function () {
        ['ujian' => $ujian, 'usBenar' => $usBenar, 'usSalah' => $usSalah] = buildPembahasanUjian();

        $attempt = finishedAttempt($ujian, [
            ['ujian_soal' => $usBenar, 'values' => ['jawaban' => 'B', 'nilai' => 5, 'benar' => true]],
            ['ujian_soal' => $usSalah, 'values' => ['jawaban' => 'C', 'nilai' => 0, 'benar' => false]],
        ]);

        $response = $this->withSession([
            'offline_peserta_id' => 1,
            'offline_attempt_id' => $attempt->id,
            'offline_ujian_id' => $ujian->id,
        ])->get(route('peserta.ujian.pembahasan', $ujian));

        $response->assertSuccessful();
        $response->assertViewIs('peserta.ujian.pembahasan');
        $response->assertViewHas('peserta', fn ($p) => $p->id === $attempt->id);
        $response->assertViewHas('ujianSoals', fn ($items) => $items->count() === 2);
    });

    it('exposes correct benar/salah state and expected key to the view', function () {
        ['ujian' => $ujian, 'usBenar' => $usBenar, 'usSalah' => $usSalah, 'soalSalah' => $soalSalah] = buildPembahasanUjian();

        $attempt = finishedAttempt($ujian, [
            ['ujian_soal' => $usBenar, 'values' => ['jawaban' => 'B', 'nilai' => 5, 'benar' => true]],
            ['ujian_soal' => $usSalah, 'values' => ['jawaban' => 'C', 'nilai' => 0, 'benar' => false]],
        ]);

        $response = $this->withSession([
            'offline_peserta_id' => 1,
            'offline_attempt_id' => $attempt->id,
            'offline_ujian_id' => $ujian->id,
        ])->get(route('peserta.ujian.pembahasan', $ujian));

        $response->assertSee('B'); // participant's correct answer
        $response->assertSee($soalSalah->kunci_jawaban); // expected key 'A' for the wrong one
    });

    it('redirects to hasil when tampilkan_hasil is false', function () {
        ['ujian' => $ujian, 'usBenar' => $usBenar] = buildPembahasanUjian(['tampilkan_hasil' => false]);

        $attempt = finishedAttempt($ujian, [
            ['ujian_soal' => $usBenar, 'values' => ['jawaban' => 'B', 'nilai' => 5, 'benar' => true]],
        ]);

        $response = $this->withSession([
            'offline_peserta_id' => 1,
            'offline_attempt_id' => $attempt->id,
            'offline_ujian_id' => $ujian->id,
        ])->get(route('peserta.ujian.pembahasan', $ujian));

        $response->assertRedirect(route('peserta.ujian.hasil', $ujian));
    });

    it('404s when the attempt is not yet selesai', function () {
        ['ujian' => $ujian] = buildPembahasanUjian();

        $attempt = $ujian->peserta()->create([
            'user_id' => null,
            'status' => 'sedang_ujian',
            'waktu_mulai' => now(),
        ]);

        $response = $this->withSession([
            'offline_peserta_id' => 1,
            'offline_attempt_id' => $attempt->id,
            'offline_ujian_id' => $ujian->id,
        ])->get(route('peserta.ujian.pembahasan', $ujian));

        $response->assertNotFound();
    });
});

describe('Peserta Pembahasan (online)', function () {
    it('shows the pembahasan page for the authenticated peserta', function () {
        ['ujian' => $ujian, 'usBenar' => $usBenar, 'usSalah' => $usSalah] = buildPembahasanUjian(['tipe_ujian' => 'online_paket', 'durasi_ujian' => null]);
        $user = User::factory()->create(['is_peserta' => true]);

        $attempt = $ujian->peserta()->create([
            'user_id' => $user->id,
            'status' => 'selesai',
            'waktu_selesai' => now(),
        ]);
        $attempt->jawaban()->create(['ujian_soal_id' => $usBenar->id, 'soal_id' => $usBenar->soal_id, 'jenis_ujian_id' => $usBenar->jenis_ujian_id, 'jawaban' => 'B', 'nilai' => 5, 'benar' => true]);

        $response = $this->actingAs($user)->get(route('peserta.ujian.pembahasan', $ujian));

        $response->assertSuccessful();
        $response->assertViewIs('peserta.ujian.pembahasan');
    });
});
