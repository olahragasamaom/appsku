<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubJenisUjianRequest;
use App\Models\JenisUjian;
use App\Models\SubJenisUjian;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * CONTROLLER: SubJenisUjianController
 * ====================================
 * CRUD untuk Sub Jenis Ujian. Strukturnya mirip JenisUjianController,
 * dengan satu konsep baru penting di index(): EAGER LOADING (->with()).
 *
 * PETA ROUTE -> METHOD:
 *   GET    /superadmin/sub-jenis-ujian                   -> index()
 *   POST   /superadmin/sub-jenis-ujian                   -> store()
 *   PUT    /superadmin/sub-jenis-ujian/{subJenisUjian}   -> update()
 *   DELETE /superadmin/sub-jenis-ujian/{subJenisUjian}   -> destroy()
 */
class SubJenisUjianController extends Controller
{
    /**
     * READ: tampilkan daftar, DIKELOMPOKKAN per Jenis Ujian.
     *
     * Di sini kita ambil semua JenisUjian, lalu untuk tiap jenis kita ikut
     * memuat anak-anaknya (subJenisUjian) sekaligus. Ini disebut EAGER LOADING.
     *
     * KENAPA PENTING? Tanpa ->with(), saat view melakukan loop dan mengakses
     * $jenis->subJenisUjian di setiap baris, Laravel akan menembak 1 query
     * database PER baris (masalah "N+1 query" -> lambat). Dengan ->with(),
     * semua anak diambil dalam SATU query tambahan saja -> jauh lebih efisien.
     *
     * Closure di dalam ->with([...]) dipakai untuk MENGURUTKAN anak-anaknya
     * (berdasarkan 'urutan', lalu nama) saat dimuat.
     */
    public function index(): View
    {
        $jenisUjians = JenisUjian::query()
            ->with(['subJenisUjian' => function ($query): void {
                $query->orderBy('urutan')->orderBy('nama_sub_jenis_ujian');
            }])
            ->orderBy('nama_jenis_ujian')
            ->get();

        return view('superadmin.sub-jenis-ujian.index', compact('jenisUjians'));
    }

    /**
     * CREATE: simpan sub jenis ujian baru.
     * Validasi otomatis lewat SubJenisUjianRequest sebelum masuk sini.
     */
    public function store(SubJenisUjianRequest $request): RedirectResponse
    {
        SubJenisUjian::create($request->validated());

        return redirect()->route('superadmin.sub-jenis-ujian.index')
            ->with('success', 'Sub jenis ujian berhasil dibuat.');
    }

    /**
     * UPDATE: ubah data. $subJenisUjian dicari otomatis dari {subJenisUjian} di URL
     * (route model binding).
     */
    public function update(SubJenisUjianRequest $request, SubJenisUjian $subJenisUjian): RedirectResponse
    {
        $subJenisUjian->update($request->validated());

        return redirect()->route('superadmin.sub-jenis-ujian.index')
            ->with('success', 'Sub jenis ujian berhasil diupdate.');
    }

    /**
     * DELETE: hapus data.
     */
    public function destroy(SubJenisUjian $subJenisUjian): RedirectResponse
    {
        $subJenisUjian->delete();

        return redirect()->route('superadmin.sub-jenis-ujian.index')
            ->with('success', 'Sub jenis ujian berhasil dihapus.');
    }
}
