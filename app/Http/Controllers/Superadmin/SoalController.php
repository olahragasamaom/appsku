<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SoalRequest;
use App\Models\JenisUjian;
use App\Models\Soal;
use App\Models\SubIndikator;
use App\Models\SubJenisUjian;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

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
     * READ: daftar soal dengan FILTER berjenjang + PENCARIAN.
     *
     * $query dibangun bertahap (query builder), belum dieksekusi sampai ->paginate().
     *
     * Filter memakai pola if/elseif dari yang paling spesifik ke paling umum:
     *   - sub_indikator_id  -> filter langsung di kolom soal.
     *   - sub_jenis_ujian_id -> whereHas('subIndikator', ...) artinya "ambil soal
     *     yang RELASI subIndikator-nya memenuhi syarat". whereHas = filter lewat relasi.
     *   - jenis_ujian_id -> whereHas menembus dua relasi ('subIndikator.subJenisUjian').
     *
     * ->latest() = urutkan dari terbaru. ->withQueryString() = pertahankan
     * parameter filter/pencarian saat pindah halaman pagination.
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
     * CREATE (tahap 1 - TAMPILKAN FORM kosong).
     * Berbeda dari modul modal: soal punya halaman form sendiri.
     *
     * $jenisUjians untuk mengisi dropdown pertama. Jika URL membawa
     * sub_indikator_id (mis. datang dari halaman lain), kita muat sekalian
     * agar form bisa langsung terisi konteksnya.
     */
    public function create(Request $request): View
    {
        $jenisUjians = JenisUjian::orderBy('nama_jenis_ujian')->get();
        $subIndikator = null;

        // Jika datang dari halaman lain dengan membawa sub_indikator_id di URL,
        // muat sub indikator beserta relasi ke atasnya (sub jenis & jenis ujian)
        // agar ketiga dropdown kategori bisa langsung terisi otomatis.
        if ($request->filled('sub_indikator_id')) {
            $subIndikator = SubIndikator::with('subJenisUjian.jenisUjian')
                ->find($request->sub_indikator_id);
        }

        // $locked = kategori dikunci (tidak bisa diubah) ketika sub indikator
        // sudah ditentukan dari luar (mis. tombol "Tambah Soal" per sub indikator).
        $locked = $subIndikator !== null;

        return view('superadmin.soal.create', compact('jenisUjians', 'subIndikator', 'locked'));
    }

    /**
     * CREATE (tahap 2 - SIMPAN data + gambar).
     *
     * 1. $request->validated() -> data yang lolos validasi kondisional (SoalRequest).
     * 2. Tambahkan pembuat_soal_id = id user yang sedang login ($request->user()).
     * 3. Loop tiap field gambar (lihat imageFields()); jika ada file yang diunggah,
     *    simpan ke folder storage 'panritta/soal' di disk 'public', lalu simpan
     *    PATH-nya ke database (bukan file-nya). ->store() mengembalikan path itu.
     * 4. Soal::create($data) -> INSERT ke database.
     */
    public function store(SoalRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['pembuat_soal_id'] = $request->user()->id;

        foreach ($this->imageFields() as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('panritta/soal', 'public');
            }
        }

        Soal::create($data);

        return redirect()->route('superadmin.soal.index')
            ->with('success', 'Soal berhasil dibuat.');
    }

    /**
     * UPDATE (tahap 1 - TAMPILKAN FORM terisi).
     * ->load(...) melakukan eager loading pada model yang SUDAH ada (hasil
     * route model binding), agar view bisa menampilkan konteks lengkapnya.
     */
    public function edit(Soal $soal): View
    {
        $soal->load('subIndikator.subJenisUjian.jenisUjian');
        $jenisUjians = JenisUjian::orderBy('nama_jenis_ujian')->get();

        return view('superadmin.soal.edit', compact('soal', 'jenisUjians'));
    }

    /**
     * UPDATE (tahap 2 - SIMPAN perubahan).
     *
     * Perbedaan penting dari store():
     *   a) Saat mengganti gambar: HAPUS DULU file lama dari storage
     *      (Storage::delete) agar tidak menumpuk sampah, baru simpan yang baru.
     *   b) PEMBERSIHAN kolom sesuai sistem penilaian: kita cek ulang
     *      SubJenisUjian (resolveSubJenisUjian). Jika "benar_salah", kosongkan
     *      semua nilai_bobot per opsi; jika sebaliknya, kosongkan kunci_jawaban
     *      & nilai_bobot_benar. Ini menjaga data tetap konsisten bila user
     *      mengganti tipe penilaian saat mengedit.
     */
    public function update(SoalRequest $request, Soal $soal): RedirectResponse
    {
        $data = $request->validated();

        foreach ($this->imageFields() as $field) {
            if ($request->hasFile($field)) {
                if ($soal->{$field}) {
                    Storage::disk('public')->delete($soal->{$field});
                }
                $data[$field] = $request->file($field)->store('panritta/soal', 'public');
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

        return redirect()->route('superadmin.soal.index')
            ->with('success', 'Soal berhasil diupdate.');
    }

    /**
     * DELETE: hapus soal SEKALIGUS file gambar terkait dari storage,
     * supaya tidak ada file yatim (orphan) yang tersisa.
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
     * ENDPOINT JSON (AJAX): opsi Sub Jenis Ujian untuk sebuah Jenis Ujian.
     *
     * Ini BUKAN halaman HTML, melainkan mengembalikan data JSON. Dipanggil oleh
     * JavaScript di form soal: saat user memilih Jenis Ujian, JS memanggil URL
     * ini untuk mengisi dropdown Sub Jenis Ujian secara dinamis (dependent dropdown)
     * tanpa reload halaman.
     *
     * get(['id', ...]) sengaja hanya mengambil kolom yang dibutuhkan front-end
     * (termasuk sistem_penilaian & jumlah opsi, agar JS bisa mengatur form).
     */
    public function subJenisUjianOptions(JenisUjian $jenisUjian): JsonResponse
    {
        $options = SubJenisUjian::where('jenis_ujian_id', $jenisUjian->id)
            ->orderBy('nama_sub_jenis_ujian')
            ->get(['id', 'nama_sub_jenis_ujian', 'sistem_penilaian', 'jumlah_jawaban_pilihan_ganda', 'nilai_benar']);

        return response()->json($options);
    }

    /**
     * ENDPOINT JSON (AJAX): opsi Sub Indikator untuk sebuah Sub Jenis Ujian.
     * Rantai lanjutan dropdown: setelah Sub Jenis dipilih, isi dropdown Sub Indikator.
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
