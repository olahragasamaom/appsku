<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Soal;
use App\Models\Ujian;
use App\Models\UjianSoal;
use App\Services\Ujian\ExamAssemblyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UjianSoalController extends Controller
{
    public function __construct(
        private readonly ExamAssemblyService $assemblyService
    ) {}

    public function index(Request $request, Ujian $ujian): View
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

        $totalSoal = $ujian->ujianSoals()->count();

        return view('superadmin.ujian.soal.index', compact(
            'ujian',
            'jenisUjians',
            'activeJenisId',
            'ujianSoals',
            'totalSoal',
        ));
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
