<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\PesertaOffline;
use App\Models\Ujian;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AbsensiController extends Controller
{
    /**
     * Index: Daftar ujian offline yang aktif hari ini.
     * Admin pilih ujian untuk melihat daftar peserta dan mengaktifkan mereka.
     */
    public function index(): View
    {
        $ujians = Ujian::where('tipe_ujian', 'offline_kelas')
            ->where('status', 'aktif')
            ->with('pesertaOffline')
            ->orderBy('tanggal_ujian', 'desc')
            ->get();

        return view('superadmin.absensi.index', compact('ujians'));
    }

    /**
     * Daftar peserta untuk ujian tertentu dengan tombol aktivasi.
     * Admin dapat mengaktifkan peserta sebelum mereka ikut ujian,
     * atau menonaktifkan jika ada keanehan selama ujian berlangsung.
     */
    public function activation(Ujian $ujian): View
    {
        abort_unless($ujian->isOffline() && $ujian->status === 'aktif', 404);

        $peserta = $ujian->pesertaOffline()
            ->with(['ujianPeserta' => function ($query) use ($ujian) {
                $query->where('ujian_id', $ujian->id);
            }])
            ->orderBy('nomor_peserta')
            ->paginate(20);

        return view('superadmin.absensi.activation', compact('ujian', 'peserta'));
    }

    /**
     * Toggle aktivasi 1 peserta.
     */
    public function toggleActivation(Ujian $ujian, PesertaOffline $pesertaOffline): RedirectResponse
    {
        $pivot = DB::table('panritta_ujian_peserta_offline')
            ->where('ujian_id', $ujian->id)
            ->where('peserta_offline_id', $pesertaOffline->id)
            ->first();

        abort_unless($pivot || $pesertaOffline->ujian_id === $ujian->id, 404);

        $newActive = $pivot ? ! $pivot->is_active : ! $pesertaOffline->is_active;

        DB::table('panritta_ujian_peserta_offline')->updateOrInsert(
            [
                'ujian_id' => $ujian->id,
                'peserta_offline_id' => $pesertaOffline->id,
            ],
            [
                'is_active' => $newActive,
                'updated_at' => now(),
            ]
        );

        if ($pesertaOffline->ujian_id === $ujian->id) {
            $pesertaOffline->update(['is_active' => $newActive]);
        }

        $status = $newActive ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Peserta {$pesertaOffline->nama_peserta} berhasil {$status}.");
    }

    /**
     * Bulk aktifkan/nonaktifkan multiple peserta.
     */
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

        $status = $isActive ? 'diaktifkan' : 'dinonaktifkan';
        $totalCount = count($validated['ids']);

        return back()->with('success', "{$totalCount} peserta berhasil {$status}.");
    }
}
