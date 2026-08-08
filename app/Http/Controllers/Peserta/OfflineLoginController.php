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
        abort_unless($ujian->isOffline() && $ujian->status === 'aktif', 404);

        return view('peserta.ujian.offline.login', compact('ujian'));
    }

    public function login(LoginPesertaOfflineRequest $request, Ujian $ujian): RedirectResponse
    {
        abort_unless($ujian->isOffline() && $ujian->status === 'aktif', 404);

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
