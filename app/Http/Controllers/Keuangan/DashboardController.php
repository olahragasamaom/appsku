<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Paket;
use App\Models\PesertaPembayaran;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalPeserta = User::where('is_peserta', true)->count();
        $totalPaket = Paket::count();
        $pembayaranPending = PesertaPembayaran::where('status', 'pending')->count();
        $pembayaranVerified = PesertaPembayaran::where('status', 'verified')->count();

        return view('keuangan.dashboard', compact(
            'totalPeserta',
            'totalPaket',
            'pembayaranPending',
            'pembayaranVerified'
        ));
    }
}
