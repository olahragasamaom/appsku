<?php

namespace App\Services\Ujian;

use App\Models\Soal;
use App\Models\Ujian;
use App\Models\UjianPeserta;
use App\Models\UjianPesertaKategori;
use App\Services\OpsiRandomizer;
use Illuminate\Support\Collection;

class UjianScoringService
{
    /**
     * Calculate the score for a single answer based on the soal's grading system.
     * Handle randomize_opsi_tkp by reverse-mapping user display answer to original opsi.
     *
     * @return array{nilai: float, benar: bool|null}
     */
    public function scoreAnswer(Soal $soal, ?string $jawaban, ?Ujian $ujian = null, ?UjianPeserta $ujianPeserta = null): array
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

        $originalOpsiKey = $jawaban;
        if ($ujian && $ujianPeserta && $ujian->randomize_opsi_tkp) {
            $mapping = OpsiRandomizer::getMappingForPeserta(
                $ujian,
                $ujianPeserta,
                $soal->id
            );
            $originalOpsiKey = OpsiRandomizer::reverseMapDisplay($jawaban, $mapping);
        }

        $field = 'nilai_bobot_'.strtolower($originalOpsiKey);
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
        $peserta->loadMissing('ujian.ujianJenisUjians.jenisUjian', 'ujian.subJenisUjian', 'jawaban');

        $nilaiPerJenis = $peserta->jawaban
            ->groupBy('jenis_ujian_id')
            ->map(fn ($rows) => (float) $rows->sum('nilai'));

        return $peserta->ujian->ujianJenisUjians->map(function ($ujianJenis) use ($nilaiPerJenis, $peserta) {
            $nilai = $nilaiPerJenis->get($ujianJenis->jenis_ujian_id, 0.0);

            // Priority 1: Use passing grade from ujian_jenis_ujian (admin-set per exam)
            $passingGrade = $ujianJenis->passing_grade !== null
                ? (float) $ujianJenis->passing_grade
                : null;

            // Priority 2: Fallback ke sub jenis ujian passing_grade
            if ($passingGrade === null && $peserta->ujian->subJenisUjian && $peserta->ujian->subJenisUjian->passing_grade !== null) {
                $passingGrade = (float) $peserta->ujian->subJenisUjian->passing_grade;
            }

            // Priority 3: Auto-calculate jika masih null
            if ($passingGrade === null && $peserta->ujian->subJenisUjian) {
                $totalSoalKategori = $peserta->ujian->ujianSoals()
                    ->where('jenis_ujian_id', $ujianJenis->jenis_ujian_id)
                    ->count();

                // Default passing: 60% dari total nilai kategori
                if ($totalSoalKategori > 0) {
                    $nilaiPerSoal = $peserta->ujian->subJenisUjian->nilai_benar ?? 5;
                    $totalNilaiKategori = $totalSoalKategori * $nilaiPerSoal;
                    $passingGrade = (float) round($totalNilaiKategori * 0.6, 2);
                }
            }

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
     * Breakdown nilai per Sub Jenis Ujian (TWK, TIU, TKP, dll).
     * Mengelompokkan jawaban berdasarkan sub_jenis_ujian dari soal.
     *
     * @return array<int, array{sub_jenis_ujian_id: int, nama: string, nilai: float, nilai_maksimal: float, passing_grade: float|null, lulus: bool|null, pencapaian_persen: float}>
     */
    public function breakdownPerSubJenis(UjianPeserta $peserta): array
    {
        $peserta->loadMissing('jawaban.soal.subIndikator.subJenisUjian', 'ujian.ujianSoals.soal.subIndikator.subJenisUjian');

        // Build subJenisMap dari SEMUA soal di ujian (bukan dari jawaban saja)
        // Ini memastikan sub jenis tetap tampil meski peserta tidak menjawab satu soalpun
        $subJenisMap = [];
        $nilaiMaksimalPerSubJenis = [];

        foreach ($peserta->ujian->ujianSoals as $ujianSoal) {
            $subJenis = $ujianSoal->soal?->subIndikator?->subJenisUjian;
            if (! $subJenis) {
                continue;
            }

            $id = $subJenis->id;
            $subJenisMap[$id] = $subJenis;

            $sistem = $subJenis->sistem_penilaian ?? 'benar_salah';

            // Nilai maksimal per soal
            if ($sistem === 'benar_salah') {
                $nilaiMax = $ujianSoal->soal->nilai_bobot_benar
                    ?? $subJenis->nilai_benar
                    ?? 0;
            } else {
                // Tiap jawaban ada poin: ambil nilai bobot tertinggi dari opsi A-E
                $nilaiMax = max(
                    (float) ($ujianSoal->soal->nilai_bobot_a ?? 0),
                    (float) ($ujianSoal->soal->nilai_bobot_b ?? 0),
                    (float) ($ujianSoal->soal->nilai_bobot_c ?? 0),
                    (float) ($ujianSoal->soal->nilai_bobot_d ?? 0),
                    (float) ($ujianSoal->soal->nilai_bobot_e ?? 0),
                );
            }

            $nilaiMaksimalPerSubJenis[$id] = ($nilaiMaksimalPerSubJenis[$id] ?? 0) + (float) $nilaiMax;
        }

        // Kelompokkan nilai jawaban peserta berdasarkan sub_jenis_ujian_id
        $nilaiPerSubJenis = [];
        foreach ($peserta->jawaban as $jawaban) {
            $subJenis = $jawaban->soal?->subIndikator?->subJenisUjian;
            if (! $subJenis) {
                continue;
            }

            $id = $subJenis->id;
            $nilaiPerSubJenis[$id] = ($nilaiPerSubJenis[$id] ?? 0) + (float) $jawaban->nilai;
        }

        // Build hasil breakdown — semua sub jenis dari ujian ter-include
        $result = [];
        foreach ($subJenisMap as $id => $subJenis) {
            $nilai = $nilaiPerSubJenis[$id] ?? 0.0;
            $nilaiMaksimal = $nilaiMaksimalPerSubJenis[$id] ?? 0.0;
            $passingGrade = $subJenis->passing_grade !== null
                ? (float) $subJenis->passing_grade
                : null;

            $pencapaianPersen = $nilaiMaksimal > 0
                ? round(($nilai / $nilaiMaksimal) * 100, 1)
                : 0.0;

            $result[] = [
                'sub_jenis_ujian_id' => $id,
                'nama' => $subJenis->nama_sub_jenis_ujian ?? '-',
                'nilai' => $nilai,
                'nilai_maksimal' => $nilaiMaksimal,
                'passing_grade' => $passingGrade,
                'lulus' => $passingGrade === null ? null : $nilai >= $passingGrade,
                'pencapaian_persen' => $pencapaianPersen,
            ];
        }

        return $result;
    }

    /**
     * Breakdown nilai per Sub Indikator (misal: Pancasila, UUD 1945, Integritas, dll).
     * Mengelompokkan dan menghitung persentase penguasaan tiap sub indikator,
     * lalu diurutkan dari persentase tertinggi ke terendah.
     *
     * @return array<int, array{
     *     sub_indikator_id: int,
     *     nama: string,
     *     sub_jenis_id: int|null,
     *     sub_jenis_nama: string,
     *     total_soal: int,
     *     total_dijawab: int,
     *     total_benar: int,
     *     total_salah: int,
     *     nilai: float,
     *     nilai_maksimal: float,
     *     pencapaian_persen: float,
     *     is_kurang: bool,
     *     status: string,
     *     status_label: string
     * }>
     */
    public function breakdownPerSubIndikator(UjianPeserta $peserta): array
    {
        $peserta->loadMissing(
            'jawaban.soal.subIndikator.subJenisUjian',
            'ujian.ujianSoals.soal.subIndikator.subJenisUjian'
        );

        $subIndikatorMap = [];
        $jawabanMap = $peserta->jawaban->keyBy('ujian_soal_id');

        foreach ($peserta->ujian->ujianSoals as $ujianSoal) {
            $soal = $ujianSoal->soal;
            if (! $soal) {
                continue;
            }

            $subIndikator = $soal->subIndikator;
            $subJenis = $subIndikator?->subJenisUjian ?? $soal->subJenisUjian;
            $subIndikatorId = $subIndikator ? $subIndikator->id : 0;
            $subIndikatorNama = $subIndikator ? $subIndikator->nama_sub_indikator : 'Umum / Lainnya';
            $subJenisId = $subJenis ? $subJenis->id : null;
            $subJenisNama = $subJenis ? ($subJenis->nama_sub_jenis_ujian ?? '-') : '-';
            $sistem = $subJenis->sistem_penilaian ?? 'benar_salah';

            if (! isset($subIndikatorMap[$subIndikatorId])) {
                $subIndikatorMap[$subIndikatorId] = [
                    'sub_indikator_id' => $subIndikatorId,
                    'nama' => $subIndikatorNama,
                    'sub_jenis_id' => $subJenisId,
                    'sub_jenis_nama' => $subJenisNama,
                    'total_soal' => 0,
                    'total_dijawab' => 0,
                    'total_benar' => 0,
                    'total_salah' => 0,
                    'nilai' => 0.0,
                    'nilai_maksimal' => 0.0,
                ];
            }

            // Hitung nilai maksimal untuk soal ini
            if ($sistem === 'benar_salah') {
                $nilaiMaxSoal = (float) ($soal->nilai_bobot_benar ?? $subJenis?->nilai_benar ?? 5);
            } else {
                $nilaiMaxSoal = max(
                    (float) ($soal->nilai_bobot_a ?? 0),
                    (float) ($soal->nilai_bobot_b ?? 0),
                    (float) ($soal->nilai_bobot_c ?? 0),
                    (float) ($soal->nilai_bobot_d ?? 0),
                    (float) ($soal->nilai_bobot_e ?? 0),
                );
                if ($nilaiMaxSoal <= 0) {
                    $nilaiMaxSoal = 5.0;
                }
            }

            $subIndikatorMap[$subIndikatorId]['total_soal']++;
            $subIndikatorMap[$subIndikatorId]['nilai_maksimal'] += $nilaiMaxSoal;

            // Cek jawaban peserta
            $jwb = $jawabanMap->get($ujianSoal->id);
            if ($jwb && $jwb->jawaban !== null && $jwb->jawaban !== '') {
                $subIndikatorMap[$subIndikatorId]['total_dijawab']++;
                $subIndikatorMap[$subIndikatorId]['nilai'] += (float) $jwb->nilai;

                // Tentukan benar / salah
                if ($sistem === 'benar_salah') {
                    if ($jwb->benar || (float) $jwb->nilai >= $nilaiMaxSoal) {
                        $subIndikatorMap[$subIndikatorId]['total_benar']++;
                    } else {
                        $subIndikatorMap[$subIndikatorId]['total_salah']++;
                    }
                } else {
                    // Sistem poin (TKP): jika dapat nilai tertinggi dianggap sempurna/benar
                    if ((float) $jwb->nilai >= $nilaiMaxSoal) {
                        $subIndikatorMap[$subIndikatorId]['total_benar']++;
                    } else {
                        $subIndikatorMap[$subIndikatorId]['total_salah']++;
                    }
                }
            } else {
                // Tidak dijawab
                $subIndikatorMap[$subIndikatorId]['total_salah']++;
            }
        }

        // Kalkulasi persentase dan status evaluasi
        $result = [];
        foreach ($subIndikatorMap as $item) {
            $nilai = $item['nilai'];
            $nilaiMaksimal = $item['nilai_maksimal'];

            if ($nilaiMaksimal > 0) {
                $pencapaianPersen = round(($nilai / $nilaiMaksimal) * 100, 1);
            } elseif ($item['total_soal'] > 0) {
                $pencapaianPersen = round(($item['total_benar'] / $item['total_soal']) * 100, 1);
            } else {
                $pencapaianPersen = 0.0;
            }

            $isKurang = $pencapaianPersen < 60.0;

            if ($pencapaianPersen >= 80.0) {
                $status = 'baik';
                $statusLabel = 'Dikuasai / Tinggi';
            } elseif ($pencapaianPersen >= 60.0) {
                $status = 'cukup';
                $statusLabel = 'Cukup';
            } else {
                $status = 'kurang';
                $statusLabel = 'Perlu Evaluasi';
            }

            $result[] = [
                'sub_indikator_id' => $item['sub_indikator_id'],
                'nama' => $item['nama'],
                'sub_jenis_id' => $item['sub_jenis_id'],
                'sub_jenis_nama' => $item['sub_jenis_nama'],
                'total_soal' => $item['total_soal'],
                'total_dijawab' => $item['total_dijawab'],
                'total_benar' => $item['total_benar'],
                'total_salah' => $item['total_salah'],
                'nilai' => $nilai,
                'nilai_maksimal' => $nilaiMaksimal,
                'pencapaian_persen' => $pencapaianPersen,
                'is_kurang' => $isKurang,
                'status' => $status,
                'status_label' => $statusLabel,
            ];
        }

        // Urutkan dari tertinggi ke terendah berdasarkan pencapaian_persen
        usort($result, function ($a, $b) {
            if ($b['pencapaian_persen'] == $a['pencapaian_persen']) {
                return $b['total_soal'] <=> $a['total_soal'];
            }

            return $b['pencapaian_persen'] <=> $a['pencapaian_persen'];
        });

        return $result;
    }

    /**
     * Rebuild rankings for all finished peserta of an ujian (highest cumulative first).
     *
     * Urutan: nilai tertinggi lebih dulu; jika seri, penyelesaian tercepat menang.
     */
    public function rank(Ujian $ujian): Collection
    {
        return $ujian->peserta()
            ->with('user', 'pesertaOffline')
            ->where('status', 'selesai')
            ->orderByDesc('total_nilai')
            ->orderBy('waktu_selesai')
            ->get()
            ->values();
    }

    /**
     * Calculate ranking based on first attempt only (attempt 1 for each participant).
     * Only includes participants with status 'selesai'.
     * Returns ranking ordered by total_nilai DESC, then waktu_selesai ASC.
     */
    public function rankFirstAttemptOnly(Ujian $ujian): Collection
    {
        return \DB::table('panritta_ujian_peserta')
            ->select('panritta_ujian_peserta.*')
            ->where('panritta_ujian_peserta.ujian_id', $ujian->id)
            ->where('panritta_ujian_peserta.status', 'selesai')
            ->whereRaw(
                'panritta_ujian_peserta.id = (
                    SELECT MIN(id)
                    FROM panritta_ujian_peserta p2
                    WHERE p2.ujian_id = ? AND (
                        (p2.user_id = panritta_ujian_peserta.user_id AND p2.user_id IS NOT NULL) OR
                        (p2.peserta_offline_id = panritta_ujian_peserta.peserta_offline_id AND p2.peserta_offline_id IS NOT NULL)
                    )
                )',
                [$ujian->id]
            )
            ->orderByDesc('panritta_ujian_peserta.total_nilai')
            ->orderBy('panritta_ujian_peserta.waktu_selesai')
            ->get()
            ->values();
    }

    /**
     * Calculate ranking position for first attempt.
     * Only includes participants with status 'selesai' on their first attempt.
     *
     * @return array{rank: int|null, total: int}
     */
    public function positionOfFirstAttempt(UjianPeserta $peserta): array
    {
        $ranking = $this->rankFirstAttemptOnly($peserta->ujian);

        $index = $ranking->search(fn ($item) => $item->id === $peserta->id);

        return [
            'rank' => $index === false ? null : $index + 1,
            'total' => $ranking->count(),
        ];
    }

    /**
     * Ringkasan posisi seorang peserta di dalam papan peringkat sebuah ujian.
     *
     * @return array{rank: int|null, total: int}
     */
    public function positionOf(UjianPeserta $peserta): array
    {
        $ranking = $this->rank($peserta->ujian);

        $index = $ranking->search(fn (UjianPeserta $item) => $item->id === $peserta->id);

        return [
            'rank' => $index === false ? null : $index + 1,
            'total' => $ranking->count(),
        ];
    }
}
