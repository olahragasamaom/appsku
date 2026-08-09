<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Soal;
use App\Models\Ujian;
use App\Models\UjianPeserta;
use App\Models\User;
use Illuminate\View\View;

class SuperadminDashboardController extends Controller
{
    public function index(): View
    {
        // 1. Statistik Utama
        $stats = [
            'total_soal' => Soal::count(),
            'ujian_aktif' => Ujian::where('status', 'aktif')->count(),
            'ujian_selesai' => Ujian::where('status', 'selesai')->count(),
            'total_peserta' => User::where('is_peserta', true)->count(),
        ];

        // 2. Jadwal Ujian Mendatang (Khusus Offline)
        $jadwalUjian = Ujian::where('tipe_ujian', 'offline_kelas')
            ->whereIn('status', ['draft', 'aktif'])
            ->whereNotNull('tanggal_ujian')
            ->where('tanggal_ujian', '>=', today())
            ->orderBy('tanggal_ujian')
            ->limit(5)
            ->get();

        // 3. Peserta dengan Nilai Tertinggi (Dari ujian yang sudah selesai)
        $topPeserta = UjianPeserta::with(['user', 'pesertaOffline', 'ujian'])
            ->where('status', 'selesai')
            ->whereNotNull('total_nilai')
            ->orderByDesc('total_nilai')
            ->limit(5)
            ->get();

        // 4. Aktivitas Ujian Terbaru (Ujian yang baru saja dikerjakan)
        $recentAttempts = UjianPeserta::with(['user', 'pesertaOffline', 'ujian'])
            ->orderByDesc('waktu_mulai')
            ->limit(5)
            ->get();

        return view('superadmin.dashboard', compact('stats', 'jadwalUjian', 'topPeserta', 'recentAttempts'));
    }
}
