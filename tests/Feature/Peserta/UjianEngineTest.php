<?php

use App\Models\JenisUjian;
use App\Models\Soal;
use App\Models\SubIndikator;
use App\Models\SubJenisUjian;
use App\Models\Ujian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function buildUjianWithSoal(array $ujianAttributes = []): array
{
    $admin = User::factory()->create(['is_superadmin' => true, 'company_id' => null]);
    $jenis = JenisUjian::factory()->create(['nama_jenis_ujian' => 'SKD']);

    $ujian = Ujian::factory()->create(array_merge([
        'dibuat_oleh' => $admin->id,
        'status' => 'aktif',
        'durasi_ujian' => 60,
        'token_ujian' => 'ABC123',
    ], $ujianAttributes));

    $ujian->jenisUjians()->attach($jenis->id, ['passing_grade' => 4]);

    $subJenis = SubJenisUjian::factory()->create(['jenis_ujian_id' => $jenis->id, 'sistem_penilaian' => 'benar_salah', 'nilai_benar' => 5]);
    $subIndikator = SubIndikator::factory()->create(['sub_jenis_ujian_id' => $subJenis->id, 'jenis_ujian_id' => $jenis->id]);

    $soal = Soal::factory()->create(['sub_indikator_id' => $subIndikator->id, 'kunci_jawaban' => 'B', 'nilai_bobot_benar' => 5]);
    $ujianSoal = $ujian->ujianSoals()->create(['soal_id' => $soal->id, 'jenis_ujian_id' => $jenis->id, 'urutan' => 1]);

    return compact('admin', 'jenis', 'ujian', 'soal', 'ujianSoal');
}

describe('Peserta Auth', function () {
    it('logs in a peserta by username', function () {
        $peserta = User::factory()->create(['username' => 'peserta1', 'password' => bcrypt('rahasia123'), 'is_peserta' => true]);

        $response = $this->post('/peserta/login', ['username' => 'peserta1', 'password' => 'rahasia123']);

        $response->assertRedirect(route('peserta.dashboard'));
        $this->assertAuthenticatedAs($peserta);
    });

    it('rejects a non peserta account', function () {
        User::factory()->create(['username' => 'bukan', 'password' => bcrypt('rahasia123'), 'is_peserta' => false]);

        $response = $this->post('/peserta/login', ['username' => 'bukan', 'password' => 'rahasia123']);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    });
});

describe('Exam Engine', function () {
    it('requires a valid token for offline ujian', function () {
        ['ujian' => $ujian] = buildUjianWithSoal();
        $peserta = User::factory()->create(['is_peserta' => true]);
        $ujian->peserta()->create(['user_id' => $peserta->id, 'status' => 'terdaftar']);

        $this->actingAs($peserta);

        $wrong = $this->post(route('peserta.ujian.start', $ujian), ['token' => 'WRONG']);
        $wrong->assertSessionHasErrors('token');

        $correct = $this->post(route('peserta.ujian.start', $ujian), ['token' => 'ABC123']);
        $correct->assertRedirect(route('peserta.ujian.kerjakan', $ujian));

        expect($ujian->peserta()->where('user_id', $peserta->id)->first()->status)->toBe('sedang_ujian');
    });

    it('blocks a diblokir peserta from starting', function () {
        ['ujian' => $ujian] = buildUjianWithSoal();
        $peserta = User::factory()->create(['is_peserta' => true]);
        $ujian->peserta()->create(['user_id' => $peserta->id, 'status' => 'diblokir']);

        $this->actingAs($peserta);

        $response = $this->post(route('peserta.ujian.start', $ujian), ['token' => 'ABC123']);
        $response->assertSessionHasErrors('token');
    });

    it('saves an answer and scores it', function () {
        ['ujian' => $ujian, 'ujianSoal' => $ujianSoal] = buildUjianWithSoal();
        $peserta = User::factory()->create(['is_peserta' => true]);
        $up = $ujian->peserta()->create(['user_id' => $peserta->id, 'status' => 'sedang_ujian', 'waktu_mulai' => now()]);

        $this->actingAs($peserta);

        $response = $this->postJson(route('peserta.ujian.jawaban', $ujian), [
            'ujian_soal_id' => $ujianSoal->id,
            'jawaban' => 'B',
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('panritta_ujian_jawaban', [
            'ujian_peserta_id' => $up->id,
            'ujian_soal_id' => $ujianSoal->id,
            'jawaban' => 'B',
            'nilai' => 5,
            'benar' => true,
        ]);
    });

    it('rejects an answer after the batas_waktu snapshot has passed (M-AU-8/AD-10)', function () {
        ['ujian' => $ujian, 'ujianSoal' => $ujianSoal] = buildUjianWithSoal();
        $peserta = User::factory()->create(['is_peserta' => true]);
        $ujian->peserta()->create([
            'user_id' => $peserta->id,
            'status' => 'sedang_ujian',
            'waktu_mulai' => now()->subHour(),
            'batas_waktu' => now()->subMinute(),
        ]);

        $this->actingAs($peserta);

        $response = $this->postJson(route('peserta.ujian.jawaban', $ujian), [
            'ujian_soal_id' => $ujianSoal->id,
            'jawaban' => 'B',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('panritta_ujian_jawaban', [
            'ujian_soal_id' => $ujianSoal->id,
            'jawaban' => 'B',
        ]);
    });

    it('submits and finalizes the attempt', function () {
        ['ujian' => $ujian, 'ujianSoal' => $ujianSoal] = buildUjianWithSoal();
        $peserta = User::factory()->create(['is_peserta' => true]);
        $up = $ujian->peserta()->create(['user_id' => $peserta->id, 'status' => 'sedang_ujian', 'waktu_mulai' => now()]);
        $up->jawaban()->create(['ujian_soal_id' => $ujianSoal->id, 'soal_id' => $ujianSoal->soal_id, 'jenis_ujian_id' => $ujianSoal->jenis_ujian_id, 'jawaban' => 'B', 'nilai' => 5, 'benar' => true]);

        $this->actingAs($peserta);

        $response = $this->post(route('peserta.ujian.submit', $ujian));

        $response->assertRedirect(route('peserta.ujian.hasil', $ujian));
        $up->refresh();
        expect($up->status)->toBe('selesai');
        expect((float) $up->total_nilai)->toBe(5.0);
        expect($up->lulus)->toBeTrue();
    });

    it('allows an online peserta to self-enroll on start', function () {
        ['ujian' => $ujian] = buildUjianWithSoal([
            'tipe_ujian' => 'online_paket',
            'token_ujian' => null,
            'durasi_ujian' => null,
        ]);
        $peserta = User::factory()->create(['is_peserta' => true]);

        $this->actingAs($peserta);

        $response = $this->post(route('peserta.ujian.start', $ujian));

        $response->assertRedirect(route('peserta.ujian.kerjakan', $ujian));
        $this->assertDatabaseHas('panritta_ujian_peserta', [
            'ujian_id' => $ujian->id,
            'user_id' => $peserta->id,
            'status' => 'sedang_ujian',
        ]);
    });
});

describe('Offline Participant Flow', function () {
    it('can view the exam page using session keys', function () {
        ['ujian' => $ujian, 'ujianSoal' => $ujianSoal] = buildUjianWithSoal(['tipe_ujian' => 'offline_kelas']);

        $attempt = $ujian->peserta()->create([
            'user_id' => null,
            'status' => 'sedang_ujian',
            'waktu_mulai' => now(),
        ]);

        $response = $this->withSession([
            'offline_peserta_id' => 1,
            'offline_attempt_id' => $attempt->id,
            'offline_ujian_id' => $ujian->id,
        ])->get(route('peserta.ujian.kerjakan', $ujian));

        $response->assertSuccessful();
        $response->assertViewIs('peserta.ujian.kerjakan');
        $response->assertViewHas('peserta', fn ($p) => $p->id === $attempt->id);
    });

    it('can save an answer using session keys', function () {
        ['ujian' => $ujian, 'ujianSoal' => $ujianSoal] = buildUjianWithSoal(['tipe_ujian' => 'offline_kelas']);

        $attempt = $ujian->peserta()->create([
            'user_id' => null,
            'status' => 'sedang_ujian',
            'waktu_mulai' => now(),
        ]);

        $response = $this->withSession([
            'offline_peserta_id' => 1,
            'offline_attempt_id' => $attempt->id,
            'offline_ujian_id' => $ujian->id,
        ])->postJson(route('peserta.ujian.jawaban', $ujian), [
            'ujian_soal_id' => $ujianSoal->id,
            'jawaban' => 'B',
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('panritta_ujian_jawaban', [
            'ujian_peserta_id' => $attempt->id,
            'ujian_soal_id' => $ujianSoal->id,
            'jawaban' => 'B',
            'nilai' => 5,
            'benar' => true,
        ]);
    });

    it('can submit the exam using session keys', function () {
        ['ujian' => $ujian, 'ujianSoal' => $ujianSoal] = buildUjianWithSoal(['tipe_ujian' => 'offline_kelas']);

        $attempt = $ujian->peserta()->create([
            'user_id' => null,
            'status' => 'sedang_ujian',
            'waktu_mulai' => now(),
        ]);

        $attempt->jawaban()->create([
            'ujian_soal_id' => $ujianSoal->id,
            'soal_id' => $ujianSoal->soal_id,
            'jenis_ujian_id' => $ujianSoal->jenis_ujian_id,
            'jawaban' => 'B',
            'nilai' => 5,
            'benar' => true,
        ]);

        $response = $this->withSession([
            'offline_peserta_id' => 1,
            'offline_attempt_id' => $attempt->id,
            'offline_ujian_id' => $ujian->id,
        ])->post(route('peserta.ujian.submit', $ujian));

        $response->assertRedirect(route('peserta.ujian.hasil', $ujian));
        $attempt->refresh();
        expect($attempt->status)->toBe('selesai');
        expect((float) $attempt->total_nilai)->toBe(5.0);
    });
});

describe('Deadline snapshot (AD-10 / C-AU-6)', function () {
    it('auto-submits when batas_waktu snapshot has passed even if waktu_mulai + durasi has not', function () {
        ['ujian' => $ujian] = buildUjianWithSoal(['tipe_ujian' => 'offline_kelas', 'durasi_ujian' => 120]);

        $attempt = $ujian->peserta()->create([
            'user_id' => null,
            'status' => 'sedang_ujian',
            'waktu_mulai' => now(),
            'batas_waktu' => now()->subMinute(),
        ]);

        $response = $this->withSession([
            'offline_peserta_id' => 1,
            'offline_attempt_id' => $attempt->id,
            'offline_ujian_id' => $ujian->id,
        ])->get(route('peserta.ujian.kerjakan', $ujian));

        $response->assertRedirect(route('peserta.ujian.hasil', $ujian));
        expect($attempt->fresh()->status)->toBe('selesai');
    });

    it('stays in the exam when batas_waktu snapshot is still in the future', function () {
        ['ujian' => $ujian] = buildUjianWithSoal(['tipe_ujian' => 'offline_kelas', 'durasi_ujian' => 1]);

        $attempt = $ujian->peserta()->create([
            'user_id' => null,
            'status' => 'sedang_ujian',
            'waktu_mulai' => now()->subMinutes(10),
            'batas_waktu' => now()->addMinutes(30),
        ]);

        $response = $this->withSession([
            'offline_peserta_id' => 1,
            'offline_attempt_id' => $attempt->id,
            'offline_ujian_id' => $ujian->id,
        ])->get(route('peserta.ujian.kerjakan', $ujian));

        $response->assertSuccessful();
        $response->assertViewIs('peserta.ujian.kerjakan');
        expect($attempt->fresh()->status)->toBe('sedang_ujian');
    });
});
