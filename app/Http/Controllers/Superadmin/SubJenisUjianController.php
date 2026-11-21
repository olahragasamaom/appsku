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
     * METHOD: index()
     * ===============
     * URL Contoh: GET /superadmin/sub-jenis-ujian
     * 
     * Keterangan:
     * Menampilkan DAFTAR SUB JENIS UJIAN, dikelompokkan per JENIS UJIAN.
     * Misal: Jenis "CPNS" > Sub Jenis "TWK, TIU, TKP"
     * 
     * Menggunakan EAGER LOADING (->with()):
     * - Tanpa: N+1 query problem (1 query/jenis)
     * - Dengan: Hanya 2 query total (cepat)
     * 
     * Relasi terurut: urutan (drag-drop), lalu nama_sub_jenis_ujian (A-Z).
     * Anchor (#jenis-X) pada redirect untuk scroll ke grup yang diubah.
     * 
     * Form: Modal tambah & edit di index view
     * 
     * Debug Mode (uncomment untuk trace):
     * // dd($jenisUjians->map(fn($j) => ['jenis' => $j->nama_jenis_ujian, 'sub_count' => $j->subJenisUjian->count()])->toArray());
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
     * METHOD: store()
     * ===============
     * URL Contoh: POST /superadmin/sub-jenis-ujian
     * 
     * Keterangan:
     * Menyimpan SUB JENIS UJIAN baru (misal: "TWK" untuk jenis "CPNS").
     * 
     * Validasi: SoalRequest form request validator
     * Redirect anchor (#jenis-X) untuk auto-scroll ke grup jenis yang baru dibuat.
     * 
     * Form: Modal di index view (POST method)
     *       Input: nama_sub_jenis_ujian, jenis_ujian_id, sistem_penilaian, nilai_benar, dll
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Store sub jenis ujian', ['jenis_id' => $request->jenis_ujian_id, 'nama' => $request->nama_sub_jenis_ujian]);
     */
    public function store(SubJenisUjianRequest $request): RedirectResponse
    {
        $subJenisUjian = SubJenisUjian::create($request->validated());

        return redirect(route('superadmin.sub-jenis-ujian.index').'#jenis-'.$subJenisUjian->jenis_ujian_id)
            ->with('success', 'Sub jenis ujian berhasil dibuat.');
    }

    /**
     * METHOD: update()
     * ================
     * URL Contoh: PUT /superadmin/sub-jenis-ujian/{subJenisUjian}
     * 
     * Keterangan:
     * Mengubah data SUB JENIS UJIAN yang sudah ada.
     * Parameter {subJenisUjian} diambil via Route Model Binding (otomatis dicari by ID).
     * 
     * Redirect anchor (#jenis-X) untuk auto-scroll ke grup yang diubah.
     * 
     * Form: Modal di index view (PUT method)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Update sub jenis ujian ID '.$subJenisUjian->id, ['nama' => $subJenisUjian->nama_sub_jenis_ujian]);
     */
    public function update(SubJenisUjianRequest $request, SubJenisUjian $subJenisUjian): RedirectResponse
    {
        $subJenisUjian->update($request->validated());

        return redirect(route('superadmin.sub-jenis-ujian.index').'#jenis-'.$subJenisUjian->jenis_ujian_id)
            ->with('success', 'Sub jenis ujian berhasil diupdate.');
    }

    /**
     * METHOD: destroy()
     * =================
     * URL Contoh: DELETE /superadmin/sub-jenis-ujian/{subJenisUjian}
     * 
     * Keterangan:
     * Menghapus SUB JENIS UJIAN beserta semua sub-indikator & soal yang belong to-nya.
     * 
     * Redirect anchor (#jenis-X) untuk auto-scroll ke grup jenis setelah delete.
     * 
     * Form: Tombol delete di index view (via form DELETE method)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Delete sub jenis ujian ID '.$subJenisUjian->id);
     */
    public function destroy(SubJenisUjian $subJenisUjian): RedirectResponse
    {
        $jenisUjianId = $subJenisUjian->jenis_ujian_id;
        $subJenisUjian->delete();

        return redirect(route('superadmin.sub-jenis-ujian.index').'#jenis-'.$jenisUjianId)
            ->with('success', 'Sub jenis ujian berhasil dihapus.');
    }
}
