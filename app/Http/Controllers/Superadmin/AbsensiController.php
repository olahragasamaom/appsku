<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\PesertaOffline;
use App\Models\Ujian;
use App\Services\Ujian\OfflineParticipantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AbsensiController extends Controller
{
    public function __construct(
        private readonly OfflineParticipantService $participantService
    ) {}

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

    public function kehadiran(Ujian $ujian): View
    {
        abort_unless($ujian->isOffline() && $ujian->status === 'aktif', 404);

        $peserta = $ujian->pesertaOffline()
            ->with(['kehadiran' => function ($query) use ($ujian) {
                $query->where('ujian_id', $ujian->id);
            }])
            ->orderBy('nomor_peserta')
            ->paginate(15);

        return view('superadmin.absensi.kehadiran', compact('ujian', 'peserta'));
    }

    public function updateKehadiran(Request $request, Ujian $ujian, PesertaOffline $pesertaOffline): RedirectResponse
    {
        abort_unless($pesertaOffline->ujian_id === $ujian->id, 404);

        $validated = $request->validate([
            'status_kehadiran' => ['required', 'in:hadir,tidak_hadir'],
        ]);

        $this->participantService->markAttendance($pesertaOffline, $ujian, $validated['status_kehadiran']);

        $statusText = $validated['status_kehadiran'] === 'hadir' ? 'Hadir' : 'Tidak Hadir';

        return back()->with('success', "Kehadiran {$pesertaOffline->nama_peserta} diperbarui menjadi {$statusText}.");
    }

    public function bulkUpdateKehadiran(Request $request, Ujian $ujian): RedirectResponse
    {
        $validated = $request->validate([
            'peserta_ids' => ['required', 'array'],
            'peserta_ids.*' => ['integer'],
            'status_kehadiran' => ['required', 'in:hadir,tidak_hadir'],
        ], [
            'peserta_ids.required' => 'Pilih minimal satu peserta.',
        ]);

        $pesertaOffline = PesertaOffline::whereIn('id', $validated['peserta_ids'])
            ->where('ujian_id', $ujian->id)
            ->get();

        foreach ($pesertaOffline as $peserta) {
            $this->participantService->markAttendance($peserta, $ujian, $validated['status_kehadiran']);
        }

        $statusText = $validated['status_kehadiran'] === 'hadir' ? 'Hadir' : 'Tidak Hadir';

        return back()->with('success', count($validated['peserta_ids'])." peserta berhasil diupdate menjadi {$statusText}.");
    }
}
