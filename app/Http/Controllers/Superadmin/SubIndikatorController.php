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
     * READ: tampilkan daftar dengan pengelompokan 2 tingkat.
     *
     * Langkah 1 — ambil data + eager loading BERANTAI:
     *   ->with('subJenisUjian.jenisUjian') artinya: muat SubJenisUjian tiap
     *   SubIndikator, DAN muat JenisUjian dari tiap SubJenisUjian. Titik (.)
     *   berarti "lalu masuk lebih dalam". Ini menghindari masalah N+1 query.
     *
     * Langkah 2 — kelompokkan koleksi memakai groupBy():
     *   groupBy() bekerja di MEMORI (bukan query SQL), memecah koleksi menjadi
     *   sub-koleksi berdasarkan kunci yang kita tentukan.
     *   Di sini: kelompok luar = nama Jenis Ujian, kelompok dalam = nama Sub Jenis.
     *   Operator "?->" (nullsafe) mencegah error jika relasi kosong; jika null
     *   dipakai label cadangan seperti "Tanpa Jenis Ujian".
     *
     * Langkah 3 — siapkan $subJenisUjians untuk mengisi dropdown di form modal.
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
     * CREATE: simpan sub indikator baru.
     * Memakai validatedWithJenisUjian() agar kolom jenis_ujian_id (jalan pintas)
     * ikut terisi otomatis, bukan sekadar $request->validated().
     */
    public function store(SubIndikatorRequest $request): RedirectResponse
    {
        SubIndikator::create($request->validatedWithJenisUjian());

        return redirect()->route('superadmin.sub-indikator.index')
            ->with('success', 'Sub indikator berhasil dibuat.');
    }

    /**
     * UPDATE: ubah data. Sama-sama pakai validatedWithJenisUjian().
     */
    public function update(SubIndikatorRequest $request, SubIndikator $subIndikator): RedirectResponse
    {
        $subIndikator->update($request->validatedWithJenisUjian());

        return redirect()->route('superadmin.sub-indikator.index')
            ->with('success', 'Sub indikator berhasil diupdate.');
    }

    /**
     * DELETE: hapus data.
     */
    public function destroy(SubIndikator $subIndikator): RedirectResponse
    {
        $subIndikator->delete();

        return redirect()->route('superadmin.sub-indikator.index')
            ->with('success', 'Sub indikator berhasil dihapus.');
    }
}
