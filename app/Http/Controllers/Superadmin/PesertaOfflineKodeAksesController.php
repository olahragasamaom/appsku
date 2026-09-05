<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\PesertaOffline;
use App\Models\Ujian;
use App\Services\Ujian\OfflineParticipantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * CONTROLLER: PesertaOfflineKodeAksesController
 * ==============================================
 * Manajemen reset kode akses peserta offline.
 * Support reset individual dan bulk reset.
 */
class PesertaOfflineKodeAksesController extends Controller
{
    public function __construct(
        private readonly OfflineParticipantService $participantService
    ) {}

    /**
     * Reset kode akses untuk satu peserta.
     */
    public function reset(Request $request, Ujian $ujian, PesertaOffline $pesertaOffline): RedirectResponse
    {
        $newKode = $this->participantService->resetKodeAkses($pesertaOffline, $request->user()->id);

        return redirect()->route('superadmin.ujian.peserta-offline.index', $ujian->id)
            ->with('kode_akses', $newKode)
            ->with('nomor_peserta', $pesertaOffline->nomor_peserta)
            ->with('success', "Kode akses untuk {$pesertaOffline->nomor_peserta} berhasil di-reset.");
    }

    /**
     * Bulk reset kode akses semua peserta di ujian.
     * Redirect ke halaman print kartu dengan data baru.
     */
    public function bulkReset(Request $request, Ujian $ujian): RedirectResponse
    {
        $count = PesertaOffline::where('ujian_id', $ujian->id)->count();

        if ($count === 0) {
            return redirect()->route('superadmin.ujian.peserta-offline.index', $ujian->id)
                ->with('error', 'Belum ada peserta untuk di-reset.');
        }

        $this->participantService->bulkResetKodeAkses($ujian, $request->user()->id);

        return redirect()->route('superadmin.ujian.peserta-offline.export', $ujian->id)
            ->with('success', "Kode akses untuk {$count} peserta berhasil di-reset. Silakan cetak kartu peserta.");
    }
}
