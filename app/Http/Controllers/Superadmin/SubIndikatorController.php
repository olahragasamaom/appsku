<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubIndikatorRequest;
use App\Models\SubIndikator;
use App\Models\SubJenisUjian;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * CONTROLLER: SubIndikatorController
 * ===================================
 * CRUD untuk Sub Indikator. Method index()-nya paling "kaya" karena data
 * ditampilkan bertingkat: dikelompokkan per Jenis Ujian, lalu per Sub Jenis.
 *
 * Perhatikan store()/update() memanggil validatedWithJenisUjian() (bukan
 * validated() biasa) -> lihat penjelasannya di SubIndikatorRequest.
 *
 * PETA ROUTE -> METHOD:
 *   GET    /superadmin/sub-indikator                  -> index()
 *   POST   /superadmin/sub-indikator                  -> store()
 *   PUT    /superadmin/sub-indikator/{subIndikator}   -> update()
 *   DELETE /superadmin/sub-indikator/{subIndikator}   -> destroy()
 */
class SubIndikatorController extends Controller
{
    /**
     * METHOD: index()
     * ===============
     * URL Contoh: GET /superadmin/sub-indikator
     * 
     * Keterangan:
     * Menampilkan DAFTAR SUB INDIKATOR dengan pengelompokan 2 tingkat:
     * - Tingkat 1: Jenis Ujian (CPNS, ASN, etc)
     * - Tingkat 2: Sub Jenis Ujian (TWK, TIU, TKP)
     * - Tingkat 3: Sub Indikator (Pancasila, UUD 1945, etc)
     * 
     * Eager Loading: ->with('subJenisUjian.jenisUjian')
     * - Cegah N+1 query problem
     * - Load semua relasi berantai dalam 2 query total
     * 
     * GroupBy: groupBy() in-memory bukan SQL
     * - Kelompok koleksi per nama jenis + nama sub jenis
     * - Nullsafe operator (?->) jika relasi kosong
     * 
     * Form: Modal tambah & edit di index view
     * 
     * Debug Mode (uncomment untuk trace):
     * // dd($groupedSubIndikators->map(fn($jenis) => $jenis->map(fn($sub) => $sub->count())->toArray())->toArray());
     */
    public function index(): View
    {
        $subIndikators = SubIndikator::with('subJenisUjian.jenisUjian')
            ->orderBy('nama_sub_indikator')
            ->get();

        $groupedSubIndikators = $subIndikators
            ->groupBy(fn (SubIndikator $subIndikator): string => $subIndikator->subJenisUjian?->jenisUjian?->nama_jenis_ujian ?? 'Tanpa Jenis Ujian')
            ->map(fn ($items) => $items->groupBy(fn (SubIndikator $subIndikator): string => $subIndikator->subJenisUjian?->nama_sub_jenis_ujian ?? 'Tanpa Sub Jenis Ujian'));

        $subJenisUjians = SubJenisUjian::with('jenisUjian')
            ->orderBy('nama_sub_jenis_ujian')
            ->get();

        return view('superadmin.sub-indikator.index', compact('groupedSubIndikators', 'subJenisUjians'));
    }

    /**
     * METHOD: store()
     * ===============
     * URL Contoh: POST /superadmin/sub-indikator
     * 
     * Keterangan:
     * Menyimpan SUB INDIKATOR baru (misal: "Pancasila" untuk sub jenis "TWK").
     * 
     * Validasi: SubIndikatorRequest form request validator
     * Data: $request->validatedWithJenisUjian()
     *       (custom method yang auto-fill jenis_ujian_id dari sub_jenis_ujian_id)
     * 
     * Form: Modal di index view (POST method)
     *       Input: nama_sub_indikator, sub_jenis_ujian_id
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Store sub indikator', ['nama' => $request->nama_sub_indikator, 'sub_jenis_id' => $request->sub_jenis_ujian_id]);
     */
    public function store(SubIndikatorRequest $request): RedirectResponse
    {
        SubIndikator::create($request->validatedWithJenisUjian());

        return redirect()->back(fallback: route('superadmin.sub-indikator.index'))
            ->with('success', 'Sub indikator berhasil dibuat.');
    }

    /**
     * METHOD: update()
     * ================
     * URL Contoh: PUT /superadmin/sub-indikator/{subIndikator}
     * 
     * Keterangan:
     * Mengubah SUB INDIKATOR yang sudah ada.
     * Parameter {subIndikator} diambil via Route Model Binding.
     * 
     * Validasi & data: sama seperti store() (validatedWithJenisUjian())
     * 
     * Form: Modal di index view (PUT method)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Update sub indikator ID '.$subIndikator->id);
     */
    public function update(SubIndikatorRequest $request, SubIndikator $subIndikator): RedirectResponse
    {
        $subIndikator->update($request->validatedWithJenisUjian());

        return redirect()->route('superadmin.sub-indikator.index')
            ->with('success', 'Sub indikator berhasil diupdate.');
    }

    /**
     * METHOD: destroy()
     * =================
     * URL Contoh: DELETE /superadmin/sub-indikator/{subIndikator}
     * 
     * Keterangan:
     * Menghapus SUB INDIKATOR beserta semua soal yang belong to-nya.
     * 
     * Cascade delete: Sub Indikator > Soal (via foreign key on_delete=cascade)
     * 
     * Form: Tombol delete di index view (via form DELETE method)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::critical('Delete sub indikator ID '.$subIndikator->id.' nama: '.$subIndikator->nama_sub_indikator);
     */
    public function destroy(SubIndikator $subIndikator): RedirectResponse
    {
        $subIndikator->delete();

        return redirect()->route('superadmin.sub-indikator.index')
            ->with('success', 'Sub indikator berhasil dihapus.');
    }
}
