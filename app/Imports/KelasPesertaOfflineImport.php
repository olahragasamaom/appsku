<?php

namespace App\Imports;

use App\Models\PesertaOffline;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KelasPesertaOfflineImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    protected int $successCount = 0;

    protected int $skipCount = 0;

    /** @var array<int, string> */
    protected array $errors = [];

    /** @var array<int, string> */
    protected array $existingNomor = [];

    public function __construct(
        private readonly ?int $kelasId,
        private readonly ?string $namaKelas
    ) {
        $this->existingNomor = PesertaOffline::pluck('nomor_peserta')
            ->map(fn (string $nomor): string => mb_strtolower(trim($nomor)))
            ->all();
    }

    /**
     * @param  Collection<int, Collection<string, mixed>>  $rows
     */
    public function collection(Collection $rows): void
    {
        $currentRow = 1;

        foreach ($rows as $row) {
            $currentRow++;

            // Tangani alias nama kolom header
            $nomor = trim((string) ($row['nomor_peserta'] ?? $row['no_peserta'] ?? $row['username'] ?? $row['nomor'] ?? ''));
            $nama = trim((string) ($row['nama_peserta'] ?? $row['nama'] ?? ''));
            $nomorHp = trim((string) ($row['no_hp'] ?? $row['nomor_hp'] ?? $row['telepon'] ?? $row['phone'] ?? $row['hp'] ?? ''));
            $password = trim((string) ($row['password'] ?? $row['kode_akses'] ?? $row['kode'] ?? ''));

            if ($nomor === '' || $nama === '') {
                $this->skipCount++;
                $this->errors[] = "Baris {$currentRow}: Nomor peserta atau nama peserta kosong, dilewati.";

                continue;
            }

            if (in_array(mb_strtolower($nomor), $this->existingNomor, true)) {
                $this->skipCount++;
                $this->errors[] = "Baris {$currentRow}: Nomor peserta '{$nomor}' sudah terdaftar, dilewati.";

                continue;
            }

            $plainPassword = $password !== '' ? $password : Str::upper(Str::random(6));

            PesertaOffline::create([
                'nomor_peserta' => $nomor,
                'nama_peserta' => $nama,
                'kelas_id' => $this->kelasId,
                'nama_kelas' => $this->namaKelas,
                'nomor_hp' => $nomorHp !== '' ? $nomorHp : null,
                'kode_akses' => Hash::make($plainPassword),
                'kode_akses_plain' => $plainPassword,
                'is_active' => false,
                'is_blocked' => false,
            ]);

            $this->existingNomor[] = mb_strtolower($nomor);
            $this->successCount++;
        }
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getSkipCount(): int
    {
        return $this->skipCount;
    }

    /** @return array<int, string> */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
