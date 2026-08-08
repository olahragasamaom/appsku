<?php

namespace App\Services\Ujian;

use App\Models\PesertaLangganan;
use App\Models\Ujian;
use App\Models\UjianPeserta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AttemptService
{
    public function __construct(
        private readonly UjianScoringService $scoring
    ) {}

    /**
     * Start (or re-take) an online/offline attempt for the given user.
     *
     * Online attempts consume quota atomically and snapshot the deadline from
     * the active subscription. Re-takes always create a new attempt row (AD-9).
     */
    public function start(Ujian $ujian, int $userId): UjianPeserta
    {
        if ($ujian->isOffline()) {
            return $this->createAttempt($ujian, $userId, [
                'batas_waktu' => $this->offlineDeadline($ujian),
            ]);
        }

        return DB::transaction(function () use ($ujian, $userId): UjianPeserta {
            $langganan = $this->lockActiveSubscription($ujian, $userId);

            $unlimited = $langganan->paket?->kuota_ujian === null;

            if (! $unlimited && (int) $langganan->sisa_kuota_ujian <= 0) {
                throw ValidationException::withMessages([
                    'kuota' => 'Kuota ujian Anda sudah habis.',
                ]);
            }

            if (! $unlimited) {
                $langganan->decrement('sisa_kuota_ujian');
            }

            return $this->createAttempt($ujian, $userId, [
                'langganan_id' => $langganan->id,
                'batas_waktu' => $langganan->berakhir_pada,
            ]);
        });
    }

    /**
     * Start an offline attempt via participant credentials (C-4).
     */
    public function startOffline(string $nomorPeserta, string $kodeAkses, Ujian $ujian): UjianPeserta
    {
        $pesertaOffline = $ujian->pesertaOffline()
            ->where('nomor_peserta', $nomorPeserta)
            ->first();

        if (! $pesertaOffline || ! Hash::check($kodeAkses, $pesertaOffline->kode_akses)) {
            throw ValidationException::withMessages([
                'kode_akses' => 'Nomor peserta atau kode akses salah.',
            ]);
        }

        $attempt = $this->createAttempt($ujian, null, [
            'batas_waktu' => $this->offlineDeadline($ujian),
        ]);

        $pesertaOffline->forceFill(['ujian_peserta_id' => $attempt->id])->save();

        return $attempt;
    }

    /**
     * Finalize an attempt. No-op when already finished (idempotent).
     */
    public function submit(UjianPeserta $peserta): void
    {
        if ($peserta->status === 'selesai') {
            return;
        }

        $this->scoring->finalize($peserta);
    }

    /**
     * Auto-submit every attempt past its snapshot deadline (AD-10 / C-AU-6).
     */
    public function autoSubmitExpired(): void
    {
        UjianPeserta::query()
            ->where('status', 'sedang_ujian')
            ->whereNotNull('batas_waktu')
            ->where('batas_waktu', '<=', now())
            ->get()
            ->each(function (UjianPeserta $peserta): void {
                $peserta->forceFill(['auto_submitted' => true])->save();
                $this->submit($peserta);
            });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createAttempt(Ujian $ujian, ?int $userId, array $attributes): UjianPeserta
    {
        return $ujian->peserta()->create(array_merge([
            'user_id' => $userId,
            'status' => 'sedang_ujian',
            'waktu_mulai' => now(),
        ], $attributes));
    }

    private function offlineDeadline(Ujian $ujian): ?\Illuminate\Support\Carbon
    {
        if (! $ujian->durasi_ujian) {
            return null;
        }

        return now()->addMinutes($ujian->durasi_ujian);
    }

    private function lockActiveSubscription(Ujian $ujian, int $userId): PesertaLangganan
    {
        $paketIds = $ujian->pakets()->pluck('panritta_paket.id')->all();

        $langganan = PesertaLangganan::query()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->when($paketIds !== [], fn ($query) => $query->whereIn('paket_id', $paketIds))
            ->lockForUpdate()
            ->first();

        if (! $langganan) {
            throw ValidationException::withMessages([
                'langganan' => 'Anda tidak memiliki langganan aktif untuk ujian ini.',
            ]);
        }

        return $langganan;
    }
}
