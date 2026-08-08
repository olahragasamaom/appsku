<?php

namespace App\Imports;

use App\Models\Soal;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * IMPORT: SoalImport
 * ===================
 * Membaca file Excel/CSV berisi bank soal, lalu membuat record Soal baru
 * yang seluruhnya dikelompokkan ke SATU sub indikator tertentu.
 *
 * Format kolom header Excel yang dikenali (baris pertama):
 *   soal, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e,
 *   kunci_jawaban, nilai_bobot_benar,
 *   nilai_bobot_a, nilai_bobot_b, nilai_bobot_c, nilai_bobot_d, nilai_bobot_e,
 *   pembahasan
 *
 * Sub indikator TIDAK diambil dari file, melainkan ditentukan saat import
 * (karena admin mengunggah file per sub indikator dari halaman kelola soal).
 */
class SoalImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    protected int $successCount = 0;

    protected int $skipCount = 0;

    /** @var array<int, string> */
    protected array $errors = [];

    /** @var array<int, int> Menyimpan id soal yang berhasil dibuat, agar bisa dilampirkan ke ujian. */
    protected array $createdSoalIds = [];

    public function __construct(
        protected int $subIndikatorId,
        protected int $pembuatSoalId
    ) {}

    /**
     * @param  Collection<int, Collection<string, mixed>>  $rows
     */
    public function collection(Collection $rows): void
    {
        $currentRow = 1;

        foreach ($rows as $row) {
            $currentRow++;

            $teksSoal = trim((string) ($row['soal'] ?? ''));
            $opsiA = trim((string) ($row['opsi_a'] ?? ''));
            $opsiB = trim((string) ($row['opsi_b'] ?? ''));

            // Soal minimal harus punya teks soal dan dua opsi jawaban
            if ($teksSoal === '' || $opsiA === '' || $opsiB === '') {
                $this->skipCount++;
                $this->errors[] = "Baris {$currentRow}: Teks soal atau opsi jawaban kosong, dilewati.";

                continue;
            }

            $soal = Soal::create([
                'sub_indikator_id' => $this->subIndikatorId,
                'soal' => $teksSoal,
                'opsi_a' => $opsiA,
                'opsi_b' => $opsiB,
                // opsi_c & opsi_d wajib (NOT NULL) di database, default string kosong bila tidak ada
                'opsi_c' => trim((string) ($row['opsi_c'] ?? '')),
                'opsi_d' => trim((string) ($row['opsi_d'] ?? '')),
                // opsi_e boleh null
                'opsi_e' => trim((string) ($row['opsi_e'] ?? '')) ?: null,
                'kunci_jawaban' => $this->normalizeKunci($row['kunci_jawaban'] ?? null),
                'nilai_bobot_benar' => $this->numericOrNull($row['nilai_bobot_benar'] ?? null),
                'nilai_bobot_a' => $this->numericOrNull($row['nilai_bobot_a'] ?? null),
                'nilai_bobot_b' => $this->numericOrNull($row['nilai_bobot_b'] ?? null),
                'nilai_bobot_c' => $this->numericOrNull($row['nilai_bobot_c'] ?? null),
                'nilai_bobot_d' => $this->numericOrNull($row['nilai_bobot_d'] ?? null),
                'nilai_bobot_e' => $this->numericOrNull($row['nilai_bobot_e'] ?? null),
                'pembahasan' => trim((string) ($row['pembahasan'] ?? '')) ?: null,
                'pembuat_soal_id' => $this->pembuatSoalId,
            ]);

            $this->createdSoalIds[] = $soal->id;
            $this->successCount++;
        }
    }

    /**
     * Ubah kunci jawaban jadi huruf kapital A-E, atau null jika tidak valid.
     */
    protected function normalizeKunci(mixed $value): ?string
    {
        $kunci = strtoupper(trim((string) $value));

        return in_array($kunci, ['A', 'B', 'C', 'D', 'E'], true) ? $kunci : null;
    }

    /**
     * Kembalikan nilai numerik, atau null jika kosong/bukan angka.
     */
    protected function numericOrNull(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
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

    /** @return array<int, int> */
    public function getCreatedSoalIds(): array
    {
        return $this->createdSoalIds;
    }
}
