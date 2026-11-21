<?php

namespace App\Services;

use App\Models\Ujian;
use App\Models\UjianPeserta;
use Illuminate\Support\Facades\Crypt;

class OpsiRandomizer
{
    /**
     * Get shuffled opsi mapping untuk peserta tertentu di ujian tertentu untuk soal tertentu.
     * Return: ['a' => 'c', 'b' => 'e', 'c' => 'a', 'd' => 'd', 'e' => 'b']
     * Meaning: Original opsi A ditampilkan di posisi C, original opsi B di posisi E, dst.
     *
     * Randomize aktif jika:
     * - ujian.randomize_opsi_tkp === true
     * - ujianPeserta.peserta_offline_id !== null (offline participant)
     * - attempt > 1 (re-attempt, bukan first attempt)
     *
     * Deterministic: seed berbasis ujian_id + peserta_id + attempt sehingga peserta selalu dapat urutan sama per attempt.
     *
     * @param Ujian $ujian
     * @param UjianPeserta $ujianPeserta
     * @param int $soalId
     * @return array mapping original → display position
     */
    public static function getMappingForPeserta(Ujian $ujian, UjianPeserta $ujianPeserta, int $soalId): array
    {
        $isOffline = $ujianPeserta->peserta_offline_id !== null;
        $attemptNumber = $ujianPeserta->attempt_number ?? 1;
        $isReAttempt = $attemptNumber > 1;
        
        if (!$ujian->randomize_opsi_tkp || !$isOffline || !$isReAttempt) {
            return ['a' => 'a', 'b' => 'b', 'c' => 'c', 'd' => 'd', 'e' => 'e'];
        }

        $seed = crc32("{$ujian->id}_{$ujianPeserta->peserta_offline_id}_{$attemptNumber}_{$soalId}");
        mt_srand($seed);

        $opsi = ['a', 'b', 'c', 'd', 'e'];
        shuffle($opsi);

        return array_flip(array_combine(['a', 'b', 'c', 'd', 'e'], $opsi));
    }

    /**
     * Get display text untuk opsi (shuffled label jika randomize_opsi_tkp aktif).
     * Contoh: original opsi['a'] content akan ditampilkan dengan label opsi E di UI.
     *
     * @param array $opsiData ['a' => content_a, 'b' => content_b, ...]
     * @param array $mapping ['a' => 'c', 'b' => 'e', ...] dari getMappingForPeserta
     * @return array shuffled [displayPos => originalOpsiKey, ...]
     */
    public static function getDisplayOrder(array $opsiData, array $mapping): array
    {
        $display = [];
        foreach (['a', 'b', 'c', 'd', 'e'] as $originalKey) {
            if (isset($opsiData[$originalKey])) {
                $displayPos = $mapping[$originalKey] ?? $originalKey;
                $display[$displayPos] = $originalKey;
            }
        }
        ksort($display);
        return $display;
    }

    /**
     * Reverse map: dari display label (user pilih) ke original opsi key.
     * User pilih "opsi C" → return 'b' (original opsi B ditampilkan di posisi C).
     *
     * @param string $userSelectedDisplay user pilih label (a/b/c/d/e) di UI
     * @param array $mapping ['a' => 'c', ...] dari getMappingForPeserta
     * @return string original opsi key
     */
    public static function reverseMapDisplay(string $userSelectedDisplay, array $mapping): string
    {
        return array_search($userSelectedDisplay, $mapping) ?: $userSelectedDisplay;
    }
}
