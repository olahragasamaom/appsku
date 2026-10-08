<?php

namespace App\Http\Controllers\Superadmin;

use App\Exports\UjianRankingExport;
use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Services\Ujian\UjianScoringService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UjianMonitoringController extends Controller
{
    public function __construct(
        private readonly UjianScoringService $scoring
    ) {}

    public function liveScoring(Ujian $ujian): View
    {
        return view('superadmin.ujian.monitoring.live', compact('ujian'));
    }

    public function liveData(Ujian $ujian): JsonResponse
    {
        // Calculate passing grade with priority logic
        $totalPassingGrade = (float) $ujian->ujianJenisUjians()->sum('passing_grade');

        // Priority 2: Fallback ke sub jenis ujian jika ada
        if ($totalPassingGrade === 0.0 && $ujian->subJenisUjian && $ujian->subJenisUjian->passing_grade !== null) {
            $totalPassingGrade = (float) $ujian->subJenisUjian->passing_grade;
        }

        // Priority 3: Auto-calculate jika masih 0
        if ($totalPassingGrade === 0.0 && $ujian->subJenisUjian) {
            $nilaiPerSoal = $ujian->subJenisUjian->nilai_benar ?? 5;
            $totalSoal = $ujian->ujianSoals()->count();
            if ($totalSoal > 0) {
                $totalNilai = $totalSoal * $nilaiPerSoal;
                $totalPassingGrade = (float) round($totalNilai * 0.6, 2); // Default 60%
            }
        }

        $peserta = $ujian->peserta()
            ->with('user', 'pesertaOffline', 'jawaban')
            ->get()
            ->map(function ($item) use ($totalPassingGrade) {
                // Hitung skor real-time dari jawaban yang sudah tersimpan
                $nilaiRealtime = $item->jawaban->sum('nilai');

                // Gunakan total_nilai jika sudah finalized (selesai),
                // atau gunakan skor real-time dari jawaban yang tersimpan
                $displayNilai = $item->total_nilai !== null ? (float) $item->total_nilai : (float) $nilaiRealtime;

                // Cek apakah nilai sudah mencapai passing grade
                $isPass = $displayNilai >= $totalPassingGrade;

                return [
                    'id' => $item->id,
                    'nama' => $item->user?->name ?? $item->pesertaOffline?->nama_peserta ?? 'Peserta #'.$item->id,
                    'username' => $item->user?->username ?? $item->pesertaOffline?->nomor_peserta ?? '-',
                    'status' => $item->status,
                    'total_nilai' => $displayNilai > 0 ? $displayNilai : null,
                    'lulus' => $item->lulus,
                    'passing_grade' => $totalPassingGrade,
                    'is_pass' => $isPass,
                ];
            })
            ->sortBy(fn ($item) => $item['is_pass'] ? 0 : 1)  // Pass first (true=0, false=1)
            ->sortByDesc('total_nilai')  // Then by value descending
            ->values()                    // Reset indices
            ->map(fn ($item, $index) => array_merge($item, ['rank' => $index + 1])); // Add rank

        return response()->json([
            'peserta' => $peserta,
            'updated_at' => now()->toDateTimeString(),
            'passing_grade' => $totalPassingGrade,
        ]);
    }

    public function ranking(Ujian $ujian): View
    {
        $ranking = $this->scoring->rank($ujian);

        return view('superadmin.ujian.monitoring.ranking', compact('ujian', 'ranking'));
    }

    public function exportRankingExcel(Ujian $ujian): BinaryFileResponse
    {
        return Excel::download(
            new UjianRankingExport($ujian),
            'ranking-'.str($ujian->nama_ujian)->slug().'.xlsx'
        );
    }

    public function exportRankingPdf(Ujian $ujian): Response
    {
        $ranking = $this->scoring->rank($ujian);

        $pdf = Pdf::loadView('superadmin.ujian.monitoring.ranking-pdf', compact('ujian', 'ranking'));

        return $pdf->download('ranking-'.str($ujian->nama_ujian)->slug().'.pdf');
    }

    public function review(Ujian $ujian, int $peserta): View
    {
        $ujianPeserta = $ujian->peserta()
            ->with('user', 'jawaban', 'pesertaOffline')
            ->findOrFail($peserta);

        $ujianSoals = $ujian->ujianSoals()
            ->with('soal.subIndikator.subJenisUjian', 'jenisUjian')
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        $jawabanMap = $ujianPeserta->jawaban->keyBy('ujian_soal_id');
        $breakdown = $this->scoring->breakdownPerJenis($ujianPeserta);

        return view('superadmin.ujian.monitoring.review', compact(
            'ujian',
            'ujianPeserta',
            'ujianSoals',
            'jawabanMap',
            'breakdown',
        ));
    }

    /**
     * SIMULASI UJIAN FULL (Khusus Superadmin)
     * Mensimulasikan halaman pengerjaan ujian secara penuh di memori browser.
     */
    public function simulasi(Ujian $ujian): View
    {
        $ujianSoals = $ujian->ujianSoals()
            ->with('soal.subIndikator.subJenisUjian')
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        // Acak soal jika ujian diset acak
        if ($ujian->acak_soal) {
            $ujianSoals = $ujianSoals->shuffle();
        }

        return view('superadmin.ujian.monitoring.simulasi', compact('ujian', 'ujianSoals'));
    }
}
