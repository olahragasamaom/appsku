<?php

namespace App\Services;

use App\Models\Ujian;
use App\Models\UjianPeserta;
use App\Models\PesertaOffline;
use Carbon\Carbon;

class ExamAttemptService
{
    protected ExamRandomizationService $randomizationService;

    public function __construct(ExamRandomizationService $randomizationService)
    {
        $this->randomizationService = $randomizationService;
    }

    /**
     * Create new attempt untuk offline peserta.
     * Check: sudah berapa kali ujian? Masih dalam rentang tanggal?
     */
    public function createOfflineAttempt(Ujian $ujian, PesertaOffline $pesertaOffline): UjianPeserta
    {
        $now = Carbon::now();

        // Cek: ujian dalam rentang tanggal?
        if ($now->isBefore($ujian->tanggal_ujian) || $now->isAfter($ujian->tanggal_ujian->addMinutes($ujian->durasi + 60))) {
            throw new \Exception('Ujian tidak dalam rentang waktu yang diizinkan');
        }

        // Hitung attempt sebelumnya
        $previousAttempts = UjianPeserta::where('ujian_id', $ujian->id)
            ->where('peserta_offline_id', $pesertaOffline->id)
            ->count();

        if ($previousAttempts >= 10) {
            throw new \Exception('Sudah mencapai batas maksimal 10 kali ujian');
        }

        $attemptNumber = $previousAttempts + 1;

        // Buat record baru
        $ujianPeserta = new UjianPeserta();
        $ujianPeserta->ujian_id = $ujian->id;
        $ujianPeserta->peserta_offline_id = $pesertaOffline->id;
        $ujianPeserta->attempt_number = $attemptNumber;
        $ujianPeserta->waktu_mulai = $now;
        $ujianPeserta->batas_waktu = $now->addMinutes($ujian->durasi);
        $ujianPeserta->status = 'sedang_dikerjakan';
        $ujianPeserta->save();

        // Generate randomization (shuffle soal & opsi)
        $this->randomizationService->generateAttemptRandomization($ujianPeserta);

        return $ujianPeserta;
    }

    /**
     * Get current/best attempt untuk display hasil.
     * Untuk ranking: ambil attempt dengan nilai tertinggi.
     */
    public function getBestAttempt(Ujian $ujian, PesertaOffline $pesertaOffline): ?UjianPeserta
    {
        return UjianPeserta::where('ujian_id', $ujian->id)
            ->where('peserta_offline_id', $pesertaOffline->id)
            ->whereNotNull('total_nilai')
            ->orderByDesc('total_nilai')
            ->first();
    }

    /**
     * Get semua attempts untuk history view.
     */
    public function getAllAttempts(Ujian $ujian, PesertaOffline $pesertaOffline)
    {
        return UjianPeserta::where('ujian_id', $ujian->id)
            ->where('peserta_offline_id', $pesertaOffline->id)
            ->orderBy('attempt_number')
            ->get();
    }
}
