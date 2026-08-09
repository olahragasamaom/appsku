<?php

namespace App\Services\Ujian;

use App\Models\PesertaOffline;
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
            // Simpan versi teks agar bisa ditampilkan & dicetak ulang oleh admin
            'kode_akses_plain' => $plaintext,
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
