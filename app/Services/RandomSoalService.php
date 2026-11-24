<?php

namespace App\Services;

use App\Models\Ujian;
use App\Models\UjianPeserta;
use Illuminate\Support\Collection;

class RandomSoalService
{
    public function generateSoalOrder(Ujian $ujian, int $jenisUjianId): array
    {
        $ujianSoals = $ujian->ujianSoals()
            ->with('soal.subIndikator')
            ->where('jenis_ujian_id', $jenisUjianId)
            ->orderBy('urutan')
            ->get();

        $groupedBySubIndikator = $ujianSoals->groupBy(fn ($soal) => $soal->soal?->sub_indikator_id ?? 'unknown');

        $randomOrder = [];

        foreach ($groupedBySubIndikator as $subIndikatorId => $soals) {
            $soalIds = $soals->pluck('id')->toArray();
            shuffle($soalIds);
            $randomOrder = array_merge($randomOrder, $soalIds);
        }

        return $randomOrder;
    }

    public function saveSoalOrder(UjianPeserta $peserta, array $soalOrder): void
    {
        $peserta->soal_order = $soalOrder;
        $peserta->save();
    }

    public function getDisplayNumber(int $ujianSoalId, ?array $soalOrder): int
    {
        if (! $soalOrder) {
            return 0;
        }

        $position = array_search($ujianSoalId, $soalOrder, strict: true);

        return $position !== false ? $position + 1 : 0;
    }

    public function reorderSoalsByList(Collection $ujianSoals, ?array $soalOrder): Collection
    {
        if (! $soalOrder) {
            return $ujianSoals->sortBy('urutan');
        }

        $ordered = collect();
        foreach ($soalOrder as $ujianSoalId) {
            $soal = $ujianSoals->firstWhere('id', $ujianSoalId);
            if ($soal) {
                $ordered->push($soal);
            }
        }

        return $ordered;
    }
}
