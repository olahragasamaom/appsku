<?php

namespace App\Http\Controllers\Superadmin;

use App\Exports\Templates\PesertaOfflineTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportPesertaOfflineRequest;
use App\Http\Requests\StorePesertaOfflineRequest;
use App\Imports\PesertaOfflineImport;
use App\Models\PesertaOffline;
use App\Models\Ujian;
use App\Services\Ujian\OfflineParticipantService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    public function bulkDestroy(Request $request, Ujian $ujian): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ], [
            'ids.required' => 'Pilih minimal satu peserta untuk dihapus.',
        ]);

        $deleted = $ujian->pesertaOffline()
            ->whereIn('id', $validated['ids'])
            ->delete();

        return back()->with('success', "{$deleted} peserta offline berhasil dihapus.");
    }

    public function template(): BinaryFileResponse
    {
        return Excel::download(new PesertaOfflineTemplateExport, 'template_peserta_offline.xlsx');
    }

    public function import(ImportPesertaOfflineRequest $request, Ujian $ujian): RedirectResponse
    {
        abort_unless($ujian->isOffline(), 404);

        try {
            $import = new PesertaOfflineImport($ujian, $this->offlineService);
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengimpor data: '.$e->getMessage());
        }

        $message = "Berhasil mengimpor {$import->getSuccessCount()} peserta.";

        if ($import->getSkipCount() > 0) {
            $message .= " {$import->getSkipCount()} data dilewati.";
        }

        $redirect = back()->with('success', $message);

        if ($import->getErrors() !== []) {
            $redirect->with('import_errors', $import->getErrors());
        }

        return $redirect;
    }

    public function export(Ujian $ujian): Response
    {
        $peserta = $ujian->pesertaOffline()->orderBy('nomor_peserta')->get();

        $pdf = Pdf::loadView('superadmin.ujian.peserta-offline.export', compact('ujian', 'peserta'));

        return $pdf->download('kartu-peserta-'.$ujian->id.'.pdf');
    }
}
