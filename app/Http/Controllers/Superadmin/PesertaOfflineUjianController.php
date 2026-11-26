<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PesertaOfflineUjianController extends Controller
{
    /**
     * Menampilkan daftar ujian untuk penambahan dan pengelolaan peserta offline.
     * Halaman ini berfokus khusus pada pengelolaan peserta, tanpa tombol edit, hapus, dan simulasi.
     */
    public function index(Request $request): View
    {
        $query = Ujian::with(['jenisUjians'])
            ->withCount(['pesertaOffline', 'ujianSoals']);

        if ($request->filled('search')) {
            $query->where('nama_ujian', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('tipe_ujian')) {
            $query->where('tipe_ujian', $request->tipe_ujian);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $ujians = $query->latest()->paginate(15)->withQueryString();

        return view('superadmin.peserta-offline-ujian.index', compact('ujians'));
    }
}
