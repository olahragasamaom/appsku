<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use Illuminate\View\View;

/**
 * CONTROLLER: OfflinePortalController
 * ====================================
 * Menampilkan daftar ujian offline yang berlangsung "hari ini".
 * Digunakan sebagai landing page bagi peserta offline di kelas/lokasi ujian.
 */
class OfflinePortalController extends Controller
{
    public function index(): View
    {
        // Ambil semua ujian tipe offline yang sedang aktif
        // dan tanggal ujiannya adalah hari ini
        $ujians = Ujian::query()
            ->where('tipe_ujian', 'offline_kelas')
            ->where('status', 'aktif')
            ->whereDate('tanggal_ujian', today())
            ->orderBy('tanggal_ujian')
            ->get();

        return view('peserta.ujian.offline.portal', compact('ujians'));
    }
}
