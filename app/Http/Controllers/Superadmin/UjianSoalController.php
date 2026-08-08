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
     * Tampilkan halaman kelola soal untuk sebuah ujian.
     *
     * Menyiapkan tiga data utama untuk view:
     * 1. Daftar tab jenis ujian.
     * 2. Semua soal ujian pada jenis ujian aktif (untuk tampilan flat).
     * 3. Daftar sub indikator beserta jumlah soal yang sudah masuk (untuk panel tombol & tampilan per grup).
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
     * Import soal dari file Excel ke sebuah sub indikator, lalu langsung
     * lampirkan soal-soal baru itu ke ujian saat ini.
     *
     * Alur:
     * 1. Validasi file & sub indikator tujuan.
     * 2. Proses baris Excel jadi Soal baru via SoalImport (dikelompokkan ke sub indikator terpilih).
     * 3. Lampirkan soal-soal yang baru dibuat ke ujian (hormati kapasitas ujian).
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

        $import = new SoalImport($subIndikator->id, $request->user()->id);
        Excel::import($import, $request->file('file'));

        $newSoalIds = $import->getCreatedSoalIds();

        if ($newSoalIds !== []) {
            $this->assemblyService->addQuestions($ujian, $jenisUjianId, $newSoalIds);
        }

        $message = "Import selesai: {$import->getSuccessCount()} soal ditambahkan, {$import->getSkipCount()} dilewati.";

        return redirect()
            ->route('superadmin.ujian.soal.index', ['ujian' => $ujian, 'jenis_ujian_id' => $jenisUjianId])
            ->with('success', $message);
    }

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

    public function remaining(Ujian $ujian): JsonResponse
    {
        return response()->json([
            'remaining' => $this->assemblyService->remainingSlots($ujian),
            'jumlah_soal' => $ujian->jumlah_soal,
        ]);
    }

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

    public function detach(Ujian $ujian, UjianSoal $ujianSoal): RedirectResponse
    {
        abort_unless($ujianSoal->ujian_id === $ujian->id, 404);

        $jenisUjianId = $ujianSoal->jenis_ujian_id;
        $this->assemblyService->removeQuestion($ujian, $ujianSoal->soal_id);

        return redirect()
            ->route('superadmin.ujian.soal.index', ['ujian' => $ujian, 'jenis_ujian_id' => $jenisUjianId])
            ->with('success', 'Soal berhasil dihapus dari ujian.');
    }
}
