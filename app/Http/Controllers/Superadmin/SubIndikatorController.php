<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubIndikatorRequest;
use App\Models\SubIndikator;
use App\Models\SubJenisUjian;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubIndikatorController extends Controller
{
    public function index(): View
    {
        $subIndikators = SubIndikator::with('subJenisUjian.jenisUjian')
            ->orderBy('nama_sub_indikator')
            ->paginate(15);

        $subJenisUjians = SubJenisUjian::with('jenisUjian')
            ->orderBy('nama_sub_jenis_ujian')
            ->get();

        return view('superadmin.sub-indikator.index', compact('subIndikators', 'subJenisUjians'));
    }

    public function store(SubIndikatorRequest $request): RedirectResponse
    {
        SubIndikator::create($request->validatedWithJenisUjian());

        return redirect()->route('superadmin.sub-indikator.index')
            ->with('success', 'Sub indikator berhasil dibuat.');
    }

    public function update(SubIndikatorRequest $request, SubIndikator $subIndikator): RedirectResponse
    {
        $subIndikator->update($request->validatedWithJenisUjian());

        return redirect()->route('superadmin.sub-indikator.index')
            ->with('success', 'Sub indikator berhasil diupdate.');
    }

    public function destroy(SubIndikator $subIndikator): RedirectResponse
    {
        $subIndikator->delete();

        return redirect()->route('superadmin.sub-indikator.index')
            ->with('success', 'Sub indikator berhasil dihapus.');
    }
}
