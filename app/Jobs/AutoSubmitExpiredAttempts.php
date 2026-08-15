<?php

namespace App\Jobs;

use App\Services\Ujian\AttemptService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * JOB: AutoSubmitExpiredAttempts
 * ==============================
 * Finalizes every attempt whose snapshot deadline (`batas_waktu`) has passed
 * while still `sedang_ujian` (AD-6 / AD-10). Scheduled every minute (§11).
 */
class AutoSubmitExpiredAttempts implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(AttemptService $attempts): void
    {
        $attempts->autoSubmitExpired();
    }
}
