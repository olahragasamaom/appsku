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

    /**
     * METHOD: index()
     * ===============
     * URL Contoh: GET /superadmin/ujian
     * 
     * Keterangan:
     * Menampilkan DAFTAR UJIAN dengan filter & pencarian.
     * 
     * Filter: search (nama ujian), tipe_ujian (online/offline), status (draft/aktif/selesai)
     * Tampilkan: nama ujian, pembuat, jenis ujian, jml peserta, jml soal, status
     * 
     * Paginasi 15 ujian per halaman. withQueryString() menjaga filter saat pindah halaman.
     * 
     * Form: Tidak ada, parameter GET saja (search, tipe_ujian, status)
     * 
     * Debug Mode (uncomment untuk trace):
     * // dd($ujians->map(fn($u) => ['id' => $u->id, 'nama' => $u->nama_ujian, 'status' => $u->status])->toArray());
     */
    public function index(Request $request): View
    {
        $query = Ujian::with(['jenisUjians', 'pembuat', 'updatedBy'])->withCount(['peserta', 'pesertaOffline', 'ujianSoals']);

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

    /**
     * METHOD: create()
     * ================
     * URL Contoh: GET /superadmin/ujian/create
     * 
     * Keterangan:
     * Menampilkan FORM KOSONG untuk membuat ujian baru.
     * 
     * Form harus miliki:
     * - nama_ujian, tipe_ujian (online/offline_kelas)
     * - jumlah_soal, acak_soal (checkbox), tampilkan_hasil (checkbox)
     * - jenis_ujian_id (multiselect), passing_grade per jenis
     * - Jika offline: tanggal_ujian, durasi_ujian, batas_keterlambatan, token_ujian
     * - Jika online: akses_member (Free/Basic/Pro/Platinum)
     * 
     * Form: resources/views/superadmin/ujian/create.blade.php (POST method)
     * 
     * Debug Mode (uncomment untuk trace):
     * // dd($jenisUjians->map(fn($j) => ['id' => $j->id, 'nama' => $j->nama_jenis_ujian, 'sub_count' => $j->subJenisUjian->count()])->toArray());
     */
    public function create(): View
    {
        // Muat Jenis Ujian beserta Sub Jenis & Sub Indikatornya
        $jenisUjians = JenisUjian::with(['subJenisUjian.subIndikator'])
            ->orderBy('nama_jenis_ujian')
            ->get();

        $aksesMemberOptions = $this->aksesMemberOptions();

        return view('superadmin.ujian.create', compact('jenisUjians', 'aksesMemberOptions'));
    }

    /**
     * METHOD: store()
     * ===============
     * URL Contoh: POST /superadmin/ujian
     * 
     * Keterangan:
     * Menyimpan UJIAN BARU ke database dalam 1 transaction:
     * 1. Buat record Ujian (dengan buildData() yang handle tipe ujian)
     * 2. Sync jenis_ujian_id + passing_grade via many-to-many pivot
     * 
     * Jika tipe = offline_kelas: generate token (atau pakai yang diisi user)
     * Jika tipe = online: kosongkan tanggal/durasi/token
     * 
     * Form: resources/views/superadmin/ujian/create.blade.php (POST method)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Store ujian', ['nama' => $request->nama_ujian, 'tipe' => $request->tipe_ujian, 'ujian_id' => $ujian->id]);
     */
    public function store(UjianRequest $request): RedirectResponse
    {
        $ujian = DB::transaction(function () use ($request) {
            $ujian = Ujian::create($this->buildData($request));
            $this->syncJenisUjian($ujian, $request);

            return $ujian;
        });

        return redirect()->route('superadmin.ujian.index')
            ->with('success', 'Ujian berhasil dibuat.');
    }

    /**
     * METHOD: edit()
     * ==============
     * URL Contoh: GET /superadmin/ujian/{ujian}/edit (misal: /superadmin/ujian/5/edit)
     * 
     * Keterangan:
     * Menampilkan FORM TERISI untuk mengedit ujian yang sudah ada.
     * Parameter {ujian} diambil via Route Model Binding.
     * 
     * Pre-filled: nama, tipe, jml soal, jenis ujian terpilih, passing grade per jenis.
     * Jika offline: tampilkan tanggal, durasi, batas_keterlambatan, token.
     * 
     * Form: resources/views/superadmin/ujian/edit.blade.php (PUT method)
     * 
     * Debug Mode (uncomment untuk trace):
     * // dd(['ujian' => $ujian->toArray(), 'selectedJenis' => $selectedJenis, 'passingGrades' => $passingGrades->toArray()]);
     */
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

    /**
     * METHOD: update()
     * ================
     * URL Contoh: PUT /superadmin/ujian/{ujian} (misal: /superadmin/ujian/5)
     * 
     * Keterangan:
     * Menyimpan PERUBAHAN UJIAN dalam 1 transaction:
     * 1. Update record ujian via buildData() (handle tipe ujian conditional)
     * 2. Re-sync jenis_ujian_id + passing_grade
     * 
     * Jika ujian sudah finalized atau sudah ada peserta:
     * - Beberapa field mungkin tidak bisa diubah (lihat middleware/authorization)
     * 
     * Form: resources/views/superadmin/ujian/edit.blade.php (PUT method)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Update ujian ID '.$ujian->id, ['nama_baru' => $request->nama_ujian]);
     */
    public function update(UjianRequest $request, Ujian $ujian): RedirectResponse
    {
        DB::transaction(function () use ($request, $ujian) {
            $ujian->update($this->buildData($request, $ujian));
            $this->syncJenisUjian($ujian, $request);
        });

        return redirect()->route('superadmin.ujian.index')
            ->with('success', 'Ujian berhasil diupdate.');
    }

    /**
     * METHOD: destroy()
     * =================
     * URL Contoh: DELETE /superadmin/ujian/{ujian} (misal: /superadmin/ujian/5)
     * 
     * Keterangan:
     * Menghapus UJIAN beserta:
     * - Semua ujian_soal (lampiran soal ke ujian)
     * - Semua ujian_peserta & jawaban peserta
     * - Relasi many-to-many dengan jenis_ujian
     * 
     * Cascade delete diatur di migration/model relationship.
     * 
     * Form: Tombol delete di index view (via form DELETE method)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::critical('Delete ujian ID '.$ujian->id.' nama: '.$ujian->nama_ujian);
     */
    public function destroy(Ujian $ujian): RedirectResponse
    {
        $ujian->delete();

        return redirect()->route('superadmin.ujian.index')
            ->with('success', 'Ujian berhasil dihapus.');
    }

    /**
     * METHOD: activate()
     * ==================
     * URL Contoh: PATCH /superadmin/ujian/{ujian}/activate
     * 
     * Keterangan:
     * Mengubah status ujian menjadi AKTIF (siap dijawab peserta).
     * 
     * Validasi:
     * 1. Ujian harus sudah finalized (susunan soal terkunci)
     * 2. Untuk ujian offline: generate/ensure token akses
     * 
     * Aksi:
     * - Update status = 'aktif'
     * - Peserta sekarang bisa login & mulai ujian
     * 
     * Form: Button di index view (PATCH dengan CSRF token)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Activate ujian ID '.$ujian->id, ['offline' => $ujian->isOffline(), 'token' => $ujian->token_ujian]);
     */
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
     * METHOD: updateStatus()
     * ======================
     * URL Contoh: PATCH /superadmin/ujian/{ujian}/status
     * 
     * Keterangan:
     * Mengubah status ujian ke DRAFT / AKTIF / SELESAI via modal.
     * 
     * Status:
     * - DRAFT: ujian dalam persiapan, soal belum terkunci
     * - AKTIF: ujian berjalan, peserta bisa ujian
     * - SELESAI: ujian ditutup, hasil sudah final
     * 
     * Form: Modal status di index view (PATCH dengan CSRF token)
     *       Input: status (dropdown)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Update status ujian ID '.$ujian->id.' ke '.$request->status);
     */
    public function updateStatus(Request $request, Ujian $ujian): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:draft,aktif,selesai'],
        ]);

        $ujian->update(['status' => $request->status]);

        $statusLabel = match ($request->status) {
            'aktif' => 'Aktif',
            'draft' => 'Draft',
            'selesai' => 'Selesai',
            default => 'Unknown',
        };

        return back()->with('success', "Status ujian berhasil diubah menjadi {$statusLabel}.");
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

        $data['user_updated_by'] = $request->user()->id;

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
