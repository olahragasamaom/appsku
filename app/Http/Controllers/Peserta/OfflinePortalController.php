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
        // dan masih dalam rentang waktu akses (tanggal_ujian hingga batas_keterlambatan)
        $now = now();

        $ujians = Ujian::query()
            ->where('tipe_ujian', 'offline_kelas')
            ->where('status', 'aktif')
            ->where(function ($query) use ($now) {
                // Jika tanggal_ujian null, ujian bisa diakses kapan saja
                $query->whereNull('tanggal_ujian')
                    // ATAU jika sudah waktunya ujian
                    ->orWhere(function ($q) use ($now) {
                        $q->where('tanggal_ujian', '<=', $now)
                            // Dan belum lewat batas keterlambatan (atau tidak ada batas)
                            ->where(function ($q2) use ($now) {
                                $q2->whereNull('batas_keterlambatan')
                                    ->orWhere('batas_keterlambatan', '>=', $now);
                            });
                    });
            })
            ->orderBy('tanggal_ujian')
            ->get();

        return view('peserta.ujian.offline.portal', compact('ujians'));
    }
}
