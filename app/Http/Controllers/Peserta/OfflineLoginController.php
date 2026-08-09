<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginPesertaOfflineRequest;
use App\Models\Ujian;
use App\Services\Ujian\AttemptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OfflineLoginController extends Controller
{
    public function __construct(
        private readonly AttemptService $attemptService
    ) {}

    public function show(Ujian $ujian): View
    {
        // Sembunyikan ujian non-offline (mis. ujian online) dengan 404.
        abort_unless($ujian->isOffline(), 404);

        // Jika ujian offline tapi belum aktif, tampilkan halaman info yang jelas
        // (bukan 404 yang membingungkan).
        if ($ujian->status !== 'aktif') {
            return view('peserta.ujian.offline.belum-aktif', compact('ujian'));
        }

        return view('peserta.ujian.offline.login', compact('ujian'));
    }

    public function login(LoginPesertaOfflineRequest $request, Ujian $ujian): RedirectResponse
    {
        abort_unless($ujian->isOffline(), 404);

        if ($ujian->status !== 'aktif') {
            return redirect()
                ->route('peserta.ujian.offline.login', $ujian)
                ->withErrors(['kode_akses' => 'Ujian ini belum dimulai atau belum diaktifkan oleh penyelenggara.']);
        }

        $attempt = $this->attemptService->startOffline(
            $request->input('nomor_peserta'),
            $request->input('kode_akses'),
            $ujian
        );

        $pesertaOffline = $ujian->pesertaOffline()
            ->where('nomor_peserta', $request->input('nomor_peserta'))
            ->first();

        $request->session()->put([
            'offline_peserta_id' => $pesertaOffline->id,
            'offline_ujian_id' => $ujian->id,
            'offline_attempt_id' => $attempt->id,
        ]);

        return redirect()->route('peserta.ujian.kerjakan', $ujian);
    }
}
