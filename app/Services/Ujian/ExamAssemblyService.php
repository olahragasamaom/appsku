<?php

namespace App\Services\Ujian;

use App\Models\Soal;
use App\Models\Ujian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ExamAssemblyService
{
    /**
     * Remaining question slots to fill for the exam.
     */
    public function remainingSlots(Ujian $ujian): int
    {
        return (int) $ujian->jumlah_soal - $ujian->ujianSoals()->count();
    }

    /**
     * Append questions for the given jenis ujian, honoring exam capacity.
     *
     * @param  array<int, int>  $soalIds
     */
    public function addQuestions(Ujian $ujian, int $jenisUjianId, array $soalIds): void
    {
        DB::transaction(function () use ($ujian, $jenisUjianId, $soalIds): void {
            $existing = $ujian->ujianSoals()->pluck('soal_id')->all();

            $newSoalIds = collect($soalIds)
                ->map(fn ($id): int => (int) $id)
                ->unique()
                ->reject(fn (int $id): bool => in_array($id, $existing, true))
                ->values();

            if ($newSoalIds->isEmpty()) {
                return;
            }

            $remaining = $this->remainingSlots($ujian);

            if ($newSoalIds->count() > $remaining) {
                throw ValidationException::withMessages([
                    'soal_id' => "Kapasitas ujian hanya menyisakan {$remaining} soal, tidak dapat menambahkan {$newSoalIds->count()} soal.",
                ]);
            }

            $urutan = (int) $ujian->ujianSoals()->max('urutan');

            foreach ($newSoalIds as $soalId) {
                $ujian->ujianSoals()->create([
                    'soal_id' => $soalId,
                    'jenis_ujian_id' => $jenisUjianId,
                    'urutan' => ++$urutan,
                ]);
            }
        });
    }

    /**
     * Detach a single question from the exam.
     */
    public function removeQuestion(Ujian $ujian, int $soalId): void
    {
        $ujian->ujianSoals()->where('soal_id', $soalId)->delete();
    }

    /**
     * Ensure the exam is ready to be activated.
     *
     * @throws ValidationException when capacity is unmet or the bank is short.
     */
    public function assertFinalizable(Ujian $ujian): void
    {
        $remaining = $this->remainingSlots($ujian);

        if ($remaining > 0) {
            throw ValidationException::withMessages([
                'status' => "Ujian belum lengkap, masih kurang {$remaining} soal dari {$ujian->jumlah_soal}.",
            ]);
        }

        $this->assertBankSufficient($ujian);
    }

    /**
     * Verify the available question bank covers the required capacity (R4).
     *
     * @throws ValidationException when the aggregate bank is short.
     */
    protected function assertBankSufficient(Ujian $ujian): void
    {
        $jenisUjianIds = $ujian->jenisUjians()->pluck('panritta_jenis_ujian.id')->all();

        if ($jenisUjianIds === []) {
            return;
        }

        $available = Soal::query()
            ->whereHas('subIndikator.subJenisUjian', fn ($query) => $query->whereIn('jenis_ujian_id', $jenisUjianIds))
            ->count();

        if ($available < (int) $ujian->jumlah_soal) {
            $deficit = (int) $ujian->jumlah_soal - $available;

            Validator::make([], [])->after(function ($validator) use ($deficit): void {
                $validator->errors()->add(
                    'status',
                    "Bank soal tidak mencukupi, kurang {$deficit} soal untuk memenuhi kapasitas ujian.",
                );
            })->validate();
        }
    }
}
