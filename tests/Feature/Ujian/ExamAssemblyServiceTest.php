<?php

use App\Models\JenisUjian;
use App\Models\Soal;
use App\Models\SubIndikator;
use App\Models\SubJenisUjian;
use App\Models\Ujian;
use App\Services\Ujian\ExamAssemblyService;
use Illuminate\Validation\ValidationException;

function assemblyService(): ExamAssemblyService
{
    return app(ExamAssemblyService::class);
}

/**
 * @return array<int, int>
 */
function makeBankSoal(JenisUjian $jenis, int $count): array
{
    $subJenis = SubJenisUjian::factory()->create(['jenis_ujian_id' => $jenis->id]);
    $subIndikator = SubIndikator::factory()->create([
        'sub_jenis_ujian_id' => $subJenis->id,
        'jenis_ujian_id' => $jenis->id,
    ]);

    return Soal::factory()->count($count)->create([
        'sub_indikator_id' => $subIndikator->id,
    ])->pluck('id')->all();
}

describe('remainingSlots', function () {
    it('returns jumlah_soal minus attached questions', function () {
        $jenis = JenisUjian::factory()->create();
        $ujian = Ujian::factory()->create(['jumlah_soal' => 5]);
        $soalIds = makeBankSoal($jenis, 2);

        assemblyService()->addQuestions($ujian, $jenis->id, $soalIds);

        expect(assemblyService()->remainingSlots($ujian))->toBe(3);
    });
});

describe('addQuestions', function () {
    it('appends questions with incrementing urutan', function () {
        $jenis = JenisUjian::factory()->create();
        $ujian = Ujian::factory()->create(['jumlah_soal' => 5]);
        $soalIds = makeBankSoal($jenis, 3);

        assemblyService()->addQuestions($ujian, $jenis->id, $soalIds);

        $rows = $ujian->ujianSoals()->orderBy('urutan')->get();
        expect($rows)->toHaveCount(3);
        expect($rows->pluck('urutan')->all())->toBe([1, 2, 3]);
        expect($rows->pluck('jenis_ujian_id')->unique()->all())->toBe([$jenis->id]);
    });

    it('rejects adding questions beyond capacity', function () {
        $jenis = JenisUjian::factory()->create();
        $ujian = Ujian::factory()->create(['jumlah_soal' => 2]);
        $soalIds = makeBankSoal($jenis, 3);

        expect(fn () => assemblyService()->addQuestions($ujian, $jenis->id, $soalIds))
            ->toThrow(ValidationException::class);

        expect($ujian->ujianSoals()->count())->toBe(0);
    });

    it('ignores duplicate soal ids already attached', function () {
        $jenis = JenisUjian::factory()->create();
        $ujian = Ujian::factory()->create(['jumlah_soal' => 5]);
        $soalIds = makeBankSoal($jenis, 2);

        assemblyService()->addQuestions($ujian, $jenis->id, $soalIds);
        assemblyService()->addQuestions($ujian, $jenis->id, $soalIds);

        expect($ujian->ujianSoals()->count())->toBe(2);
    });
});

describe('removeQuestion', function () {
    it('detaches a single question', function () {
        $jenis = JenisUjian::factory()->create();
        $ujian = Ujian::factory()->create(['jumlah_soal' => 5]);
        $soalIds = makeBankSoal($jenis, 2);

        assemblyService()->addQuestions($ujian, $jenis->id, $soalIds);
        assemblyService()->removeQuestion($ujian, $soalIds[0]);

        expect($ujian->ujianSoals()->count())->toBe(1);
    });
});

describe('assertFinalizable', function () {
    it('blocks when remaining slots are greater than zero', function () {
        $jenis = JenisUjian::factory()->create();
        $ujian = Ujian::factory()->create(['jumlah_soal' => 5]);
        $ujian->jenisUjians()->attach($jenis->id, ['passing_grade' => null]);
        $soalIds = makeBankSoal($jenis, 2);

        assemblyService()->addQuestions($ujian, $jenis->id, $soalIds);

        expect(fn () => assemblyService()->assertFinalizable($ujian))
            ->toThrow(ValidationException::class);
    });

    it('blocks when the question bank is short (R4)', function () {
        $jenis = JenisUjian::factory()->create();
        $ujian = Ujian::factory()->create(['jumlah_soal' => 3]);
        $ujian->jenisUjians()->attach($jenis->id, ['passing_grade' => null]);
        $soalIds = makeBankSoal($jenis, 3);

        assemblyService()->addQuestions($ujian, $jenis->id, $soalIds);

        Soal::query()->whereIn('id', $soalIds)->limit(1)->delete();
        $ujian->refresh();

        expect(fn () => assemblyService()->assertFinalizable($ujian))
            ->toThrow(ValidationException::class);
    });

    it('passes when capacity is met and the bank is sufficient', function () {
        $jenis = JenisUjian::factory()->create();
        $ujian = Ujian::factory()->create(['jumlah_soal' => 3]);
        $ujian->jenisUjians()->attach($jenis->id, ['passing_grade' => null]);
        $soalIds = makeBankSoal($jenis, 3);

        assemblyService()->addQuestions($ujian, $jenis->id, $soalIds);

        assemblyService()->assertFinalizable($ujian);

        expect(true)->toBeTrue();
    });
});
