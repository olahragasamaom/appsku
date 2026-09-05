<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MarkAttendanceRequest;
use App\Models\PesertaOffline;
use App\Models\Ujian;
use App\Services\Ujian\OfflineParticipantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PesertaOfflineKehadiranController extends Controller
{
    public function __construct(
        private readonly OfflineParticipantService $participantService
    ) {}

    public function index(Ujian $ujian): View
    {
        if ($ujian->tipe_ujian !== 'offline_kelas') {
            abort(404, 'Hanya untuk ujian offline.');
        }

        $pesertaOffline = PesertaOffline::where('ujian_id', $ujian->id)
            ->with(['kehadiran' => function ($query) use ($ujian) {
                $query->where('ujian_id', $ujian->id);
            }])
            ->orderBy('nomor_peserta')
            ->paginate(20);

        return view('superadmin.ujian.peserta-offline.kehadiran', compact('ujian', 'pesertaOffline'));
    }

    public function update(MarkAttendanceRequest $request, Ujian $ujian, PesertaOffline $pesertaOffline): RedirectResponse
    {
        $status = $request->validated()['status_kehadiran'];

        $this->participantService->markAttendance($pesertaOffline, $ujian, $status);

        return redirect()->route('superadmin.ujian.peserta-offline.kehadiran', $ujian->id)
            ->with('success', "Kehadiran peserta {$pesertaOffline->nomor_peserta} diperbarui.");
    }
}
