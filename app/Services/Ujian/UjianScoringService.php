<?php

namespace App\Services\Ujian;

use App\Models\Soal;
use App\Models\Ujian;
use App\Models\UjianPeserta;
use App\Models\UjianPesertaKategori;

class UjianScoringService
{
    /**
     * Calculate the score for a single answer based on the soal's grading system.
     *
     * @return array{nilai: float, benar: bool|null}
     */
    public function scoreAnswer(Soal $soal, ?string $jawaban): array
    {
        $sistem = $soal->subIndikator?->subJenisUjian?->sistem_penilaian ?? 'benar_salah';

        if ($jawaban === null) {
            return ['nilai' => 0.0, 'benar' => $sistem === 'benar_salah' ? false : null];
        }

        if ($sistem === 'benar_salah') {
            $benar = $soal->kunci_jawaban !== null && $soal->kunci_jawaban === $jawaban;

            $nilaiBenar = $soal->nilai_bobot_benar
                ?? $soal->subIndikator?->subJenisUjian?->nilai_benar
                ?? 0;

            return [
                'nilai' => $benar ? (float) $nilaiBenar : 0.0,
                'benar' => $benar,
            ];
        }

        $field = 'nilai_bobot_'.strtolower($jawaban);
        $nilai = (float) ($soal->{$field} ?? 0);

        return ['nilai' => $nilai, 'benar' => null];
    }

    /**
     * Finalize a peserta's attempt: persist per-category scores then evaluate pass/fail.
     */
    public function finalize(UjianPeserta $peserta): void
    {
        $this->aggregateCategories($peserta);
        $this->evaluatePass($peserta);

        $peserta->forceFill([
            'status' => 'selesai',
            'waktu_selesai' => $peserta->waktu_selesai ?? now(),
        ])->save();
    }

    /**
     * Upsert per-category scores summed from the peserta's answers (AD-4). Idempotent.
     */
    public function aggregateCategories(UjianPeserta $peserta): void
    {
        $peserta->loadMissing('ujian.ujianJenisUjians', 'jawaban');

        $nilaiPerJenis = $peserta->jawaban
            ->groupBy('jenis_ujian_id')
            ->map(fn ($rows) => (float) $rows->sum('nilai'));

        foreach ($peserta->ujian->ujianJenisUjians as $ujianJenis) {
            $nilai = $nilaiPerJenis->get($ujianJenis->jenis_ujian_id, 0.0);

            UjianPesertaKategori::updateOrCreate(
                [
                    'ujian_peserta_id' => $peserta->id,
                    'jenis_ujian_id' => $ujianJenis->jenis_ujian_id,
                ],
                [
                    'nilai_kategori' => $nilai,
                    'passing_grade' => $ujianJenis->passing_grade,
                ],
            );
        }

        $peserta->load('kategori');
    }

    /**
     * Evaluate pass/fail per category and set the overall lulus flag (AD-4).
     */
    public function evaluatePass(UjianPeserta $peserta): void
    {
        $peserta->loadMissing('kategori');

        $lulus = true;
        $totalNilai = 0.0;

        foreach ($peserta->kategori as $kategori) {
            $nilai = (float) $kategori->nilai_kategori;
            $totalNilai += $nilai;

            $passingGrade = $kategori->passing_grade;

            if ($passingGrade === null) {
                $lulusKategori = null;
            } else {
                $lulusKategori = $nilai >= (float) $passingGrade;

                if (! $lulusKategori) {
                    $lulus = false;
                }
            }

            $kategori->forceFill(['lulus_kategori' => $lulusKategori])->save();
        }

        $peserta->forceFill([
            'total_nilai' => $totalNilai,
            'lulus' => $lulus,
        ])->save();
    }

    /**
     * @return array<int, array{jenis_ujian_id: int, nama: string, nilai: float, passing_grade: float|null, lulus: bool|null}>
     */
    public function breakdownPerJenis(UjianPeserta $peserta): array
    {
        $peserta->loadMissing('ujian.ujianJenisUjians.jenisUjian', 'jawaban');

        $nilaiPerJenis = $peserta->jawaban
            ->groupBy('jenis_ujian_id')
            ->map(fn ($rows) => (float) $rows->sum('nilai'));

        return $peserta->ujian->ujianJenisUjians->map(function ($ujianJenis) use ($nilaiPerJenis) {
            $nilai = $nilaiPerJenis->get($ujianJenis->jenis_ujian_id, 0.0);
            $passingGrade = $ujianJenis->passing_grade !== null ? (float) $ujianJenis->passing_grade : null;

            return [
                'jenis_ujian_id' => $ujianJenis->jenis_ujian_id,
                'nama' => $ujianJenis->jenisUjian?->nama_jenis_ujian ?? '-',
                'nilai' => $nilai,
                'passing_grade' => $passingGrade,
                'lulus' => $passingGrade === null ? null : $nilai >= $passingGrade,
            ];
        })->all();
    }

    /**
     * Rebuild rankings for all finished peserta of an ujian (highest cumulative first).
     */
    public function rank(Ujian $ujian): \Illuminate\Support\Collection
    {
        return $ujian->peserta()
            ->with('user', 'pesertaOffline')
            ->orderByDesc('total_nilai')
            ->get()
            ->values();
    }
}
