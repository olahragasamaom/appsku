<?php

namespace App\Http\Controllers\PembuatSoal;

use App\Http\Controllers\Controller;
use App\Models\Soal;
use App\Models\Ujian;
use App\Models\UjianPeserta;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUjian = Ujian::count();
        $totalSoal = Soal::count();
        $totalPeserta = UjianPeserta::count();
        $ujianAktif = Ujian::where('status', 'aktif')->count();

        return view('pembuat-soal.dashboard', compact(
            'totalUjian',
            'totalSoal',
            'totalPeserta',
            'ujianAktif'
        ));
    }
}
