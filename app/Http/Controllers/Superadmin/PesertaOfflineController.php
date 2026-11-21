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

    /**
     * METHOD: index()
     * ===============
     * URL Contoh: GET /superadmin/ujian/{ujian}/peserta-offline
     * 
     * Keterangan:
     * Menampilkan DAFTAR PESERTA OFFLINE yang terdaftar di ujian.
     * 
     * Tampilkan: nomor peserta, nama, status (belum ujian/sedang ujian/selesai),
     *            waktu mulai, waktu selesai, nilai, lulus/tidak.
     * 
     * Tombol aksi: Hapus, lihat hasil, lihat kartu peserta.
     * 
     * Form: Tidak ada GET saja
     * 
     * Debug Mode (uncomment untuk trace):
     * // dd($peserta->map(fn($p) => ['nomor' => $p->nomor_peserta, 'nama' => $p->nama_peserta])->toArray());
     */
    public function index(Ujian $ujian): View
    {
        $peserta = $ujian->pesertaOffline()->latest()->get();

        return view('superadmin.ujian.peserta-offline.index', compact('ujian', 'peserta'));
    }

    /**
     * METHOD: store()
     * ===============
     * URL Contoh: POST /superadmin/ujian/{ujian}/peserta-offline
     * 
     * Keterangan:
     * Menambahkan PESERTA OFFLINE BARU secara manual (one-by-one).
     * 
     * Alur (via OfflineParticipantService::create):
     * 1. Generate nomor_peserta auto-increment
     * 2. Hash password
     * 3. Buat PesertaOffline record
     * 4. Return kode akses (plain text, untuk ditampilkan ke user sekali)
     * 
     * Form: Modal tambah peserta di index view (POST method)
     *       Input: nama_peserta, password
     * 
     * Response: flash message + kode_akses & nomor_peserta (ditampilkan sekali saja)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Create peserta offline', ['ujian_id' => $ujian->id, 'nomor_peserta' => $result['peserta']->nomor_peserta]);
     */
    public function store(StorePesertaOfflineRequest $request, Ujian $ujian): RedirectResponse
    {
        $result = $this->offlineService->create($ujian, $request->validated());

        return back()
            ->with('success', 'Peserta offline berhasil ditambahkan.')
            ->with('kode_akses', $result['kode_akses'])
            ->with('nomor_peserta', $result['peserta']->nomor_peserta);
    }

    /**
     * METHOD: destroy()
     * =================
     * URL Contoh: DELETE /superadmin/ujian/{ujian}/peserta-offline/{pesertaOffline}
     * 
     * Keterangan:
     * Menghapus SATU peserta offline dari ujian.
     * Jika peserta sudah ada ujian attempt: result tetap disimpan (cascade tidak delete).
     * 
     * Route Model Binding: {pesertaOffline} -> PesertaOffline model
     * 
     * Form: Tombol delete per peserta (DELETE method)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Delete peserta offline '.$pesertaOffline->id.' ujian '.$ujian->id);
     */
    public function destroy(Ujian $ujian, PesertaOffline $pesertaOffline): RedirectResponse
    {
        abort_unless($pesertaOffline->ujian_id === $ujian->id, 404);

        $pesertaOffline->delete();

        return back()->with('success', 'Peserta offline berhasil dihapus.');
    }

    /**
     * METHOD: bulkDestroy()
     * =====================
     * URL Contoh: DELETE /superadmin/ujian/{ujian}/peserta-offline/bulk-destroy
     * 
     * Keterangan:
     * Menghapus MULTIPLE peserta offline sekaligus (bulk delete).
     * 
     * Validasi: ids array, tidak boleh kosong.
     * Efisien: query DELETE langsung (bukan loop individual delete).
     * 
     * Form: Checkbox list + tombol "Hapus Terpilih" (DELETE method)
     *       Input: ids[] (array of peserta ID)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Bulk delete peserta offline ujian '.$ujian->id, ['count' => $deleted]);
     */
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

    /**
     * METHOD: template()
     * ==================
     * URL Contoh: GET /superadmin/peserta-offline/template
     * 
     * Keterangan:
     * Download TEMPLATE EXCEL untuk bulk import peserta offline.
     * 
     * Template format:
     * - Kolom: nomor_peserta, nama_peserta, password, dll
     * - Contoh data: 001, "Andi Pratama", "password123"
     * 
     * Form: Tidak ada, link download di halaman import
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Download peserta offline template');
     */
    public function template(): BinaryFileResponse
    {
        return Excel::download(new PesertaOfflineTemplateExport, 'template_peserta_offline.xlsx');
    }

    /**
     * METHOD: importForm()
     * ====================
     * URL Contoh: GET /superadmin/ujian/{ujian}/peserta-offline/import-form
     * 
     * Keterangan:
     * Menampilkan FORM IMPORT peserta offline (upload Excel).
     * 
     * Guard: Hanya jika ujian tipe offline_kelas (abort 404 jika online).
     * 
     * Form: File upload input (POST ke import() method)
     *       Input: file (Excel format)
     * 
     * Debug Mode (uncomment untuk trace):
     * // dd($ujian->tipe_ujian);
     */
    public function importForm(Ujian $ujian): View
    {
        abort_unless($ujian->isOffline(), 404);

        return view('superadmin.ujian.peserta-offline.import', compact('ujian'));
    }

    /**
     * METHOD: import()
     * ================
     * URL Contoh: POST /superadmin/ujian/{ujian}/peserta-offline/import
     * 
     * Keterangan:
     * BULK IMPORT peserta offline dari file Excel.
     * 
     * Alur:
     * 1. Validasi file (.xlsx, .xls, .csv)
     * 2. Parse Excel rows via PesertaOfflineImport class
     * 3. Create PesertaOffline records per row (via OfflineParticipantService)
     * 4. Capture errors/skips, tampilkan summary
     * 
     * Guard: Hanya jika ujian tipe offline_kelas.
     * 
     * Form: File upload dari importForm() (POST method)
     *       Input: file (Excel format)
     * 
     * Response: Summary message + error list (jika ada)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Import peserta offline ujian '.$ujian->id, ['success' => $import->getSuccessCount(), 'skip' => $import->getSkipCount()]);
     */
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

    /**
     * METHOD: showPasswords()
     * =======================
     * URL Contoh: GET /superadmin/ujian/{ujian}/peserta-offline/passwords
     * 
     * Keterangan:
     * Menampilkan DAFTAR PASSWORD peserta offline (untuk cetak/distribusi).
     * 
     * WARNING: Screen ini sensitive! Batasi akses hanya ke admin/supervisor.
     * Password ditampilkan plain text karena untuk distribusi manual.
     * 
     * Paginasi 15 peserta per halaman, urut nomor_peserta.
     * 
     * Form: Tidak ada GET saja
     * 
     * Debug Mode (uncomment untuk trace):
     * // dd($peserta->map(fn($p) => ['nomor' => $p->nomor_peserta, 'nama' => $p->nama_peserta])->toArray());
     */
    public function showPasswords(Ujian $ujian): View
    {
        $peserta = $ujian->pesertaOffline()->orderBy('nomor_peserta')->paginate(15);

        return view('superadmin.ujian.peserta-offline.passwords', compact('ujian', 'peserta'));
    }

    /**
     * METHOD: export()
     * ================
     * URL Contoh: GET /superadmin/ujian/{ujian}/peserta-offline/export
     * 
     * Keterangan:
     * Download KARTU PESERTA (ID card) dalam format PDF.
     * Isi: nomor peserta, nama, password, ujian info, etc.
     * 
     * Cocok untuk dicetak & diberikan ke setiap peserta.
     * 
     * Form: Tidak ada, button download
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Export kartu peserta ujian '.$ujian->id);
     */
    public function export(Ujian $ujian): Response
    {
        $peserta = $ujian->pesertaOffline()->orderBy('nomor_peserta')->get();

        $pdf = Pdf::loadView('superadmin.ujian.peserta-offline.export', compact('ujian', 'peserta'));

        return $pdf->download('kartu-peserta-'.$ujian->id.'.pdf');
    }
}
