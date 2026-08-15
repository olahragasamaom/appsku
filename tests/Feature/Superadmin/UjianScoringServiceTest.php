<?php

use App\Models\JenisUjian;
use App\Models\Soal;
use App\Models\SubIndikator;
use App\Models\SubJenisUjian;
use App\Models\Ujian;
use App\Models\User;
use App\Services\Ujian\UjianScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeSoal(JenisUjian $jenis, string $sistem, array $attributes = []): Soal
{
    $subJenis = SubJenisUjian::factory()->create([
        'jenis_ujian_id' => $jenis->id,
        'sistem_penilaian' => $sistem,
        'nilai_benar' => 5,
    ]);
    $subIndikator = SubIndikator::factory()->create([
        'sub_jenis_ujian_id' => $subJenis->id,
        'jenis_ujian_id' => $jenis->id,
    ]);

    return Soal::factory()->create(array_merge(['sub_indikator_id' => $subIndikator->id], $attributes));
}

describe('scoreAnswer', function () {
    it('scores a benar_salah answer correctly', function () {
        $jenis = JenisUjian::factory()->create();
        $soal = makeSoal($jenis, 'benar_salah', ['kunci_jawaban' => 'B', 'nilai_bobot_benar' => 4]);
        $soal->load('subIndikator.subJenisUjian');

        $service = app(UjianScoringService::class);

        expect($service->scoreAnswer($soal, 'B'))->toMatchArray(['nilai' => 4.0, 'benar' => true]);
        expect($service->scoreAnswer($soal, 'A'))->toMatchArray(['nilai' => 0.0, 'benar' => false]);
    });

    it('falls back to sub jenis nilai_benar when soal has none', function () {
        $jenis = JenisUjian::factory()->create();
        $soal = makeSoal($jenis, 'benar_salah', ['kunci_jawaban' => 'C', 'nilai_bobot_benar' => null]);
        $soal->load('subIndikator.subJenisUjian');

        expect(app(UjianScoringService::class)->scoreAnswer($soal, 'C')['nilai'])->toBe(5.0);
    });

    it('scores a poin-based answer using the chosen option weight', function () {
        $jenis = JenisUjian::factory()->create();
        $soal = makeSoal($jenis, 'tiap_jawaban_ada_poin', [
            'kunci_jawaban' => null,
            'nilai_bobot_a' => 1, 'nilai_bobot_b' => 2, 'nilai_bobot_c' => 3, 'nilai_bobot_d' => 4, 'nilai_bobot_e' => 5,
        ]);
        $soal->load('subIndikator.subJenisUjian');

        expect(app(UjianScoringService::class)->scoreAnswer($soal, 'C'))->toMatchArray(['nilai' => 3.0, 'benar' => null]);
    });
});

describe('finalize', function () {
    it('sums scores and marks lulus when all passing grades are met', function () {
        $skd = JenisUjian::factory()->create();
        $superadmin = User::factory()->create(['is_superadmin' => true, 'company_id' => null]);
        $ujian = Ujian::factory()->create(['dibuat_oleh' => $superadmin->id]);
        $ujian->jenisUjians()->attach($skd->id, ['passing_grade' => 8]);

        $soal1 = makeSoal($skd, 'benar_salah', ['kunci_jawaban' => 'A', 'nilai_bobot_benar' => 5]);
        $soal2 = makeSoal($skd, 'benar_salah', ['kunci_jawaban' => 'B', 'nilai_bobot_benar' => 5]);
        $us1 = $ujian->ujianSoals()->create(['soal_id' => $soal1->id, 'jenis_ujian_id' => $skd->id, 'urutan' => 1]);
        $us2 = $ujian->ujianSoals()->create(['soal_id' => $soal2->id, 'jenis_ujian_id' => $skd->id, 'urutan' => 2]);

        $peserta = User::factory()->create(['is_peserta' => true]);
        $up = $ujian->peserta()->create(['user_id' => $peserta->id, 'status' => 'sedang_ujian']);

        $up->jawaban()->create(['ujian_soal_id' => $us1->id, 'soal_id' => $soal1->id, 'jenis_ujian_id' => $skd->id, 'jawaban' => 'A', 'nilai' => 5, 'benar' => true]);
        $up->jawaban()->create(['ujian_soal_id' => $us2->id, 'soal_id' => $soal2->id, 'jenis_ujian_id' => $skd->id, 'jawaban' => 'B', 'nilai' => 5, 'benar' => true]);

        app(UjianScoringService::class)->finalize($up);

        $up->refresh();
        expect((float) $up->total_nilai)->toBe(10.0);
        expect($up->lulus)->toBeTrue();
        expect($up->status)->toBe('selesai');
    });

    it('marks tidak lulus when a jenis is below passing grade', function () {
        $skd = JenisUjian::factory()->create();
        $superadmin = User::factory()->create(['is_superadmin' => true, 'company_id' => null]);
        $ujian = Ujian::factory()->create(['dibuat_oleh' => $superadmin->id]);
        $ujian->jenisUjians()->attach($skd->id, ['passing_grade' => 20]);

        $soal = makeSoal($skd, 'benar_salah', ['kunci_jawaban' => 'A', 'nilai_bobot_benar' => 5]);
        $us = $ujian->ujianSoals()->create(['soal_id' => $soal->id, 'jenis_ujian_id' => $skd->id, 'urutan' => 1]);

        $peserta = User::factory()->create(['is_peserta' => true]);
        $up = $ujian->peserta()->create(['user_id' => $peserta->id, 'status' => 'sedang_ujian']);
        $up->jawaban()->create(['ujian_soal_id' => $us->id, 'soal_id' => $soal->id, 'jenis_ujian_id' => $skd->id, 'jawaban' => 'A', 'nilai' => 5, 'benar' => true]);

        app(UjianScoringService::class)->finalize($up);

        expect($up->fresh()->lulus)->toBeFalse();
    });
});

describe('aggregateCategories', function () {
    it('persists a category row per jenis ujian', function () {
        $twk = JenisUjian::factory()->create();
        $tiu = JenisUjian::factory()->create();
        $superadmin = User::factory()->create(['is_superadmin' => true, 'company_id' => null]);
        $ujian = Ujian::factory()->create(['dibuat_oleh' => $superadmin->id]);
        $ujian->jenisUjians()->attach($twk->id, ['passing_grade' => 5]);
        $ujian->jenisUjians()->attach($tiu->id, ['passing_grade' => 5]);

        $soalTwk = makeSoal($twk, 'benar_salah', ['kunci_jawaban' => 'A', 'nilai_bobot_benar' => 5]);
        $soalTiu = makeSoal($tiu, 'benar_salah', ['kunci_jawaban' => 'B', 'nilai_bobot_benar' => 5]);
        $usTwk = $ujian->ujianSoals()->create(['soal_id' => $soalTwk->id, 'jenis_ujian_id' => $twk->id, 'urutan' => 1]);
        $usTiu = $ujian->ujianSoals()->create(['soal_id' => $soalTiu->id, 'jenis_ujian_id' => $tiu->id, 'urutan' => 2]);

        $peserta = User::factory()->create(['is_peserta' => true]);
        $up = $ujian->peserta()->create(['user_id' => $peserta->id, 'status' => 'sedang_ujian']);
        $up->jawaban()->create(['ujian_soal_id' => $usTwk->id, 'soal_id' => $soalTwk->id, 'jenis_ujian_id' => $twk->id, 'jawaban' => 'A', 'nilai' => 5, 'benar' => true]);
        $up->jawaban()->create(['ujian_soal_id' => $usTiu->id, 'soal_id' => $soalTiu->id, 'jenis_ujian_id' => $tiu->id, 'jawaban' => 'B', 'nilai' => 5, 'benar' => true]);

        app(UjianScoringService::class)->aggregateCategories($up);

        expect($up->kategori()->count())->toBe(2);
        expect((float) $up->kategori()->where('jenis_ujian_id', $twk->id)->value('nilai_kategori'))->toBe(5.0);
    });

    it('is idempotent when re-scoring the same attempt', function () {
        $skd = JenisUjian::factory()->create();
        $superadmin = User::factory()->create(['is_superadmin' => true, 'company_id' => null]);
        $ujian = Ujian::factory()->create(['dibuat_oleh' => $superadmin->id]);
        $ujian->jenisUjians()->attach($skd->id, ['passing_grade' => 5]);

        $soal = makeSoal($skd, 'benar_salah', ['kunci_jawaban' => 'A', 'nilai_bobot_benar' => 5]);
        $us = $ujian->ujianSoals()->create(['soal_id' => $soal->id, 'jenis_ujian_id' => $skd->id, 'urutan' => 1]);

        $peserta = User::factory()->create(['is_peserta' => true]);
        $up = $ujian->peserta()->create(['user_id' => $peserta->id, 'status' => 'sedang_ujian']);
        $up->jawaban()->create(['ujian_soal_id' => $us->id, 'soal_id' => $soal->id, 'jenis_ujian_id' => $skd->id, 'jawaban' => 'A', 'nilai' => 5, 'benar' => true]);

        $service = app(UjianScoringService::class);
        $service->aggregateCategories($up);
        $service->aggregateCategories($up);

        expect($up->kategori()->count())->toBe(1);
    });
});

describe('evaluatePass', function () {
    it('passes only when every category meets its passing grade (AD-4)', function () {
        $twk = JenisUjian::factory()->create();
        $tiu = JenisUjian::factory()->create();
        $superadmin = User::factory()->create(['is_superadmin' => true, 'company_id' => null]);
        $ujian = Ujian::factory()->create(['dibuat_oleh' => $superadmin->id]);
        $ujian->jenisUjians()->attach($twk->id, ['passing_grade' => 5]);
        $ujian->jenisUjians()->attach($tiu->id, ['passing_grade' => 5]);

        $soalTwk = makeSoal($twk, 'benar_salah', ['kunci_jawaban' => 'A', 'nilai_bobot_benar' => 5]);
        $soalTiu = makeSoal($tiu, 'benar_salah', ['kunci_jawaban' => 'B', 'nilai_bobot_benar' => 5]);
        $usTwk = $ujian->ujianSoals()->create(['soal_id' => $soalTwk->id, 'jenis_ujian_id' => $twk->id, 'urutan' => 1]);
        $usTiu = $ujian->ujianSoals()->create(['soal_id' => $soalTiu->id, 'jenis_ujian_id' => $tiu->id, 'urutan' => 2]);

        $peserta = User::factory()->create(['is_peserta' => true]);
        $up = $ujian->peserta()->create(['user_id' => $peserta->id, 'status' => 'sedang_ujian']);
        $up->jawaban()->create(['ujian_soal_id' => $usTwk->id, 'soal_id' => $soalTwk->id, 'jenis_ujian_id' => $twk->id, 'jawaban' => 'A', 'nilai' => 5, 'benar' => true]);
        $up->jawaban()->create(['ujian_soal_id' => $usTiu->id, 'soal_id' => $soalTiu->id, 'jenis_ujian_id' => $tiu->id, 'jawaban' => 'C', 'nilai' => 0, 'benar' => false]);

        $service = app(UjianScoringService::class);
        $service->aggregateCategories($up);
        $service->evaluatePass($up);

        expect($up->fresh()->lulus)->toBeFalse();
        expect($up->kategori()->where('jenis_ujian_id', $twk->id)->first()->lulus_kategori)->toBeTrue();
        expect($up->kategori()->where('jenis_ujian_id', $tiu->id)->first()->lulus_kategori)->toBeFalse();
    });
});

describe('rank & positionOf', function () {
    it('ranks finished peserta by nilai desc then fastest completion', function () {
        $ujian = Ujian::factory()->create();

        $userA = User::factory()->create(['is_peserta' => true]);
        $userB = User::factory()->create(['is_peserta' => true]);
        $userC = User::factory()->create(['is_peserta' => true]);

        $ujian->peserta()->create(['user_id' => $userA->id, 'status' => 'selesai', 'total_nilai' => 80, 'waktu_selesai' => now()->subMinutes(10)]);
        $ujian->peserta()->create(['user_id' => $userB->id, 'status' => 'selesai', 'total_nilai' => 90, 'waktu_selesai' => now()->subMinutes(5)]);
        $tieFast = $ujian->peserta()->create(['user_id' => $userC->id, 'status' => 'selesai', 'total_nilai' => 90, 'waktu_selesai' => now()->subMinutes(30)]);

        $ranking = app(UjianScoringService::class)->rank($ujian);

        expect($ranking->pluck('user_id')->all())->toBe([$userC->id, $userB->id, $userA->id]);
        expect($ranking->first()->id)->toBe($tieFast->id);
    });

    it('excludes peserta that have not finished', function () {
        $ujian = Ujian::factory()->create();
        $done = User::factory()->create(['is_peserta' => true]);
        $ongoing = User::factory()->create(['is_peserta' => true]);

        $ujian->peserta()->create(['user_id' => $done->id, 'status' => 'selesai', 'total_nilai' => 50, 'waktu_selesai' => now()]);
        $ujian->peserta()->create(['user_id' => $ongoing->id, 'status' => 'sedang_ujian', 'total_nilai' => 99]);

        $ranking = app(UjianScoringService::class)->rank($ujian);

        expect($ranking)->toHaveCount(1);
        expect($ranking->first()->user_id)->toBe($done->id);
    });

    it('returns the position of a peserta', function () {
        $ujian = Ujian::factory()->create();
        $top = User::factory()->create(['is_peserta' => true]);
        $mid = User::factory()->create(['is_peserta' => true]);

        $ujian->peserta()->create(['user_id' => $top->id, 'status' => 'selesai', 'total_nilai' => 100, 'waktu_selesai' => now()->subMinutes(5)]);
        $midPeserta = $ujian->peserta()->create(['user_id' => $mid->id, 'status' => 'selesai', 'total_nilai' => 70, 'waktu_selesai' => now()]);

        $posisi = app(UjianScoringService::class)->positionOf($midPeserta);

        expect($posisi)->toBe(['rank' => 2, 'total' => 2]);
    });
});
