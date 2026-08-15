<?php

namespace App\Imports;

use App\Models\Ujian;
use App\Services\Ujian\OfflineParticipantService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PesertaOfflineImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    protected int $successCount = 0;

    protected int $skipCount = 0;

    /** @var array<int, string> */
    protected array $errors = [];

    /** @var array<int, string> lowercased nomor_peserta already used in this exam */
    protected array $existingNomor = [];

    public function __construct(
        private readonly Ujian $ujian,
        private readonly OfflineParticipantService $offlineService
    ) {
        $this->existingNomor = $ujian->pesertaOffline()
            ->pluck('nomor_peserta')
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

            $nomor = trim((string) ($row['nomor_peserta'] ?? ''));
            $nama = trim((string) ($row['nama_peserta'] ?? ''));

            if ($nomor === '' || $nama === '') {
                $this->skipCount++;
                $this->errors[] = "Baris {$currentRow}: Nomor peserta atau nama peserta kosong, dilewati.";

                continue;
            }

            if (in_array(mb_strtolower($nomor), $this->existingNomor, true)) {
                $this->skipCount++;
                $this->errors[] = "Baris {$currentRow}: Nomor peserta '{$nomor}' sudah ada, dilewati.";

                continue;
            }

            $this->offlineService->create($this->ujian, [
                'nomor_peserta' => $nomor,
                'nama_peserta' => $nama,
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
