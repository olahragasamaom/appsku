<?php

namespace App\Http\Controllers\Pengawas;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\UjianPeserta;
use App\Services\UjianScoringService;
use Illuminate\View\View;

class HasilUjianController extends Controller
{
    public function __construct(private UjianScoringService $scoringService) {}

    public function index(): View
    {
        $ujians = Ujian::where('status', 'aktif')
            ->with(['peserta' => fn ($q) => $q->with('user')])
            ->orderByDesc('created_at')
            ->get();

        return view('pengawas.hasil-ujian.index', compact('ujians'));
    }

    public function show(Ujian $ujian): View
    {
        $ujian->load(['peserta' => fn ($q) => $q->with('user')]);

        $pesertaList = $ujian->peserta->map(function ($ujianPeserta) {
            $breakdown = $this->scoringService->breakdownPerSubJenis($ujianPeserta);

            return [
                'ujianPeserta' => $ujianPeserta,
                'user' => $ujianPeserta->user,
                'nilai_total' => $ujianPeserta->nilai_total ?? 0,
                'breakdown' => $breakdown,
            ];
        });

        return view('pengawas.hasil-ujian.show', compact('ujian', 'pesertaList'));
    }

    public function showPeserta(Ujian $ujian, UjianPeserta $ujianPeserta): View
    {
        $ujianPeserta->load(['user', 'ujianJawaban.soal']);
        $breakdown = $this->scoringService->breakdownPerSubJenis($ujianPeserta);

        return view('pengawas.hasil-ujian.peserta', compact('ujian', 'ujianPeserta', 'breakdown'));
    }
}
