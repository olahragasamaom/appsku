<?php

namespace App\Services;

use App\Models\Ujian;
use App\Models\UjianPeserta;
use Illuminate\Support\Collection;

class RandomSoalService
{
    /**
     * Acak urutan soal di dalam tiap sub-indikator untuk seluruh soal ujian,
     * dengan tetap menjaga urutan sub-indikatornya.
     *
     * @return array<int> Daftar ID panritta_ujian_soal yang telah diacak per sub-indikator
     */
    public function generateSoalOrderForUjian(Ujian $ujian): array
    {
        $ujianSoals = $ujian->ujianSoals()
            ->with('soal.subIndikator')
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        if ($ujianSoals->isEmpty()) {
            return [];
        }

        // Kelompokkan soal berdasarkan kombinasi sub_jenis_ujian_id dan sub_indikator_id
        // Collection::groupBy mempertahankan urutan kemunculan grup sesuai urutan soal
        $groupedBySubIndikator = $ujianSoals->groupBy(function ($ujianSoal) {
            $subJenisId = $ujianSoal->soal?->subIndikator?->sub_jenis_ujian_id
                ?? $ujianSoal->soal?->sub_jenis_ujian_id
                ?? $ujianSoal->jenis_ujian_id
                ?? 0;
            $subIndikatorId = $ujianSoal->soal?->sub_indikator_id ?? 0;

            return $subJenisId . '_' . $subIndikatorId;
        });

        $randomOrder = [];

        foreach ($groupedBySubIndikator as $groupKey => $soals) {
            $soalIds = $soals->pluck('id')->toArray();
            if (count($soalIds) > 1) {
                $original = $soalIds;
                $attempts = 0;
                while ($soalIds === $original && $attempts < 5) {
                    shuffle($soalIds);
                    $attempts++;
                }
            }
            $randomOrder = array_merge($randomOrder, $soalIds);
        }

        return $randomOrder;
    }

    /**
     * Acak urutan soal per sub-indikator (dengan parameter jenisUjianId untuk backward-compatibility).
     */
    public function generateSoalOrder(Ujian $ujian, ?int $jenisUjianId = null): array
    {
        if ($jenisUjianId === null) {
            return $this->generateSoalOrderForUjian($ujian);
        }

        // Jika jenisUjianId mencakup semua soal atau jika ingin acak seluruh ujian
        $soalsCountInJenis = $ujian->ujianSoals()->where('jenis_ujian_id', $jenisUjianId)->count();
        $totalSoals = $ujian->ujianSoals()->count();

        if ($soalsCountInJenis === $totalSoals) {
            return $this->generateSoalOrderForUjian($ujian);
        }

        $ujianSoals = $ujian->ujianSoals()
            ->with('soal.subIndikator')
            ->where('jenis_ujian_id', $jenisUjianId)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        $groupedBySubIndikator = $ujianSoals->groupBy(function ($ujianSoal) {
            return $ujianSoal->soal?->sub_indikator_id ?? 0;
        });

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
        if (empty($soalOrder)) {
            return $ujianSoals->sortBy(['urutan', 'id']);
        }

        $ujianSoalMap = $ujianSoals->keyBy('id');
        $ordered = collect();

        foreach ($soalOrder as $ujianSoalId) {
            if ($ujianSoalMap->has($ujianSoalId)) {
                $ordered->push($ujianSoalMap->get($ujianSoalId));
                $ujianSoalMap->forget($ujianSoalId);
            }
        }

        // Masukkan sisa soal (jika ada soal ujian yang belum masuk daftar soalOrder)
        foreach ($ujianSoalMap as $remaining) {
            $ordered->push($remaining);
        }

        return $ordered;
    }
}
