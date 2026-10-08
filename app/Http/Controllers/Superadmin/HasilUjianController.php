<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\UjianJawaban;
use App\Models\UjianPeserta;
use App\Services\Ujian\UjianScoringService;
use Illuminate\View\View;

class HasilUjianController extends Controller
{
    public function __construct(
        private readonly UjianScoringService $scoring
    ) {}

    /**
     * Index: Daftar ujian aktif/berlangsung
     */
    public function index(): View
    {
        $ujians = Ujian::where('status', 'aktif')
            ->with(['subJenisUjian', 'peserta'])
            ->withCount([
                'peserta',
                'peserta as peserta_selesai_count' => fn ($q) => $q->where('status', 'selesai'),
                'peserta as peserta_sedang_count' => fn ($q) => $q->where('status', 'sedang_ujian'),
            ])
            ->orderBy('tanggal_ujian', 'desc')
            ->get();

        return view('superadmin.hasil-ujian.index', compact('ujians'));
    }

    /**
     * Show: Dashboard hasil ujian untuk 1 ujian
     */
    public function show(Ujian $ujian): View
    {
        $ujian->loadCount([
            'peserta',
            'peserta as peserta_selesai_count' => fn ($q) => $q->where('status', 'selesai'),
            'peserta as peserta_sedang_count' => fn ($q) => $q->where('status', 'sedang_ujian'),
        ]);

        // Hitung total peserta terdaftar (offline atau online)
        $totalRegistered = $ujian->pesertaOffline()->count();

        // Peserta belum ujian = terdaftar tapi belum punya UjianPeserta
        $pesertaBelumUjian = max(0, $totalRegistered - $ujian->peserta_count);

        return view('superadmin.hasil-ujian.show', compact(
            'ujian',
            'totalRegistered',
            'pesertaBelumUjian',
        ));
    }

    /**
     * Live Score (realtime scoring)
     */
    public function liveScore(Ujian $ujian): View
    {
        return view('superadmin.hasil-ujian.live-score', compact('ujian'));
    }

    /**
     * Final Score (ranking)
     */
    public function finalScore(Ujian $ujian): View
    {
        $ranking = $this->scoring->rank($ujian);

        return view('superadmin.hasil-ujian.final-score', compact('ujian', 'ranking'));
    }

    /**
     * Analisis Soal: statistik jawaban per soal
     */
    public function soalAnalysis(Ujian $ujian): View
    {
        $ujianSoals = $ujian->ujianSoals()
            ->with(['soal.subIndikator.subJenisUjian', 'jenisUjian'])
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        // Hitung statistik jawaban per soal
        $totalPeserta = $ujian->peserta()->where('status', 'selesai')->count();

        $statistics = [];
        foreach ($ujianSoals as $ujianSoal) {
            $jawabanCount = UjianJawaban::where('ujian_soal_id', $ujianSoal->id)
                ->whereHas('ujianPeserta', fn ($q) => $q->where('status', 'selesai'))
                ->selectRaw('jawaban, COUNT(*) as total, SUM(CASE WHEN benar = 1 THEN 1 ELSE 0 END) as benar_count')
                ->groupBy('jawaban')
                ->get()
                ->keyBy('jawaban');

            $totalJawab = $jawabanCount->sum('total');
            $totalBenar = $jawabanCount->sum('benar_count');
            $totalSalah = $totalJawab - $totalBenar;
            $belumJawab = max(0, $totalPeserta - $totalJawab);

            $statistics[$ujianSoal->id] = [
                'ujian_soal' => $ujianSoal,
                'soal' => $ujianSoal->soal,
                'total_jawab' => $totalJawab,
                'total_benar' => $totalBenar,
                'total_salah' => $totalSalah,
                'belum_jawab' => $belumJawab,
                'persen_benar' => $totalJawab > 0 ? round(($totalBenar / $totalJawab) * 100, 1) : 0,
                'distribusi' => $jawabanCount,
            ];
        }

        return view('superadmin.hasil-ujian.soal-analysis', compact('ujian', 'statistics', 'totalPeserta'));
    }

    /**
     * Daftar Peserta: dengan status (selesai, sedang_ujian, belum_ujian)
     */
    public function pesertaList(Ujian $ujian): View
    {
        // Peserta offline yang terdaftar
        $pesertaOfflineList = $ujian->pesertaOffline()
            ->with(['ujianPeserta' => function ($q) use ($ujian) {
                $q->where('ujian_id', $ujian->id);
            }])
            ->orderBy('nomor_peserta')
            ->get()
            ->map(function ($peserta) {
                $attempt = $peserta->ujianPeserta;

                $status = 'belum_ujian';
                $statusLabel = 'Belum Ujian';
                $statusColor = 'secondary';

                if ($attempt) {
                    if ($attempt->status === 'selesai') {
                        $status = 'selesai';
                        $statusLabel = 'Selesai';
                        $statusColor = 'success';
                    } elseif ($attempt->status === 'sedang_ujian') {
                        $status = 'sedang_ujian';
                        $statusLabel = 'Sedang Ujian';
                        $statusColor = 'warning';
                    }
                }

                return [
                    'id' => $peserta->id,
                    'nomor_peserta' => $peserta->nomor_peserta,
                    'nama_peserta' => $peserta->nama_peserta,
                    'is_active' => $peserta->is_active,
                    'attempt' => $attempt,
                    'status' => $status,
                    'status_label' => $statusLabel,
                    'status_color' => $statusColor,
                    'waktu_mulai' => $attempt?->waktu_mulai,
                    'waktu_selesai' => $attempt?->waktu_selesai,
                    'total_nilai' => $attempt?->total_nilai,
                    'lulus' => $attempt?->lulus,
                ];
            });

        return view('superadmin.hasil-ujian.peserta-list', compact('ujian', 'pesertaOfflineList'));
    }

    /**
     * Review Peserta: detail jawaban per soal
     */
    public function reviewPeserta(Ujian $ujian, UjianPeserta $peserta): View
    {
        abort_unless($peserta->ujian_id === $ujian->id, 404);

        $peserta->load('user', 'pesertaOffline', 'jawaban');

        $ujianSoals = $ujian->ujianSoals()
            ->with('soal.subIndikator.subJenisUjian', 'jenisUjian')
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        $jawabanMap = $peserta->jawaban->keyBy('ujian_soal_id');
        $breakdown = $this->scoring->breakdownPerJenis($peserta);

        return view('superadmin.hasil-ujian.review-peserta', compact(
            'ujian',
            'peserta',
            'ujianSoals',
            'jawabanMap',
            'breakdown',
        ));
    }

    /**
     * Force Finalize: untuk peserta yang stuck "sedang_ujian"
     */
    public function forceFinalize(Ujian $ujian, UjianPeserta $peserta): \Illuminate\Http\RedirectResponse
    {
        abort_unless($peserta->ujian_id === $ujian->id, 404);

        if ($peserta->status === 'selesai') {
            return back()->with('info', 'Peserta sudah selesai ujian.');
        }

        $this->scoring->finalize($peserta);

        $nama = $peserta->user?->name ?? $peserta->pesertaOffline?->nama_peserta ?? 'Peserta';

        return back()->with('success', "Ujian {$nama} berhasil difinalisasi.");
    }
}
