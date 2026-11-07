<?php

namespace App\Services;

use App\Models\UjianPeserta;
use App\Models\Soal;
use Illuminate\Support\Collection;

class ExamRandomizationService
{
    /**
     * Generate randomization for exam attempt.
     * Deterministic shuffle using seed for reproducibility.
     */
    public function generateAttemptRandomization(UjianPeserta $ujianPeserta): void
    {
        $seed = intval(time() % 2147483647) ^ ($ujianPeserta->id * 73);
        $ujianPeserta->attempt_seed = $seed;
        $ujianPeserta->save();

        // Get questions grouped by sub_jenis_ujian
        $questions = $ujianPeserta->ujian
            ->soal()
            ->with('subIndikator.subJenisUjian')
            ->get();

        // Shuffle questions within each sub-jenis, preserve sub-jenis grouping
        $questionOrder = $this->shuffleQuestionsBySubJenis($questions, $seed);
        
        // Shuffle answer options for each question
        $optionMapping = $this->generateOptionMapping($questions, $seed);

        $ujianPeserta->question_order = $questionOrder;
        $ujianPeserta->option_mapping = $optionMapping;
        $ujianPeserta->save();
    }

    /**
     * Shuffle questions within sub-jenis groups using deterministic seed.
     * Returns: {soal_id: new_position}
     */
    private function shuffleQuestionsBySubJenis(Collection $questions, int $seed): array
    {
        $grouped = $questions->groupBy(fn($q) => $q->subIndikator?->subJenisUjian?->id ?? 'unknown');
        $questionOrder = [];
        $position = 1;

        foreach ($grouped as $subJenisId => $subJenisQuestions) {
            $ids = $subJenisQuestions->pluck('id')->toArray();
            $shuffled = $this->deterministicShuffle($ids, $seed . '_' . $subJenisId);
            
            foreach ($shuffled as $soalId) {
                $questionOrder[$soalId] = $position++;
            }
        }

        return $questionOrder;
    }

    /**
     * Deterministic shuffle using seed.
     */
    private function deterministicShuffle(array $items, string $seedStr): array
    {
        $seed = crc32($seedStr) % 2147483647;
        mt_srand($seed);

        $shuffled = $items;
        shuffle($shuffled);

        return $shuffled;
    }

    /**
     * Generate option mapping for answer shuffling.
     * Returns: {soal_id: {a: orig_option, b: orig_option, ...}}
     */
    private function generateOptionMapping(Collection $questions, int $seed): array
    {
        $optionMapping = [];

        foreach ($questions as $soal) {
            $options = ['a', 'b', 'c', 'd'];
            if ($soal->kunci_jawaban === 'E' || (!empty($soal->opsi_e))) {
                $options[] = 'e';
            }

            $shuffled = $this->deterministicShuffle($options, $seed . '_opt_' . $soal->id);
            $mapping = [];

            foreach ($shuffled as $newPos => $origOption) {
                $newLetter = chr(97 + $newPos); // a, b, c, d, e
                $mapping[$newLetter] = strtolower($origOption);
            }

            $optionMapping[$soal->id] = $mapping;
        }

        return $optionMapping;
    }

    /**
     * Get shuffled question position for display.
     */
    public function getQuestionPosition(UjianPeserta $ujianPeserta, int $soalId): int
    {
        $questionOrder = $ujianPeserta->question_order ?? [];
        return $questionOrder[$soalId] ?? $soalId;
    }

    /**
     * Get original option letter from shuffled position.
     * E.g., if user selected 'a', return what 'a' maps to (original 'c')
     */
    public function getOriginalOption(UjianPeserta $ujianPeserta, int $soalId, string $selectedLetter): ?string
    {
        $optionMapping = $ujianPeserta->option_mapping ?? [];
        return $optionMapping[$soalId][$selectedLetter] ?? null;
    }

    /**
     * Get shuffled option letter from original.
     * E.g., if original answer is 'a', return shuffled letter ('c')
     */
    public function getShuffledOption(UjianPeserta $ujianPeserta, int $soalId, string $originalLetter): ?string
    {
        $optionMapping = $ujianPeserta->option_mapping[$soalId] ?? [];
        return array_search(strtolower($originalLetter), $optionMapping) ?: null;
    }
}
