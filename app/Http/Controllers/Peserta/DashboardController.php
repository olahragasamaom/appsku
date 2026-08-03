<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\UjianPeserta;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $allocations = UjianPeserta::query()
            ->with('ujian')
            ->where('user_id', $user->id)
            ->whereHas('ujian', fn ($query) => $query->where('status', 'aktif'))
            ->get();

        $onlineUjians = Ujian::query()
            ->where('tipe_ujian', 'online_paket')
            ->where('status', 'aktif')
            ->whereDoesntHave('peserta', fn ($query) => $query->where('user_id', $user->id))
            ->get();

        $riwayat = UjianPeserta::query()
            ->with('ujian')
            ->where('user_id', $user->id)
            ->where('status', 'selesai')
            ->latest('waktu_selesai')
            ->get();

        return view('peserta.dashboard', compact('allocations', 'onlineUjians', 'riwayat'));
    }
}
