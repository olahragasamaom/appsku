<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\PesertaOffline;
use App\Models\PesertaOfflineKehadiran;
use App\Models\Ujian;
use App\Services\Ujian\OfflineParticipantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * CONTROLLER: PesertaOfflineAssignController
 * ===========================================
 * Manajemen assign peserta offline ke multiple ujian.
 * Support: assign existing peserta, copy dari ujian lain, unassign.
 */
class PesertaOfflineAssignController extends Controller
{
    public function __construct(
        private readonly OfflineParticipantService $participantService
    ) {}

    /**
     * Halaman assign peserta - list peserta yang bisa di-assign.
     */
    public function index(Ujian $ujian): View
    {
        if ($ujian->tipe_ujian !== 'offline_kelas') {
            abort(404, 'Hanya untuk ujian offline.');
        }

        // Peserta yang sudah assigned ke ujian ini (via kehadiran)
        $assignedPesertaIds = PesertaOfflineKehadiran::where('ujian_id', $ujian->id)
            ->pluck('peserta_offline_id')
            ->toArray();

        $assignedPeserta = PesertaOffline::whereIn('id', $assignedPesertaIds)
            ->with(['ujian' => function ($query) {
                $query->select('id', 'nama_ujian');
            }])
            ->orderBy('nomor_peserta')
            ->get();

        // Peserta yang belum assigned ke ujian ini (dari ujian offline lain)
        $availablePeserta = PesertaOffline::whereNotIn('id', $assignedPesertaIds)
            ->whereHas('ujian', function ($query) use ($ujian) {
                $query->where('tipe_ujian', 'offline_kelas')
                    ->where('id', '!=', $ujian->id);
            })
            ->with(['ujian' => function ($query) {
                $query->select('id', 'nama_ujian');
            }])
            ->orderBy('nomor_peserta')
            ->get();

        // List ujian offline lain untuk copy source
        $sourceUjianList = Ujian::where('tipe_ujian', 'offline_kelas')
            ->where('id', '!=', $ujian->id)
            ->withCount('pesertaOffline')
            ->orderBy('nama_ujian')
            ->get();

        return view('superadmin.ujian.peserta-offline.assign', compact('ujian', 'assignedPeserta', 'availablePeserta', 'sourceUjianList'));
    }

    /**
     * Assign peserta existing (dipilih via checkbox) ke ujian ini.
     */
    public function assign(Request $request, Ujian $ujian): RedirectResponse
    {
        $validated = $request->validate([
            'peserta_ids' => ['required', 'array', 'min:1'],
            'peserta_ids.*' => ['integer', 'exists:panritta_peserta_offline,id'],
        ]);

        $count = 0;
        foreach ($validated['peserta_ids'] as $pesertaId) {
            $peserta = PesertaOffline::find($pesertaId);
            if ($peserta) {
                $this->participantService->assignToUjian($peserta, $ujian);
                $count++;
            }
        }

        return redirect()->route('superadmin.ujian.peserta-offline.assign.index', $ujian->id)
            ->with('success', "{$count} peserta berhasil di-assign ke ujian ini.");
    }

    /**
     * Unassign peserta dari ujian (hapus kehadiran, bukan peserta).
     */
    public function unassign(Ujian $ujian, PesertaOffline $pesertaOffline): RedirectResponse
    {
        $this->participantService->unassignFromUjian($pesertaOffline, $ujian);

        return redirect()->route('superadmin.ujian.peserta-offline.assign.index', $ujian->id)
            ->with('success', "Peserta {$pesertaOffline->nomor_peserta} berhasil dilepas dari ujian ini.");
    }

    /**
     * Copy semua peserta dari ujian lain ke ujian ini.
     */
    public function copyFromUjian(Request $request, Ujian $ujian): RedirectResponse
    {
        $validated = $request->validate([
            'source_ujian_id' => ['required', 'integer', 'exists:panritta_ujian,id'],
        ]);

        $sourceUjian = Ujian::findOrFail($validated['source_ujian_id']);

        if ($sourceUjian->tipe_ujian !== 'offline_kelas') {
            return redirect()->route('superadmin.ujian.peserta-offline.assign.index', $ujian->id)
                ->with('error', 'Ujian sumber harus tipe offline.');
        }

        $count = $this->participantService->copyPesertaFromUjian($sourceUjian, $ujian);

        return redirect()->route('superadmin.ujian.peserta-offline.assign.index', $ujian->id)
            ->with('success', "{$count} peserta berhasil di-copy dari ujian '{$sourceUjian->nama_ujian}'.");
    }
}
