<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\JenisUjianRequest;
use App\Models\JenisUjian;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * CONTROLLER: JenisUjianController
 * =================================
 * Controller adalah "juru bicara" antara URL (route), data (model), dan
 * tampilan (view). Saat user membuka sebuah URL, route mengarahkannya ke
 * salah satu method di controller ini.
 *
 * Ini contoh CRUD PALING SEDERHANA di aplikasi -> jadikan patokan.
 * (CRUD = Create, Read, Update, Delete)
 *
 * PETA ROUTE -> METHOD (lihat routes/web.php):
 *   GET    /superadmin/jenis-ujian              -> index()   (tampilkan daftar)
 *   POST   /superadmin/jenis-ujian              -> store()   (simpan data baru)
 *   PUT    /superadmin/jenis-ujian/{jenisUjian} -> update()  (ubah data)
 *   DELETE /superadmin/jenis-ujian/{jenisUjian} -> destroy() (hapus data)
 *
 * Catatan: modul ini pakai MODAL (pop-up) untuk tambah/edit, jadi tidak ada
 * method create()/edit() terpisah seperti di modul Soal. Formnya menyatu di index.
 */
class JenisUjianController extends Controller
{
    /**
     * READ (menampilkan daftar).
     * Dipanggil saat membuka halaman daftar jenis ujian.
     *
     * Alur:
     *   1. Ambil semua JenisUjian, urutkan berdasarkan nama (A-Z).
     *   2. paginate(15) = pecah jadi halaman berisi 15 data (ada tombol next/prev).
     *   3. Kirim variabel $jenisUjians ke view lewat compact().
     *
     * return View -> Laravel me-render file blade & mengirim HTML ke browser.
     */
    public function index(): View
    {
        $jenisUjians = JenisUjian::orderBy('nama_jenis_ujian')->paginate(15);

        // compact('jenisUjians') sama artinya dengan ['jenisUjians' => $jenisUjians]
        return view('superadmin.jenis-ujian.index', compact('jenisUjians'));
    }

    /**
     * CREATE (menyimpan data baru).
     * Dipanggil saat form "Tambah" di-submit (method POST).
     *
     * Perhatikan parameter: JenisUjianRequest $request.
     * -> Laravel otomatis memvalidasi input DULU (lihat JenisUjianRequest).
     *    Jika gagal, method ini TIDAK akan dijalankan.
     *
     * $request->validated() = HANYA data yang sudah lolos aturan validasi.
     * JenisUjian::create(...) = INSERT baris baru ke database.
     *
     * return redirect()->route(...) = arahkan browser kembali ke halaman daftar.
     * ->with('success', ...) = titipkan pesan sukses sekali-tampil (flash message)
     *   yang bisa ditampilkan di view sebagai notifikasi hijau.
     */
    public function store(JenisUjianRequest $request): RedirectResponse
    {
        JenisUjian::create($request->validated());

        return redirect()->route('superadmin.jenis-ujian.index')
            ->with('success', 'Jenis ujian berhasil dibuat.');
    }

    /**
     * UPDATE (mengubah data yang sudah ada).
     * Dipanggil saat form "Edit" di-submit (method PUT).
     *
     * Parameter kedua "JenisUjian $jenisUjian" adalah ROUTE MODEL BINDING:
     * Laravel melihat {jenisUjian} di URL, lalu OTOMATIS mencari record
     * dengan id tersebut di database dan menyuntikkannya ke sini.
     * Kalau id tidak ada -> otomatis error 404 (data tidak ditemukan).
     *
     * ->update(...) = UPDATE baris yang bersangkutan dengan data baru.
     */
    public function update(JenisUjianRequest $request, JenisUjian $jenisUjian): RedirectResponse
    {
        $jenisUjian->update($request->validated());

        return redirect()->route('superadmin.jenis-ujian.index')
            ->with('success', 'Jenis ujian berhasil diupdate.');
    }

    /**
     * DELETE (menghapus data).
     * Dipanggil saat tombol hapus ditekan (method DELETE).
     *
     * $jenisUjian juga hasil route model binding (dicari otomatis dari URL).
     * ->delete() = hapus baris dari database.
     */
    public function destroy(JenisUjian $jenisUjian): RedirectResponse
    {
        $jenisUjian->delete();

        return redirect()->route('superadmin.jenis-ujian.index')
            ->with('success', 'Jenis ujian berhasil dihapus.');
    }
}
