<?php

namespace App\Http\Controllers\Pengawas;

use App\Http\Controllers\Controller;
use App\Models\PesertaOffline;
use App\Models\Ujian;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AbsensiController extends Controller
{
    public function index(): View
    {
        $ujians = Ujian::where('status', 'aktif')
            ->with('pesertaOffline')
            ->orderByDesc('created_at')
            ->get();

        return view('pengawas.absensi.index', compact('ujians'));
    }

    public function activation(Ujian $ujian): View
    {
        abort_unless($ujian->isOffline() && $ujian->status === 'aktif', 404);

        $peserta = $ujian->pesertaOffline()
            ->with(['ujianPeserta' => function ($query) use ($ujian) {
                $query->where('ujian_id', $ujian->id);
            }])
            ->orderBy('nomor_peserta')
            ->paginate(20);

        return view('pengawas.absensi.activation', compact('ujian', 'peserta'));
    }

    public function toggleActivation(Ujian $ujian, PesertaOffline $pesertaOffline): RedirectResponse
    {
        $pivot = DB::table('panritta_ujian_peserta_offline')
            ->where('ujian_id', $ujian->id)
            ->where('peserta_offline_id', $pesertaOffline->id)
            ->first();

        abort_unless($pivot || $pesertaOffline->ujian_id === $ujian->id, 404);

        $newActive = $pivot ? ! $pivot->is_active : ! $pesertaOffline->is_active;

        DB::table('panritta_ujian_peserta_offline')
            ->where('ujian_id', $ujian->id)
            ->where('peserta_offline_id', $pesertaOffline->id)
            ->update(['is_active' => $newActive]);

        if ($pesertaOffline->ujian_id === $ujian->id) {
            $pesertaOffline->update(['is_active' => $newActive]);
        }

        $status = $newActive ? 'diaktifkan' : 'dinonaktifkan';

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

        $existingPivotIds = DB::table('panritta_ujian_peserta_offline')
            ->where('ujian_id', $ujian->id)
            ->whereIn('peserta_offline_id', $validated['ids'])
            ->pluck('peserta_offline_id')
            ->all();

        DB::table('panritta_ujian_peserta_offline')
            ->where('ujian_id', $ujian->id)
            ->whereIn('peserta_offline_id', $validated['ids'])
            ->update([
                'is_active' => $isActive,
                'updated_at' => now(),
            ]);

        $missingIds = array_diff($validated['ids'], $existingPivotIds);
        if (! empty($missingIds)) {
            $insertData = array_map(fn ($id) => [
                'ujian_id' => $ujian->id,
                'peserta_offline_id' => $id,
                'is_active' => $isActive,
                'created_at' => now(),
                'updated_at' => now(),
            ], $missingIds);
            DB::table('panritta_ujian_peserta_offline')->insert($insertData);
        }

        PesertaOffline::where('ujian_id', $ujian->id)
            ->whereIn('id', $validated['ids'])
            ->update(['is_active' => $isActive]);

        $actionLabel = $isActive ? 'diaktifkan' : 'dinonaktifkan';
        $totalCount = count($validated['ids']);

        return back()->with('success', "{$totalCount} peserta berhasil {$actionLabel}.");
    }
}
