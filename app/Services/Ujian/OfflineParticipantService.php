<?php

namespace App\Services\Ujian;

use App\Models\PesertaOffline;
use App\Models\PesertaOfflineKehadiran;
use App\Models\Ujian;
use App\Models\UjianPeserta;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OfflineParticipantService
{
    /**
     * Create a single offline participant and return the plaintext kode_akses once.
     * Auto-initializes attendance record with status 'tidak_hadir'.
     *
     * @param  array{nomor_peserta: string, nama_peserta: string}  $data
     * @return array{peserta: PesertaOffline, kode_akses: string}
     */
    public function create(Ujian $ujian, array $data): array
    {
        $this->assertOffline($ujian);

        $plaintext = $this->generateKodeAkses();

        $peserta = PesertaOffline::create([
            'ujian_id' => $ujian->id,
            'nomor_peserta' => $data['nomor_peserta'],
            'nama_peserta' => $data['nama_peserta'],
            'kode_akses' => Hash::make($plaintext),
            'kode_akses_plain' => $plaintext,
        ]);

        PesertaOfflineKehadiran::create([
            'peserta_offline_id' => $peserta->id,
            'ujian_id' => $ujian->id,
            'status_kehadiran' => 'tidak_hadir',
        ]);

        return ['peserta' => $peserta, 'kode_akses' => $plaintext];
    }

    /**
     * Bulk-create offline participants. Returns collection of
     * ['nomor_peserta' => ..., 'kode_akses' => plaintext].
     *
     * @param  array<int, array{nomor_peserta: string, nama_peserta: string}>  $participants
     * @return Collection<int, array{nomor_peserta: string, kode_akses: string}>
     */
    public function bulkCreate(Ujian $ujian, array $participants): Collection
    {
        $this->assertOffline($ujian);

        return collect($participants)->map(function (array $data) use ($ujian): array {
            $result = $this->create($ujian, $data);

            return [
                'nomor_peserta' => $result['peserta']->nomor_peserta,
                'kode_akses' => $result['kode_akses'],
            ];
        });
    }

    /**
     * Block an offline participant by setting their linked attempt status to 'diblokir'.
     */
    public function blockParticipant(PesertaOffline $peserta): void
    {
        if ($peserta->ujian_peserta_id) {
            UjianPeserta::where('id', $peserta->ujian_peserta_id)
                ->update(['status' => 'diblokir']);
        }
    }

    /**
     * Mark attendance for an offline participant on a specific exam.
     * Idempotent: updating to same status is safe.
     *
     * @param  string  $status  'hadir' or 'tidak_hadir'
     */
    public function markAttendance(PesertaOffline $peserta, Ujian $ujian, string $status): void
    {
        if (! in_array($status, ['hadir', 'tidak_hadir'])) {
            throw ValidationException::withMessages([
                'status_kehadiran' => "Status kehadiran harus 'hadir' atau 'tidak_hadir'.",
            ]);
        }

        PesertaOfflineKehadiran::updateOrCreate(
            [
                'peserta_offline_id' => $peserta->id,
                'ujian_id' => $ujian->id,
            ],
            [
                'status_kehadiran' => $status,
            ]
        );
    }

    /**
     * Reset kode akses untuk peserta. Generate kode baru dan return plaintext-nya.
     * Tercatat siapa dan kapan reset dilakukan.
     */
    public function resetKodeAkses(PesertaOffline $peserta, ?int $resetBy = null): string
    {
        $plaintext = $this->generateKodeAkses();

        $peserta->update([
            'kode_akses' => Hash::make($plaintext),
            'kode_akses_plain' => $plaintext,
            'kode_akses_reset_at' => now(),
            'kode_akses_reset_by' => $resetBy,
        ]);

        return $plaintext;
    }

    /**
     * Bulk reset kode akses untuk semua peserta di ujian tertentu.
     * Return collection [{nomor_peserta, kode_akses_plain}, ...] untuk print/export.
     *
     * @return Collection<int, array{nomor_peserta: string, kode_akses: string}>
     */
    public function bulkResetKodeAkses(Ujian $ujian, ?int $resetBy = null): Collection
    {
        $peserta = PesertaOffline::where('ujian_id', $ujian->id)->get();

        return $peserta->map(function (PesertaOffline $p) use ($resetBy): array {
            $plaintext = $this->resetKodeAkses($p, $resetBy);

            return [
                'nomor_peserta' => $p->nomor_peserta,
                'nama_peserta' => $p->nama_peserta,
                'kode_akses' => $plaintext,
            ];
        });
    }

    /**
     * Assign peserta existing ke ujian lain (buat kehadiran record baru).
     * Idempotent: kalau sudah pernah assigned, tidak duplicate.
     */
    public function assignToUjian(PesertaOffline $peserta, Ujian $ujian): PesertaOfflineKehadiran
    {
        $this->assertOffline($ujian);

        return PesertaOfflineKehadiran::firstOrCreate(
            [
                'peserta_offline_id' => $peserta->id,
                'ujian_id' => $ujian->id,
            ],
            [
                'status_kehadiran' => 'tidak_hadir',
            ]
        );
    }

    /**
     * Remove peserta dari ujian tertentu (hapus kehadiran, bukan peserta).
     */
    public function unassignFromUjian(PesertaOffline $peserta, Ujian $ujian): void
    {
        PesertaOfflineKehadiran::where('peserta_offline_id', $peserta->id)
            ->where('ujian_id', $ujian->id)
            ->delete();
    }

    /**
     * Copy peserta dari ujian sumber ke ujian target (bulk assign).
     * Return jumlah peserta yang berhasil di-copy.
     */
    public function copyPesertaFromUjian(Ujian $sourceUjian, Ujian $targetUjian): int
    {
        $this->assertOffline($targetUjian);

        $sourcePeserta = PesertaOffline::where('ujian_id', $sourceUjian->id)->get();

        $count = 0;
        foreach ($sourcePeserta as $peserta) {
            $created = PesertaOfflineKehadiran::firstOrCreate(
                [
                    'peserta_offline_id' => $peserta->id,
                    'ujian_id' => $targetUjian->id,
                ],
                [
                    'status_kehadiran' => 'tidak_hadir',
                ]
            );

            if ($created->wasRecentlyCreated) {
                $count++;
            }
        }

        return $count;
    }

    private function assertOffline(Ujian $ujian): void
    {
        if (! $ujian->isOffline()) {
            throw ValidationException::withMessages([
                'ujian' => 'Peserta offline hanya dapat ditambahkan pada ujian bertipe offline_kelas.',
            ]);
        }
    }

    private function generateKodeAkses(): string
    {
        return strtoupper(Str::random(8));
    }
}
