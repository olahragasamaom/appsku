<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UjianRequest;
use App\Models\JenisUjian;
use App\Models\Ujian;
use App\Services\Ujian\ExamAssemblyService;
use App\Services\Ujian\TokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UjianController extends Controller
{
    public function __construct(
        private readonly ExamAssemblyService $assemblyService,
        private readonly TokenService $tokenService
    ) {}

    public function index(Request $request): View
    {
        $query = Ujian::with('jenisUjians')->withCount('peserta', 'ujianSoals');

        if ($request->filled('search')) {
            $query->where('nama_ujian', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('tipe_ujian')) {
            $query->where('tipe_ujian', $request->tipe_ujian);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $ujians = $query->latest()->paginate(15)->withQueryString();

        return view('superadmin.ujian.index', compact('ujians'));
    }

    public function create(): View
    {
        // Muat Jenis Ujian beserta Sub Jenis & Sub Indikatornya
        $jenisUjians = JenisUjian::with(['subJenisUjian.subIndikator'])
            ->orderBy('nama_jenis_ujian')
            ->get();
            
        $aksesMemberOptions = $this->aksesMemberOptions();

        return view('superadmin.ujian.create', compact('jenisUjians', 'aksesMemberOptions'));
    }

    public function store(UjianRequest $request): RedirectResponse
    {
        $ujian = DB::transaction(function () use ($request) {
            $ujian = Ujian::create($this->buildData($request));
            $this->syncJenisUjian($ujian, $request);

            return $ujian;
        });

        return redirect()->route('superadmin.ujian.edit', $ujian)
            ->with('success', 'Ujian berhasil dibuat.');
    }

    public function edit(Ujian $ujian): View
    {
        $ujian->load('ujianJenisUjians.jenisUjian');
        
        $jenisUjians = JenisUjian::with(['subJenisUjian.subIndikator'])
            ->orderBy('nama_jenis_ujian')
            ->get();
            
        $aksesMemberOptions = $this->aksesMemberOptions();
        $passingGrades = $ujian->ujianJenisUjians->pluck('passing_grade', 'jenis_ujian_id');
        $selectedJenis = $ujian->ujianJenisUjians->pluck('jenis_ujian_id')->all();

        return view('superadmin.ujian.edit', compact(
            'ujian',
            'jenisUjians',
            'aksesMemberOptions',
            'passingGrades',
            'selectedJenis',
        ));
    }

    public function update(UjianRequest $request, Ujian $ujian): RedirectResponse
    {
        DB::transaction(function () use ($request, $ujian) {
            $ujian->update($this->buildData($request, $ujian));
            $this->syncJenisUjian($ujian, $request);
        });

        return redirect()->route('superadmin.ujian.index')
            ->with('success', 'Ujian berhasil diupdate.');
    }

    public function destroy(Ujian $ujian): RedirectResponse
    {
        $ujian->delete();

        return redirect()->route('superadmin.ujian.index')
            ->with('success', 'Ujian berhasil dihapus.');
    }

    public function activate(Ujian $ujian): RedirectResponse
    {
        $this->assemblyService->assertFinalizable($ujian);

        if ($ujian->isOffline()) {
            $this->tokenService->ensureToken($ujian);
        }

        $ujian->update(['status' => 'aktif']);

        return back()->with('success', 'Ujian berhasil diaktifkan.');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildData(UjianRequest $request, ?Ujian $ujian = null): array
    {
        $data = $request->safe()->only([
            'nama_ujian',
            'tipe_ujian',
            'jumlah_soal',
            'status',
        ]);

        $data['acak_soal'] = $request->boolean('acak_soal');
        $data['tampilkan_hasil'] = $request->boolean('tampilkan_hasil');

        if ($request->input('tipe_ujian') === 'offline_kelas') {
            $data['tanggal_ujian'] = $request->input('tanggal_ujian');
            $data['durasi_ujian'] = $request->input('durasi_ujian');
            $data['batas_keterlambatan'] = $request->input('batas_keterlambatan');
            $data['token_ujian'] = $request->filled('token_ujian')
                ? $request->input('token_ujian')
                : ($ujian?->token_ujian ?? strtoupper(Str::random(6)));
            $data['akses_member'] = null;
        } else {
            $data['akses_member'] = $request->input('akses_member', []);
            $data['tanggal_ujian'] = null;
            $data['durasi_ujian'] = null;
            $data['batas_keterlambatan'] = null;
            $data['token_ujian'] = null;
        }

        if ($ujian === null) {
            $data['dibuat_oleh'] = $request->user()->id;
            $data['status'] ??= 'draft';
        }

        return $data;
    }

    private function syncJenisUjian(Ujian $ujian, UjianRequest $request): void
    {
        $passingGrades = $request->input('passing_grade', []);
        $sync = [];

        foreach ($request->input('jenis_ujian_id', []) as $jenisUjianId) {
            $grade = $passingGrades[$jenisUjianId] ?? null;
            $sync[$jenisUjianId] = [
                'passing_grade' => ($grade === null || $grade === '') ? null : $grade,
            ];
        }

        $ujian->jenisUjians()->sync($sync);
    }

    /**
     * @return array<int, string>
     */
    private function aksesMemberOptions(): array
    {
        return ['Free', 'Basic', 'Pro', 'Platinum'];
    }
}
