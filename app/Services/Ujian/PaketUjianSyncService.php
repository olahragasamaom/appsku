<?php

namespace App\Services\Ujian;

use App\Models\Paket;
use App\Models\Ujian;
use Illuminate\Validation\ValidationException;

class PaketUjianSyncService
{
    /**
     * Sync online exams for a package via the pivot table (AD-5).
     *
     * Only online_paket exams are allowed; offline_kelas exams are rejected.
     * Non-destructive: akses_member column is left untouched (CF-2).
     *
     * @param  array<int, int>  $ujianIds
     */
    public function sync(Paket $paket, array $ujianIds): void
    {
        $ujianIds = array_map('intval', $ujianIds);

        if ($ujianIds !== []) {
            $this->assertAllOnline($ujianIds);
        }

        $paket->ujians()->sync($ujianIds);
    }

    /**
     * Attach a single online exam to a package.
     */
    public function attach(Paket $paket, Ujian $ujian): void
    {
        $this->assertOnline($ujian);

        if ($paket->ujians()->where('panritta_ujian.id', $ujian->id)->exists()) {
            return;
        }

        $paket->ujians()->attach($ujian->id);
    }

    /**
     * Detach a single exam from a package.
     */
    public function detach(Paket $paket, Ujian $ujian): void
    {
        $paket->ujians()->detach($ujian->id);
    }

    private function assertOnline(Ujian $ujian): void
    {
        if (! $ujian->isOnline()) {
            throw ValidationException::withMessages([
                'ujian_id' => "Ujian [{$ujian->nama_ujian}] bukan tipe online_paket dan tidak dapat ditautkan ke paket.",
            ]);
        }
    }

    /**
     * @param  array<int, int>  $ujianIds
     */
    private function assertAllOnline(array $ujianIds): void
    {
        $offlineCount = Ujian::query()
            ->whereIn('id', $ujianIds)
            ->where('tipe_ujian', 'offline_kelas')
            ->count();

        if ($offlineCount > 0) {
            throw ValidationException::withMessages([
                'ujian_id' => 'Hanya ujian bertipe online_paket yang dapat ditautkan ke paket.',
            ]);
        }
    }
}
