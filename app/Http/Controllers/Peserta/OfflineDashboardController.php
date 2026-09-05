<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\PesertaOffline;
use App\Models\Ujian;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * CONTROLLER: OfflineDashboardController
 * ======================================
 * Dashboard untuk peserta offline setelah login.
 * Menampilkan daftar ujian yang dialokasikan untuk peserta tersebut.
 */
class OfflineDashboardController extends Controller
{
    public function index(Request $request): View
    {
        // Ambil offline_peserta_id dari session
        $offlinePesertaId = $request->session()->get('offline_peserta_id');

        if (! $offlinePesertaId) {
            abort(403, 'Sesi tidak valid. Silakan login kembali.');
        }

        $pesertaOffline = PesertaOffline::with('ujian')->findOrFail($offlinePesertaId);

        // Ambil semua ujian yang dialokasikan untuk peserta ini
        // (berdasarkan ujian_id yang sama dengan yang digunakan saat registrasi)
        $ujians = Ujian::query()
            ->where('tipe_ujian', 'offline_kelas')
            ->where('status', 'aktif')
            ->where('id', $pesertaOffline->ujian_id)
            ->with('pesertaOffline')
            ->get();

        // Cek attempt untuk masing-masing ujian
        foreach ($ujians as $ujian) {
            $ujian->myAttempt = $ujian->peserta()
                ->where('id', $request->session()->get('offline_attempt_id'))
                ->first();
        }

        return view('peserta.ujian.offline.dashboard', compact('pesertaOffline', 'ujians'));
    }
}
