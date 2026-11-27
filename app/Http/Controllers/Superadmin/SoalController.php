<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SoalRequest;
use App\Models\JenisUjian;
use App\Models\Soal;
use App\Models\SubIndikator;
use App\Models\SubJenisUjian;
use App\Models\Ujian;
use App\Models\UjianSoal;
use App\Services\Ujian\ExamAssemblyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;

/**
 * CONTROLLER: SoalController
 * ===========================
 * CRUD PALING LENGKAP di modul ujian. Selain Create-Read-Update-Delete biasa,
 * controller ini juga menangani:
 *   - Filter & pencarian daftar soal (index)
 *   - Halaman form terpisah create() & edit() (BUKAN modal seperti modul lain)
 *   - UPLOAD GAMBAR (soal, tiap opsi, pembahasan) ke storage
 *   - Dua endpoint JSON untuk dropdown bertingkat via AJAX (dependent dropdown)
 *
 * PETA ROUTE -> METHOD:
 *   GET    /superadmin/soal                 -> index()    (daftar + filter)
 *   GET    /superadmin/soal/create          -> create()   (tampilkan form tambah)
 *   POST   /superadmin/soal                 -> store()    (simpan + upload gambar)
 *   GET    /superadmin/soal/{soal}/edit     -> edit()     (tampilkan form edit)
 *   PUT    /superadmin/soal/{soal}          -> update()   (ubah + ganti gambar)
 *   DELETE /superadmin/soal/{soal}          -> destroy()  (hapus + hapus gambar)
 *   GET    .../soal/options/sub-jenis-ujian/{jenisUjian}   -> subJenisUjianOptions() [JSON]
 *   GET    .../soal/options/sub-indikator/{subJenisUjian}  -> subIndikatorOptions()  [JSON]
 */
class SoalController extends Controller
{
    /**
     * METHOD: index()
     * ===============
     * URL Contoh: GET /superadmin/soal
     *
     * Keterangan:
     * Menampilkan DAFTAR SOAL dengan sistem FILTER BERJENJANG + PENCARIAN.
     * Filter prioritas: sub_indikator_id > sub_jenis_ujian_id > jenis_ujian_id
     * Paginasi 15 soal per halaman. Pencarian via kolom "soal".
     *
     * Form: Tidak ada, hanya paramater GET (search, sub_indikator_id, sub_jenis_ujian_id, jenis_ujian_id)
     *
     * Debug Mode (uncomment untuk trace):
     * // dd($soals->toArray()); // Lihat struktur data soal yang dikirim ke view
     */
    public function index(Request $request): View
    {
        $query = Soal::with('subIndikator.subJenisUjian.jenisUjian', 'pembuat');

        if ($request->filled('sub_indikator_id')) {
            $query->where('sub_indikator_id', $request->sub_indikator_id);
        } elseif ($request->filled('sub_jenis_ujian_id')) {
            $query->whereHas('subIndikator', function ($q) use ($request) {
                $q->where('sub_jenis_ujian_id', $request->sub_jenis_ujian_id);
            });
        } elseif ($request->filled('jenis_ujian_id')) {
            $query->whereHas('subIndikator.subJenisUjian', function ($q) use ($request) {
                $q->where('jenis_ujian_id', $request->jenis_ujian_id);
            });
        }

        if ($request->filled('search')) {
            $query->where('soal', 'like', '%'.$request->search.'%');
        }

        $soals = $query->latest()->paginate(15)->withQueryString();
        $jenisUjians = JenisUjian::orderBy('nama_jenis_ujian')->get();

        return view('superadmin.soal.index', compact('soals', 'jenisUjians'));
    }

    /**
     * METHOD: create()
     * ===============
     * URL Contoh: GET /superadmin/soal/create
     *
     * Keterangan:
     * Menampilkan FORM KOSONG untuk membuat soal baru.
     * Form terpisah halaman (BUKAN modal). Jika datang dari "Kelola Soal per Ujian",
     * parameter URL akan membawa sub_indikator_id & ujian_id untuk prefill dropdown.
     *
     * Form: resources/views/superadmin/soal/create.blade.php
     *       (Metode POST ke route: superadmin.soal.store)
     *
     * Debug Mode (uncomment untuk trace):
     * // dd(['jenisUjians' => $jenisUjians, 'subIndikator' => $subIndikator, 'locked' => $locked]);
     */
    public function create(Request $request): View
    {
        $jenisUjians = JenisUjian::orderBy('nama_jenis_ujian')->get();
        $subIndikator = null;
        $ujian = null;

        // Jika datang dari halaman lain dengan membawa sub_indikator_id di URL,
        // muat sub indikator beserta relasi ke atasnya (sub jenis & jenis ujian)
        // agar ketiga dropdown kategori bisa langsung terisi otomatis.
        if ($request->filled('sub_indikator_id')) {
            $subIndikator = SubIndikator::with('subJenisUjian.jenisUjian')
                ->find($request->sub_indikator_id);
        }

        // Jika ada ujian_id, load ujian untuk breadcrumb
        if ($request->filled('ujian_id')) {
            $ujian = Ujian::find($request->ujian_id);
        }

        // $locked = kategori dikunci (tidak bisa diubah) ketika sub indikator
        // sudah ditentukan dari luar (mis. tombol "Tambah Soal" per sub indikator).
        $locked = $subIndikator !== null;

        return view('superadmin.soal.create', compact('jenisUjians', 'subIndikator', 'locked', 'ujian'));
    }

    /**
     * METHOD: store()
     * ===============
     * URL Contoh: POST /superadmin/soal
     *
     * Keterangan:
     * Menyimpan SOAL BARU ke database beserta semua gambar (gambar_soal, gambar_opsi_*, gambar_pembahasan).
     * Validasi dilakukan via SoalRequest (custom form request).
     * File gambar disimpan ke storage disk 'uploads' folder 'panritta/soal', path disimpan ke DB.
     *
     * Form: resources/views/superadmin/soal/create.blade.php (POST method)
     *       Input: soal, opsi_a-e, sub_indikator_id, sub_jenis_ujian_id, gambar_soal, gambar_opsi_*, dll
     *
     * Jika request dari Kelola Soal Ujian (ujian_id ada):
     * - Soal otomatis di-attach ke ujian via ExamAssemblyService
     * - Redirect kembali ke form create dengan ujian_id (bisa tambah soal lagi)
     *
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Store soal:', ['data' => $data, 'user' => $request->user()->id]);
     * // dd($soal);
     */
    public function store(SoalRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['pembuat_soal_id'] = $request->user()->id;
        $data['user_updated_by'] = $request->user()->id;

        foreach ($this->imageFields() as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('panritta/soal', 'uploads');
            }
        }

        $soal = Soal::create($data);

        // Jika dikirim dari halaman Kelola Soal Ujian, attach ke ujian
        // dan redirect kembali ke form create (same page) dengan success message
        if ($request->filled('ujian_id')) {
            $ujian = Ujian::find($request->ujian_id);
            if ($ujian) {
                $jenisUjianId = $request->resolveSubJenisUjian()?->jenis_ujian_id;

                if ($jenisUjianId) {
                    app(ExamAssemblyService::class)->addQuestions($ujian, $jenisUjianId, [$soal->id]);

                    return redirect()
                        ->route('superadmin.soal.create', [
                            'sub_indikator_id' => $request->sub_indikator_id,
                            'ujian_id' => $ujian->id,
                        ])
                        ->with('success', 'Soal berhasil dibuat dan ditambahkan ke ujian.');
                }
            }
        }

        return redirect()->route('superadmin.soal.index')
            ->with('success', 'Soal berhasil dibuat.');
    }

    /**
     * METHOD: edit()
     * ==============
     * URL Contoh: GET /superadmin/soal/{soal}/edit (misal: /superadmin/soal/169/edit)
     *
     * Keterangan:
     * Menampilkan FORM TERISI untuk mengedit soal yang sudah ada.
     * Parameter {soal} diambil otomatis via Route Model Binding.
     *
     * Form: resources/views/superadmin/soal/edit.blade.php (PUT method)
     *       Input: sama seperti create, hanya pre-filled dengan data existing
     *
     * Debug Mode (uncomment untuk trace):
     * // dd(['soal' => $soal->toArray(), 'subIndikator' => $soal->subIndikator]);
     */
    public function edit(Soal $soal): View
    {
        $soal->load('subIndikator.subJenisUjian.jenisUjian');
        $jenisUjians = JenisUjian::orderBy('nama_jenis_ujian')->get();

        return view('superadmin.soal.edit', compact('soal', 'jenisUjians'));
    }

    /**
     * METHOD: update()
     * ================
     * URL Contoh: PUT /superadmin/soal/{soal} (misal: /superadmin/soal/169)
     *
     * Keterangan:
     * Menyimpan PERUBAHAN SOAL yang sudah ada.
     *
     * Fitur khusus:
     * 1. Jika ada gambar baru: hapus gambar lama DULU, baru simpan yang baru (via Storage::delete)
     * 2. Jika ada checkbox "hapus_gambar_soal" dll: delete file tersebut dari storage
     * 3. Pembersihan kolom otomatis sesuai sistem penilaian:
     *    - Benar-Salah: kosongkan nilai_bobot_a-e
     *    - Tiap Jawaban Ada Poin: kosongkan kunci_jawaban & nilai_bobot_benar
     *
     * Form: resources/views/superadmin/soal/edit.blade.php (PUT method)
     *
     * Jika dari Kelola Soal Ujian: redirect kembali ke halaman Kelola Soal dengan jenis_ujian_id
     *
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Update soal ID '.$soal->id, ['data' => $data]);
     */
    public function update(SoalRequest $request, Soal $soal): RedirectResponse
    {
        $data = $request->validated();
        $data['user_updated_by'] = $request->user()->id;

        foreach ($this->imageFields() as $field) {
            if ($request->hasFile($field)) {
                if ($soal->{$field}) {
                    Storage::disk('uploads')->delete($soal->{$field});
                }
                $data[$field] = $request->file($field)->store('panritta/soal', 'uploads');
            } elseif ($request->boolean('hapus_'.$field) && $soal->{$field}) {
                Storage::disk('uploads')->delete($soal->{$field});
                $data[$field] = null;
            }
        }

        $subJenis = $request->resolveSubJenisUjian();

        if ($subJenis && $subJenis->sistem_penilaian === 'benar_salah') {
            $data['nilai_bobot_a'] = null;
            $data['nilai_bobot_b'] = null;
            $data['nilai_bobot_c'] = null;
            $data['nilai_bobot_d'] = null;
            $data['nilai_bobot_e'] = null;
        } else {
            $data['kunci_jawaban'] = null;
            $data['nilai_bobot_benar'] = null;
        }

        $soal->update($data);

        // Jika dikirim dari halaman Kelola Soal Ujian, redirect kembali ke halaman ujian tersebut.
        if ($request->filled('ujian_id')) {
            $ujian = Ujian::find($request->ujian_id);
            $jenisUjianId = $request->resolveSubJenisUjian()?->jenis_ujian_id;

            if ($ujian && $jenisUjianId) {
                return redirect()
                    ->route('superadmin.ujian.soal.index', ['ujian' => $ujian, 'jenis_ujian_id' => $jenisUjianId])
                    ->with('success', 'Soal berhasil diupdate.');
            }
        }

        return redirect()->route('superadmin.soal.index')
            ->with('success', 'Soal berhasil diupdate.');
    }

    /**
     * METHOD: destroy()
     * =================
     * URL Contoh: DELETE /superadmin/soal/{soal} (misal: /superadmin/soal/169)
     *
     * Keterangan:
     * Menghapus SOAL beserta SEMUA file gambar terkaitnya dari storage.
     * Mencegah file yatim (orphan files) tersisa di folder uploads.
     *
     * Form: Tombol delete di halaman index (via form dengan method DELETE)
     *
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Delete soal ID '.$soal->id, ['fields' => $this->imageFields()]);
     */
    public function destroy(Soal $soal): RedirectResponse
    {
        foreach ($this->imageFields() as $field) {
            if ($soal->{$field}) {
                Storage::disk('public')->delete($soal->{$field});
            }
        }

        $soal->delete();

        return redirect()->route('superadmin.soal.index')
            ->with('success', 'Soal berhasil dihapus.');
    }

    /**
     * METHOD: preview()
     * =================
     * URL Contoh: GET /superadmin/soal/{soal}/preview (misal: /superadmin/soal/169/preview)
     *
     * Keterangan:
     * Mensimulasikan TAMPILAN SOAL persis seperti di halaman ujian peserta.
     * Jika soal ada dalam ujian: tampilkan nomor urutan & navigasi lengkap.
     * Jika soal belum ada dalam ujian: tampilkan nomor 1 dari 1.
     *
     * Render formula & HTML via FormulaRenderer service (KaTeX, safe tags).
     *
     * Form: Tidak ada, view only
     *
     * Debug Mode (uncomment untuk trace):
     * // dd(['ujianSoal' => $ujianSoal, 'totalSoal' => $totalSoal, 'nomorUrut' => $nomorUrut]);
     */
    public function preview(Soal $soal): View
    {
        $soal->load('subIndikator.subJenisUjian');

        // Cari ujian_soal entry (jika soal ini bagian dari ujian)
        $ujianSoal = UjianSoal::where('soal_id', $soal->id)
            ->orderBy('urutan')
            ->first();

        $nomorUrut = 1;
        $totalSoal = 1;
        $navigasiNomors = [1];

        if ($ujianSoal) {
            $allUjianSoals = UjianSoal::where('ujian_id', $ujianSoal->ujian_id)
                ->where('jenis_ujian_id', $ujianSoal->jenis_ujian_id)
                ->orderBy('urutan')
                ->get();

            $totalSoal = $allUjianSoals->count();
            $navigasiNomors = range(1, $totalSoal);

            $index = $allUjianSoals->search(fn ($us) => $us->soal_id === $soal->id);
            if ($index !== false) {
                $nomorUrut = $index + 1;
            }
        }

        return view('superadmin.soal.preview', compact('soal', 'nomorUrut', 'totalSoal', 'navigasiNomors'));
    }

    /**
     * METHOD: uploadGambarEditor()
     * ============================
     * URL Contoh: POST /superadmin/soal/upload-gambar-editor
     *
     * Keterangan:
     * API endpoint untuk upload gambar dari CKEditor (inline image).
     * Dipanggil via AJAX saat user insert gambar di editor WYSIWYG.
     * Mengembalikan JSON berisi URL publik untuk disematkan sebagai <img src="...">
     *
     * Form: FormData multipart (file field: "gambar")
     *       Validasi: required, image, max 2048 KB
     *
     * Response: JSON { "url": "https://example.com/uploads/panritta/soal/editor/..." }
     *
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Editor upload', ['user' => $request->user()->id, 'path' => $path]);
     */
    public function uploadGambarEditor(Request $request): JsonResponse
    {
        if (! $request->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'gambar' => ['required', 'image', 'max:5120'],
        ]);

        $path = $request->file('gambar')->store('panritta/soal/editor', 'uploads');

        return response()->json([
            'url' => Storage::disk('uploads')->url($path),
        ]);
    }

    /**
     * METHOD: downloadTemplate()
     * ==========================
     * URL Contoh: GET /superadmin/soal/download-template
     *             atau /superadmin/soal/download-template/{ujian}/{subIndikator}/{subJenisUjian}
     *
     * Keterangan:
     * Download template Excel (.xlsx) untuk bulk import soal.
     * Format kolom DINAMIS sesuai sistem penilaian sub jenis ujian:
     *
     * - Benar-Salah: kolom kunci_jawaban + nilai_bobot_benar
     * - Tiap Jawaban Ada Poin: kolom nilai_bobot_a/b/c/d/e (NO kunci_jawaban)
     * - Tanpa parameter: template generic lengkap semua kolom
     *
     * Filename otomatis sesuai ujian+kategori+tipe (misal: import_soal_cpns_tkp_psikologi.xlsx)
     *
     * Form: Tidak ada
     *
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Download template', ['subJenis' => $subJenisUjian->id ?? null]);
     */
    public function downloadTemplate(?SubJenisUjian $subJenisUjian = null, ?Ujian $ujian = null, ?SubIndikator $subIndikator = null)
    {
        $baseColumns = ['soal', 'pembahasan'];
        $data = [];

        if ($subJenisUjian === null) {
            // Generic template (semua kolom)
            $columns = [
                'soal', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e',
                'kunci_jawaban', 'nilai_bobot_benar',
                'nilai_bobot_a', 'nilai_bobot_b', 'nilai_bobot_c', 'nilai_bobot_d', 'nilai_bobot_e',
                'pembahasan',
            ];
            $data[] = $columns;
            $data[] = ['Apa ibukota Indonesia?', 'Jakarta', 'Bandung', 'Surabaya', 'Semarang', '', 'A', '5', '', '', '', '', '', 'Jakarta adalah ibukota negara RI.'];
            $data[] = ['Menurut Anda, seberapa penting disiplin?', 'Sangat penting', 'Penting', 'Biasa saja', 'Kurang penting', 'Tidak penting', '', '', '5', '4', '3', '2', '1', 'Disiplin menentukan integritas.'];
        } else {
            // Dynamic template berdasarkan sistem penilaian
            $columns = ['soal'];

            // Tambah kolom opsi berdasarkan jumlah jawaban pilihan ganda
            $jumlahOpsi = $subJenisUjian->jumlah_jawaban_pilihan_ganda ?? 5;
            $opsiLetters = ['a', 'b', 'c', 'd', 'e'];
            for ($i = 0; $i < $jumlahOpsi; $i++) {
                $columns[] = 'opsi_'.$opsiLetters[$i];
            }

            // Tambah kolom berdasarkan sistem penilaian
            if ($subJenisUjian->sistem_penilaian === 'benar_salah') {
                $columns[] = 'kunci_jawaban';
                $columns[] = 'nilai_bobot_benar';
            } else {
                // tiap_jawaban_ada_poin
                for ($i = 0; $i < $jumlahOpsi; $i++) {
                    $columns[] = 'nilai_bobot_'.$opsiLetters[$i];
                }
            }

            $columns[] = 'pembahasan';

            $data[] = $columns;

            // Contoh data berdasarkan sistem penilaian
            if ($subJenisUjian->sistem_penilaian === 'benar_salah') {
                $exampleRow = ['Apa ibukota Indonesia?'];
                for ($i = 0; $i < $jumlahOpsi; $i++) {
                    $exampleRow[] = ['Jakarta', 'Bandung', 'Surabaya', 'Semarang', 'Yogyakarta'][$i] ?? '';
                }
                $exampleRow[] = 'A';
                $exampleRow[] = $subJenisUjian->nilai_benar ?? '5';
                $exampleRow[] = 'Jakarta adalah ibukota negara RI.';
                $data[] = $exampleRow;
            } else {
                // tiap_jawaban_ada_poin
                $exampleRow = ['Menurut Anda, seberapa penting disiplin?'];
                $opsiTKP = ['Sangat penting', 'Penting', 'Biasa saja', 'Kurang penting', 'Tidak penting'];
                for ($i = 0; $i < $jumlahOpsi; $i++) {
                    $exampleRow[] = $opsiTKP[$i] ?? '';
                }
                $bobotTKP = [5, 4, 3, 2, 1];
                for ($i = 0; $i < $jumlahOpsi; $i++) {
                    $exampleRow[] = $bobotTKP[$i] ?? '';
                }
                $exampleRow[] = 'Disiplin menentukan integritas.';
                $data[] = $exampleRow;
            }
        }

        // Generate filename dengan informasi ujian, kategori, dan tipe soal
        if ($ujian && $subIndikator && $subJenisUjian) {
            $ujianSlug = Str::slug($ujian->nama_ujian);
            $kategoriSlug = Str::slug($subIndikator->nama_sub_indikator);
            $tipeSlug = Str::slug($subJenisUjian->nama_sub_jenis_ujian);
            $filename = "import_soal_{$ujianSlug}_{$kategoriSlug}_{$tipeSlug}.xlsx";
        } elseif ($subJenisUjian) {
            $filename = 'template_'.Str::slug($subJenisUjian->nama_sub_jenis_ujian).'.xlsx';
        } else {
            $filename = 'template_import_soal.xlsx';
        }

        return Excel::download(new class($data) implements FromArray
        {
            public function __construct(private array $data) {}

            public function array(): array
            {
                return $this->data;
            }
        }, $filename);
    }

    /**
     * METHOD: subJenisUjianOptions()
     * ==============================
     * URL Contoh: GET /superadmin/soal/options/sub-jenis-ujian/{jenisUjian}
     *             (misal: /superadmin/soal/options/sub-jenis-ujian/1)
     *
     * Keterangan:
     * API endpoint JSON untuk DEPENDENT DROPDOWN (dropdown bertingkat).
     * Dipanggil via AJAX saat user memilih Jenis Ujian.
     * Mengembalikan daftar Sub Jenis Ujian yang belong to Jenis Ujian.
     *
     * Response JSON:
     * [
     *   { id: 1, nama_sub_jenis_ujian: "TWK", sistem_penilaian: "benar_salah", ... },
     *   { id: 2, nama_sub_jenis_ujian: "TIU", sistem_penilaian: "tiap_jawaban_ada_poin", ... }
     * ]
     *
     * Form: Tidak ada
     *
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('SubJenisOptions for Jenis:'.$jenisUjian->id, ['count' => $options->count()]);
     */
    public function subJenisUjianOptions(JenisUjian $jenisUjian): JsonResponse
    {
        $options = SubJenisUjian::where('jenis_ujian_id', $jenisUjian->id)
            ->orderBy('nama_sub_jenis_ujian')
            ->get(['id', 'nama_sub_jenis_ujian', 'sistem_penilaian', 'jumlah_jawaban_pilihan_ganda', 'nilai_benar']);

        return response()->json($options);
    }

    /**
     * METHOD: subIndikatorOptions()
     * =============================
     * URL Contoh: GET /superadmin/soal/options/sub-indikator/{subJenisUjian}
     *             (misal: /superadmin/soal/options/sub-indikator/5)
     *
     * Keterangan:
     * API endpoint JSON untuk DEPENDENT DROPDOWN (dropdown 3 tingkat).
     * Dipanggil via AJAX saat user memilih Sub Jenis Ujian.
     * Mengembalikan daftar Sub Indikator yang belong to Sub Jenis Ujian.
     *
     * Response JSON:
     * [
     *   { id: 10, nama_sub_indikator: "Pancasila" },
     *   { id: 11, nama_sub_indikator: "UUD 1945" },
     *   ...
     * ]
     *
     * Form: Tidak ada
     *
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('SubIndikatorOptions for SubJenis:'.$subJenisUjian->id, ['count' => $options->count()]);
     */
    public function subIndikatorOptions(SubJenisUjian $subJenisUjian): JsonResponse
    {
        $options = SubIndikator::where('sub_jenis_ujian_id', $subJenisUjian->id)
            ->orderBy('nama_sub_indikator')
            ->get(['id', 'nama_sub_indikator']);

        return response()->json($options);
    }

    /**
     * METHOD BANTUAN (private): daftar nama kolom bertipe gambar.
     * "private" berarti hanya boleh dipanggil dari dalam kelas ini.
     * Dipakai berulang di store/update/destroy agar tidak menulis daftar
     * yang sama berkali-kali (prinsip DRY: Don't Repeat Yourself).
     *
     * @return array<int, string>
     */
    private function imageFields(): array
    {
        return [
            'gambar_soal',
            'gambar_opsi_a',
            'gambar_opsi_b',
            'gambar_opsi_c',
            'gambar_opsi_d',
            'gambar_opsi_e',
            'gambar_pembahasan',
        ];
    }
}
