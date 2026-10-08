<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\PesertaOffline;
use App\Models\Ujian;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AbsensiController extends Controller
{
    public function index(): View
    {
        $ujians = Ujian::where('tipe_ujian', 'offline_kelas')
            ->where('status', 'aktif')
            ->with('pesertaOffline')
            ->orderBy('tanggal_ujian', 'desc')
            ->get();

        return view('superadmin.absensi.index', compact('ujians'));
    }

    public function activation(Ujian $ujian): View
    {
        abort_unless($ujian->isOffline() && $ujian->status === 'aktif', 404);

        $peserta = $ujian->pesertaOffline()->latest()->paginate(15);

        return view('superadmin.absensi.activation', compact('ujian', 'peserta'));
    }

    public function toggleActivation(Ujian $ujian, PesertaOffline $pesertaOffline): RedirectResponse
    {
        abort_unless($pesertaOffline->ujian_id === $ujian->id, 404);

        $pesertaOffline->update([
            'is_active' => ! $pesertaOffline->is_active,
        ]);

        $status = $pesertaOffline->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Peserta {$pesertaOffline->nama_peserta} berhasil {$status}.");
    }

    public function bulkToggleActivation(Request $request, Ujian $ujian): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
            'action' => ['required', 'in:activate,deactivate'],
        ], [
            'ids.required' => 'Pilih minimal satu peserta.',
        ]);

        $isActive = $validated['action'] === 'activate';
        $updated = $ujian->pesertaOffline()
            ->whereIn('id', $validated['ids'])
            ->update(['is_active' => $isActive]);

        $status = $isActive ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "{$updated} peserta berhasil {$status}.");
    }
}
