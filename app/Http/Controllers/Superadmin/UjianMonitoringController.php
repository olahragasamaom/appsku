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
        $peserta = $ujian->peserta()
            ->with('user')
            ->orderByDesc('total_nilai')
            ->orderBy('waktu_selesai')
            ->get()
            ->map(fn ($item, $index) => [
                'rank' => $index + 1,
                'nama' => $item->user?->name ?? '-',
                'username' => $item->user?->username ?? '-',
                'status' => $item->status,
                'total_nilai' => $item->total_nilai !== null ? (float) $item->total_nilai : null,
                'lulus' => $item->lulus,
            ]);

        return response()->json([
            'peserta' => $peserta,
            'updated_at' => now()->toDateTimeString(),
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
