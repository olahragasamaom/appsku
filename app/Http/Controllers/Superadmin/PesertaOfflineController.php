<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePesertaOfflineRequest;
use App\Models\PesertaOffline;
use App\Models\Ujian;
use App\Services\Ujian\OfflineParticipantService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PesertaOfflineController extends Controller
{
    public function __construct(
        private readonly OfflineParticipantService $offlineService
    ) {}

    public function index(Ujian $ujian): View
    {
        $peserta = $ujian->pesertaOffline()->latest()->get();

        return view('superadmin.ujian.peserta-offline.index', compact('ujian', 'peserta'));
    }

    public function store(StorePesertaOfflineRequest $request, Ujian $ujian): RedirectResponse
    {
        $result = $this->offlineService->create($ujian, $request->validated());

        return back()
            ->with('success', 'Peserta offline berhasil ditambahkan.')
            ->with('kode_akses', $result['kode_akses'])
            ->with('nomor_peserta', $result['peserta']->nomor_peserta);
    }

    public function destroy(Ujian $ujian, PesertaOffline $pesertaOffline): RedirectResponse
    {
        abort_unless($pesertaOffline->ujian_id === $ujian->id, 404);

        $pesertaOffline->delete();

        return back()->with('success', 'Peserta offline berhasil dihapus.');
    }

    public function export(Ujian $ujian): Response
    {
        $peserta = $ujian->pesertaOffline()->orderBy('nomor_peserta')->get();

        $pdf = Pdf::loadView('superadmin.ujian.peserta-offline.export', compact('ujian', 'peserta'));

        return $pdf->download('kartu-peserta-'.$ujian->id.'.pdf');
    }
}
