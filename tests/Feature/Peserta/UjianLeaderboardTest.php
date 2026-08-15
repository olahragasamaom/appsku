<?php

use App\Models\JenisUjian;
use App\Models\Soal;
use App\Models\SubIndikator;
use App\Models\SubJenisUjian;
use App\Models\Ujian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function buildLeaderboardUjian(array $ujianAttributes = []): array
{
    $admin = User::factory()->create(['is_superadmin' => true, 'company_id' => null]);
    $jenis = JenisUjian::factory()->create(['nama_jenis_ujian' => 'SKD']);

    $ujian = Ujian::factory()->create(array_merge([
        'dibuat_oleh' => $admin->id,
        'status' => 'aktif',
        'durasi_ujian' => null,
        'tampilkan_hasil' => true,
        'tipe_ujian' => 'online_paket',
    ], $ujianAttributes));

    $ujian->jenisUjians()->attach($jenis->id, ['passing_grade' => 4]);

    $subJenis = SubJenisUjian::factory()->create(['jenis_ujian_id' => $jenis->id, 'sistem_penilaian' => 'benar_salah', 'nilai_benar' => 5]);
    $subIndikator = SubIndikator::factory()->create(['sub_jenis_ujian_id' => $subJenis->id, 'jenis_ujian_id' => $jenis->id]);
    $soal = Soal::factory()->create(['sub_indikator_id' => $subIndikator->id, 'kunci_jawaban' => 'B', 'nilai_bobot_benar' => 5]);
    $ujianSoal = $ujian->ujianSoals()->create(['soal_id' => $soal->id, 'jenis_ujian_id' => $jenis->id, 'urutan' => 1]);

    return compact('admin', 'jenis', 'ujian', 'soal', 'ujianSoal');
}

describe('Peserta Leaderboard', function () {
    it('shows the leaderboard with the peserta ranked and highlighted', function () {
        ['ujian' => $ujian] = buildLeaderboardUjian();

        $me = User::factory()->create(['is_peserta' => true, 'name' => 'Budi Peserta']);
        $rival = User::factory()->create(['is_peserta' => true, 'name' => 'Ani Juara']);

        $ujian->peserta()->create(['user_id' => $rival->id, 'status' => 'selesai', 'total_nilai' => 90, 'waktu_selesai' => now()->subMinutes(5)]);
        $myAttempt = $ujian->peserta()->create(['user_id' => $me->id, 'status' => 'selesai', 'total_nilai' => 50, 'waktu_selesai' => now()]);

        $response = $this->actingAs($me)->get(route('peserta.ujian.leaderboard', $ujian));

        $response->assertSuccessful();
        $response->assertViewIs('peserta.ujian.leaderboard');
        $response->assertViewHas('posisi', fn ($p) => $p['rank'] === 2 && $p['total'] === 2);
        $response->assertSee('Budi Peserta');
        $response->assertSee('Ani Juara');
    });

    it('redirects to hasil when tampilkan_hasil is false', function () {
        ['ujian' => $ujian] = buildLeaderboardUjian(['tampilkan_hasil' => false]);

        $me = User::factory()->create(['is_peserta' => true]);
        $ujian->peserta()->create(['user_id' => $me->id, 'status' => 'selesai', 'total_nilai' => 50, 'waktu_selesai' => now()]);

        $response = $this->actingAs($me)->get(route('peserta.ujian.leaderboard', $ujian));

        $response->assertRedirect(route('peserta.ujian.hasil', $ujian));
    });

    it('404s when the peserta has not finished', function () {
        ['ujian' => $ujian] = buildLeaderboardUjian();

        $me = User::factory()->create(['is_peserta' => true]);
        $ujian->peserta()->create(['user_id' => $me->id, 'status' => 'sedang_ujian']);

        $response = $this->actingAs($me)->get(route('peserta.ujian.leaderboard', $ujian));

        $response->assertNotFound();
    });
});
