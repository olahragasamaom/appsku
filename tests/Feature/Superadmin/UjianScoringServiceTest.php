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
