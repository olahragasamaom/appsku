<?php

namespace Tests\Unit;

use App\Models\Soal;
use App\Models\SubIndikator;
use App\Models\Ujian;
use App\Models\UjianSoal;
use App\Services\RandomSoalService;
use PHPUnit\Framework\TestCase;

class RandomSoalServiceTest extends TestCase
{
    private RandomSoalService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RandomSoalService();
    }

    public function test_reorder_soals_by_list(): void
    {
        $soal1 = new UjianSoal(['urutan' => 1]);
        $soal1->id = 101;
        $soal2 = new UjianSoal(['urutan' => 2]);
        $soal2->id = 102;
        $soal3 = new UjianSoal(['urutan' => 3]);
        $soal3->id = 103;

        $collection = collect([$soal1, $soal2, $soal3]);

        // Reorder: 103, 101, 102
        $reordered = $this->service->reorderSoalsByList($collection, [103, 101, 102]);

        $this->assertEquals([103, 101, 102], $reordered->pluck('id')->all());
    }

    public function test_reorder_soals_handles_missing_or_empty_order(): void
    {
        $soal1 = new UjianSoal(['urutan' => 1]);
        $soal1->id = 101;
        $soal2 = new UjianSoal(['urutan' => 2]);
        $soal2->id = 102;

        $collection = collect([$soal2, $soal1]);

        $reordered = $this->service->reorderSoalsByList($collection, null);
        $this->assertEquals([101, 102], $reordered->pluck('id')->all());
    }

    public function test_get_display_number(): void
    {
        $soalOrder = [205, 201, 203, 202];

        $this->assertEquals(1, $this->service->getDisplayNumber(205, $soalOrder));
        $this->assertEquals(2, $this->service->getDisplayNumber(201, $soalOrder));
        $this->assertEquals(3, $this->service->getDisplayNumber(203, $soalOrder));
        $this->assertEquals(4, $this->service->getDisplayNumber(202, $soalOrder));
        $this->assertEquals(0, $this->service->getDisplayNumber(999, $soalOrder));
    }
}
