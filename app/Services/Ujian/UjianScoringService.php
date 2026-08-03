<?php

namespace App\Services\Ujian;

use App\Models\Soal;
use App\Models\Ujian;
use App\Models\UjianPeserta;

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
     * Finalize a peserta's attempt: recompute totals and pass/fail per jenis ujian.
     */
    public function finalize(UjianPeserta $peserta): void
    {
        $peserta->loadMissing('ujian.ujianJenisUjians', 'jawaban');

        $ujian = $peserta->ujian;
        $totalNilai = (float) $peserta->jawaban->sum('nilai');

        $nilaiPerJenis = $peserta->jawaban
            ->groupBy('jenis_ujian_id')
            ->map(fn ($rows) => (float) $rows->sum('nilai'));

        $lulus = true;

        foreach ($ujian->ujianJenisUjians as $ujianJenis) {
            $passingGrade = $ujianJenis->passing_grade;

            if ($passingGrade === null) {
                continue;
            }

            $nilai = $nilaiPerJenis->get($ujianJenis->jenis_ujian_id, 0.0);

            if ($nilai < (float) $passingGrade) {
                $lulus = false;
                break;
            }
        }

        $peserta->forceFill([
            'total_nilai' => $totalNilai,
            'lulus' => $lulus,
            'status' => 'selesai',
            'waktu_selesai' => $peserta->waktu_selesai ?? now(),
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
            ->with('user')
            ->orderByDesc('total_nilai')
            ->get()
            ->values();
    }
}
