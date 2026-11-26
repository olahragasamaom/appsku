<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Imports\SoalImport;
use App\Models\JenisUjian;
use App\Models\Soal;
use App\Models\SubIndikator;
use App\Models\Ujian;
use App\Models\UjianSoal;
use App\Services\Ujian\ExamAssemblyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class UjianSoalController extends Controller
{
    public function __construct(
        private readonly ExamAssemblyService $assemblyService
    ) {}

    /**
     * METHOD: index()
     * ===============
     * URL Contoh: GET /superadmin/ujian/{ujian}/soal (misal: /superadmin/ujian/5/soal)
     * 
     * Keterangan:
     * Menampilkan HALAMAN KELOLA SOAL untuk ujian tertentu.
     * 
     * Tab navigasi: Per Jenis Ujian (TWK, TIU, TKP)
     * Konten: Dua view - flat (semua soal) & per grup (group by sub indikator)
     * 
     * Tombol aksi:
     * - Tambah Soal Baru (new soal + attach)
     * - Tambah dari Bank Soal (search existing soal)
     * - Import dari Excel
     * - Lanjut ke Finalisasi
     * 
     * Form: Tidak ada GET saja (tab selection via jenis_ujian_id)
     * 
     * Debug Mode (uncomment untuk trace):
     * // dd(['ujian' => $ujian->nama_ujian, 'totalSoal' => $totalSoal, 'perSubIndikator' => $jumlahSoalPerSubIndikator->toArray()]);
     */
    public function index(Request $request, Ujian $ujian): View
    {
        $ujian->load('ujianJenisUjians.jenisUjian');

        $jenisUjians = $ujian->ujianJenisUjians
            ->map(fn ($item) => $item->jenisUjian)
            ->filter()
            ->unique('id')
            ->values();

        $activeJenisId = (int) $request->input('jenis_ujian_id', $jenisUjians->first()?->id);

        // Semua soal yang sudah masuk ke ujian pada jenis ujian aktif
        $ujianSoals = $ujian->ujianSoals()
            ->with('soal.subIndikator.subJenisUjian')
            ->when($activeJenisId, fn ($query) => $query->where('jenis_ujian_id', $activeJenisId))
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        // Hitung berapa soal yang sudah masuk untuk tiap sub indikator (dipakai di badge tombol)
        $jumlahSoalPerSubIndikator = $ujianSoals
            ->groupBy(fn ($ujianSoal) => $ujianSoal->soal?->sub_indikator_id)
            ->map(fn ($items) => $items->count());

        // Ambil struktur sub jenis + sub indikator dari jenis ujian aktif
        $subIndikatorGroups = collect();
        if ($activeJenisId) {
            $subIndikatorGroups = JenisUjian::with(['subJenisUjian.subIndikator'])
                ->find($activeJenisId)
                ?->subJenisUjian ?? collect();
        }

        // Kelompokkan soal ujian per sub indikator (untuk tampilan Tab 2)
        $ujianSoalsPerSubIndikator = $ujianSoals
            ->groupBy(fn ($ujianSoal) => $ujianSoal->soal?->subIndikator?->nama_sub_indikator ?? 'Tanpa Sub Indikator');

        $totalSoal = $ujian->ujianSoals()->count();

        return view('superadmin.ujian.soal.index', compact(
            'ujian',
            'jenisUjians',
            'activeJenisId',
            'ujianSoals',
            'ujianSoalsPerSubIndikator',
            'subIndikatorGroups',
            'jumlahSoalPerSubIndikator',
            'totalSoal',
        ));
    }

    /**
     * METHOD: importExcel()
     * ====================
     * URL Contoh: POST /superadmin/ujian/{ujian}/soal/import-excel
     * 
     * Keterangan:
     * BULK IMPORT SOAL dari file Excel ke ujian (langsung attach ke sub indikator).
     * 
     * Alur:
     * 1. Validasi file (.xlsx, .xls, .csv) & sub_indikator_id tujuan
     * 2. Gunakan SoalImport class untuk parse Excel row-by-row
     * 3. Buat Soal baru per row (di sub indikator terpilih)
     * 4. Attach semua soal baru ke ujian via ExamAssemblyService
     * 5. Tampilkan summary: berapa berhasil, berapa skip, error apa
     * 
     * Form: Modal upload file (POST method)
     *       Input: file (Excel), sub_indikator_id (dropdown)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Import soal', ['ujian_id' => $ujian->id, 'success' => $successCount, 'skip' => $skipCount, 'errors' => count($errors)]);
     */
    public function importExcel(Request $request, Ujian $ujian): RedirectResponse
    {
        $validated = $request->validate([
            'sub_indikator_id' => ['required', 'integer', 'exists:panritta_sub_indikator,id'],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $subIndikator = SubIndikator::with('subJenisUjian')->findOrFail($validated['sub_indikator_id']);
        $jenisUjianId = $subIndikator->subJenisUjian?->jenis_ujian_id;

        abort_unless(
            $jenisUjianId && $ujian->jenisUjians()->where('panritta_jenis_ujian.id', $jenisUjianId)->exists(),
            404
        );

        try {
            $import = new SoalImport($subIndikator->id, $request->user()->id);
            Excel::import($import, $request->file('file'));

            $newSoalIds = $import->getCreatedSoalIds();
            $errors = $import->getErrors();
            $successCount = $import->getSuccessCount();
            $skipCount = $import->getSkipCount();

            if ($newSoalIds !== []) {
                $this->assemblyService->addQuestions($ujian, $jenisUjianId, $newSoalIds);
            }

            $redirect = redirect()
                ->route('superadmin.ujian.soal.index', ['ujian' => $ujian, 'jenis_ujian_id' => $jenisUjianId]);

            if ($errors !== []) {
                $errorMessage = "Import selesai dengan {$successCount} soal ditambahkan, {$skipCount} dilewati.\n\nError/Warning:\n".implode("\n", $errors);
                $redirect->with('info', $errorMessage);
            } else {
                $message = "Import selesai: {$successCount} soal ditambahkan, {$skipCount} dilewati.";
                $redirect->with('success', $message);
            }

            return $redirect;
        } catch (\Exception $e) {
            return redirect()
                ->route('superadmin.ujian.soal.index', ['ujian' => $ujian, 'jenis_ujian_id' => $subIndikator->subJenisUjian?->jenis_ujian_id ?? null])
                ->with('error', 'Import gagal: '.$e->getMessage());
        }
    }

    /**
     * METHOD: bankSoalOptions()
     * =========================
     * URL Contoh: GET /superadmin/ujian/{ujian}/soal/bank-options (AJAX)
     * 
     * Keterangan:
     * API endpoint JSON untuk autocomplete/search soal dari bank soal.
     * Dipanggil via AJAX saat user cari soal existing untuk ditambahkan ke ujian.
     * 
     * Filter:
     * - Hanya soal dengan jenis_ujian_id sesuai
     * - Exclude soal yang sudah ada di ujian ini
     * - Limit 50 hasil
     * - Search by soal text (case-insensitive)
     * 
     * Response JSON:
     * [
     *   { id, soal (truncated), sub_indikator, sub_jenis_ujian },
     *   ...
     * ]
     * 
     * Form: Tidak ada (API only)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Bank soal search', ['jenis_ujian_id' => $jenisUjianId, 'search' => $request->input('search')]);
     */
    public function bankSoalOptions(Request $request, Ujian $ujian): JsonResponse
    {
        $jenisUjianId = (int) $request->input('jenis_ujian_id');

        abort_unless($ujian->jenisUjians()->where('panritta_jenis_ujian.id', $jenisUjianId)->exists(), 404);

        $existingSoalIds = $ujian->ujianSoals()->pluck('soal_id')->all();

        $soals = Soal::query()
            ->with('subIndikator.subJenisUjian')
            ->whereHas('subIndikator.subJenisUjian', fn ($query) => $query->where('jenis_ujian_id', $jenisUjianId))
            ->whereNotIn('id', $existingSoalIds)
            ->when($request->filled('search'), fn ($query) => $query->where('soal', 'like', '%'.$request->input('search').'%'))
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (Soal $soal) => [
                'id' => $soal->id,
                'soal' => \Illuminate\Support\Str::limit(strip_tags($soal->soal), 120),
                'sub_indikator' => $soal->subIndikator?->nama_sub_indikator,
                'sub_jenis_ujian' => $soal->subIndikator?->subJenisUjian?->nama_sub_jenis_ujian,
            ]);

        return response()->json($soals);
    }

    /**
     * METHOD: remaining()
     * ===================
     * URL Contoh: GET /superadmin/ujian/{ujian}/soal/remaining (AJAX)
     * 
     * Keterangan:
     * API endpoint JSON untuk cek KAPASITAS UJIAN yang tersisa.
     * 
     * Response:
     * {
     *   "remaining": 10,      // sisa slot soal
     *   "jumlah_soal": 50     // total kapasitas
     * }
     * 
     * Gunakan untuk validasi UI: tombol "Tambah" disabled jika remaining <= 0
     * 
     * Form: Tidak ada (API only)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Remaining slots for ujian '.$ujian->id);
     */
    public function remaining(Ujian $ujian): JsonResponse
    {
        return response()->json([
            'remaining' => $this->assemblyService->remainingSlots($ujian),
            'jumlah_soal' => $ujian->jumlah_soal,
        ]);
    }

    /**
     * METHOD: attach()
     * ================
     * URL Contoh: POST /superadmin/ujian/{ujian}/soal/attach
     * 
     * Keterangan:
     * Menambahkan soal-soal yang sudah ada dari BANK SOAL ke ujian.
     * Beda dengan import: ini pakai soal existing (bukan buat baru).
     * 
     * Validasi:
     * - soal_id harus integer array, min 1
     * - jenis_ujian_id harus belong to ujian
     * 
     * Gunakan ExamAssemblyService::addQuestions() untuk attach + assign urutan.
     * 
     * Form: Modal "Tambah dari Bank" di halaman kelola soal (POST method)
     *       Input: jenis_ujian_id, soal_id[] (multiselect)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Attach soal to ujian '.$ujian->id, ['soal_count' => count($validated['soal_id']), 'jenis' => $jenisUjianId]);
     */
    public function attach(Request $request, Ujian $ujian): RedirectResponse
    {
        $validated = $request->validate([
            'jenis_ujian_id' => ['required', 'integer'],
            'soal_id' => ['required', 'array', 'min:1'],
            'soal_id.*' => ['integer', 'exists:panritta_soal,id'],
        ]);

        $jenisUjianId = (int) $validated['jenis_ujian_id'];

        abort_unless($ujian->jenisUjians()->where('panritta_jenis_ujian.id', $jenisUjianId)->exists(), 404);

        $this->assemblyService->addQuestions($ujian, $jenisUjianId, $validated['soal_id']);

        return redirect()
            ->route('superadmin.ujian.soal.index', ['ujian' => $ujian, 'jenis_ujian_id' => $jenisUjianId])
            ->with('success', 'Soal berhasil ditambahkan ke ujian.');
    }

    /**
     * METHOD: finalisasi()
     * ====================
     * URL Contoh: GET /superadmin/ujian/{ujian}/soal/finalisasi
     * 
     * Keterangan:
     * HALAMAN FINALISASI: tampilkan semua soal dalam urutan, siap untuk di-lock.
     * 
     * Fitur:
     * - Tampilan per sub indikator (group by kategori)
     * - Drag-and-drop untuk ubah urutan soal
     * - Preview soal per tab jenis ujian
     * - Tombol "Finalisasi" untuk lock susunan
     * 
     * Form: Tidak ada GET saja, action via reorder() & finalize() AJAX/POST
     * 
     * Debug Mode (uncomment untuk trace):
     * // dd(['ujian' => $ujian->nama_ujian, 'totalSoal' => $totalSoal, 'groupedCount' => $ujianSoalsPerSubIndikator->count()]);
     */
    public function finalisasi(Request $request, Ujian $ujian): View
    {
        $ujian->load('ujianJenisUjians.jenisUjian');

        $jenisUjians = $ujian->ujianJenisUjians
            ->map(fn ($item) => $item->jenisUjian)
            ->filter()
            ->unique('id')
            ->values();

        $activeJenisId = (int) $request->input('jenis_ujian_id', $jenisUjians->first()?->id);

        $ujianSoals = $ujian->ujianSoals()
            ->with('soal.subIndikator.subJenisUjian')
            ->when($activeJenisId, fn ($query) => $query->where('jenis_ujian_id', $activeJenisId))
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        $ujianSoalsPerSubIndikator = $ujianSoals
            ->groupBy(fn ($ujianSoal) => $ujianSoal->soal?->subIndikator?->nama_sub_indikator ?? 'Tanpa Sub Indikator');

        $totalSoal = $ujian->ujianSoals()->count();

        return view('superadmin.ujian.soal.finalisasi', compact(
            'ujian',
            'jenisUjians',
            'activeJenisId',
            'ujianSoalsPerSubIndikator',
            'totalSoal',
        ));
    }

    /**
     * METHOD: finalize()
     * ==================
     * URL Contoh: PATCH /superadmin/ujian/{ujian}/soal/finalize
     * 
     * Keterangan:
     * SET finalized_at = NOW untuk LOCK susunan soal.
     * Setelah finalized, ujian bisa diaktifkan (status = aktif).
     * Soal TIDAK BOLEH ditambah/dihapus/diurutkan lagi (jika ada enforcement).
     * 
     * Form: Button di halaman finalisasi (PATCH dengan CSRF token)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Finalize ujian ID '.$ujian->id.' at '.now());
     */
    public function finalize(Ujian $ujian): RedirectResponse
    {
        $ujian->update(['finalized_at' => now()]);

        return redirect()
            ->route('superadmin.ujian.soal.finalisasi', $ujian)
            ->with('success', 'Ujian berhasil difinalisasi.');
    }

    /**
     * METHOD: unfinalize()
     * ====================
     * URL Contoh: PATCH /superadmin/ujian/{ujian}/soal/unfinalize
     * 
     * Keterangan:
     * BATALKAN finalisasi: set finalized_at = NULL.
     * Kembali ke edit mode: soal boleh ditambah/dihapus/diurutkan.
     * 
     * Biasanya dipakai jika ada kesalahan saat finalisasi.
     * 
     * Form: Button di halaman finalisasi (PATCH dengan CSRF token)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Unfinalize ujian ID '.$ujian->id);
     */
    public function unfinalize(Ujian $ujian): RedirectResponse
    {
        $ujian->update(['finalized_at' => null]);

        return redirect()
            ->route('superadmin.ujian.soal.finalisasi', $ujian)
            ->with('success', 'Finalisasi ujian dibatalkan.');
    }

    /**
     * METHOD: reorder()
     * =================
     * URL Contoh: PATCH /superadmin/ujian/{ujian}/soal/reorder (AJAX)
     * 
     * Keterangan:
     * UBAH URUTAN SOAL via drag-and-drop (frontend).
     * 
     * Algoritma:
     * 1. Terima ujian_soal_ids array dalam urutan baru (from UI)
     * 2. Ambil semua soal itu dari DB
     * 3. Sort urutan value mereka (ascending)
     * 4. Redistribute urutan value sesuai array index baru
     * 5. Update semua sekaligus
     * 
     * Hasilnya: urutan relative soal di dalam group (sub indikator) berubah,
     * tapi gap/slot tetap terjaga untuk soal grup lain.
     * 
     * Form: AJAX dari Sortable.js library (PATCH dengan CSRF token)
     *       Input: ujian_soal_ids[] (reordered IDs)
     * 
     * Response: { "status": "ok" } atau error
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Reorder soal', ['ujian_id' => $ujian->id, 'count' => count($validated['ujian_soal_ids']), 'order' => json_encode($validated['ujian_soal_ids'])]);
     */
    public function reorder(Request $request, Ujian $ujian): JsonResponse
    {
        $validated = $request->validate([
            'ujian_soal_ids' => ['required', 'array', 'min:1'],
            'ujian_soal_ids.*' => ['integer'],
        ]);

        $ujianSoals = $ujian->ujianSoals()
            ->whereIn('id', $validated['ujian_soal_ids'])
            ->get()
            ->keyBy('id');

        abort_unless($ujianSoals->count() === count($validated['ujian_soal_ids']), 404);

        $slots = $ujianSoals->pluck('urutan')->sort()->values();

        foreach ($validated['ujian_soal_ids'] as $index => $id) {
            $ujianSoals[$id]->update(['urutan' => $slots[$index]]);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * METHOD: detach()
     * ================
     * URL Contoh: DELETE /superadmin/ujian/{ujian}/soal/{ujianSoal}
     * 
     * Keterangan:
     * Menghapus ATTACHMENT soal dari ujian (not the soal itself).
     * Soal tetap ada di bank soal, hanya dilepas dari ujian ini.
     * 
     * Route Model Binding: {ujianSoal} -> UjianSoal model (pivot record).
     * Gunakan ExamAssemblyService::removeQuestion() untuk delete + cleanup.
     * 
     * Form: Tombol delete di halaman kelola soal, per soal (DELETE method)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Detach soal from ujian', ['ujian_id' => $ujian->id, 'soal_id' => $ujianSoal->soal_id, 'jenis_ujian_id' => $jenisUjianId]);
     */
    public function detach(Ujian $ujian, UjianSoal $ujianSoal): RedirectResponse
    {
        abort_unless($ujianSoal->ujian_id === $ujian->id, 404);

        $jenisUjianId = $ujianSoal->jenis_ujian_id;
        $this->assemblyService->removeQuestion($ujian, $ujianSoal->soal_id);

        return redirect()
            ->route('superadmin.ujian.soal.index', ['ujian' => $ujian, 'jenis_ujian_id' => $jenisUjianId])
            ->with('success', 'Soal berhasil dihapus dari ujian.');
    }

    /**
     * Cetak seluruh soal dan kunci jawaban ujian dalam satu halaman HTML utuh.
     */
    public function cetak(Ujian $ujian): View
    {
        $ujianSoals = $ujian->ujianSoals()
            ->with(['soal.subIndikator.subJenisUjian'])
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        return view('superadmin.ujian.soal.cetak', compact('ujian', 'ujianSoals'));
    }
}
