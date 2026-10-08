<?php

namespace App\Http\Controllers\Pengajar;

use App\Http\Controllers\Controller;
use App\Models\Soal;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $pengajar = auth()->user();

        $soalCount = Soal::where('created_by', $pengajar->id)->count();
        $recentSoal = Soal::where('created_by', $pengajar->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('pengajar.dashboard', compact('soalCount', 'recentSoal'));
    }
}
