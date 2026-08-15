<?php

namespace App\Jobs;

use App\Models\PesertaLangganan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * JOB: ExpireSubscriptions
 * ========================
 * Flips `panritta_peserta_langganan.status` to `expired` once `berakhir_pada`
 * has passed for active subscriptions. Scheduled hourly (§11).
 */
class ExpireSubscriptions implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        PesertaLangganan::query()
            ->where('status', 'active')
            ->whereNotNull('berakhir_pada')
            ->where('berakhir_pada', '<=', now())
            ->update(['status' => 'expired']);
    }
}
