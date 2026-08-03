<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubJenisUjianRequest;
use App\Models\JenisUjian;
use App\Models\SubJenisUjian;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubJenisUjianController extends Controller
{
    public function index(): View
    {
        $subJenisUjians = SubJenisUjian::with('jenisUjian')
            ->orderBy('nama_sub_jenis_ujian')
            ->paginate(15);

        $jenisUjians = JenisUjian::orderBy('nama_jenis_ujian')->get();

        return view('superadmin.sub-jenis-ujian.index', compact('subJenisUjians', 'jenisUjians'));
    }

    public function store(SubJenisUjianRequest $request): RedirectResponse
    {
        SubJenisUjian::create($request->validated());

        return redirect()->route('superadmin.sub-jenis-ujian.index')
            ->with('success', 'Sub jenis ujian berhasil dibuat.');
    }

    public function update(SubJenisUjianRequest $request, SubJenisUjian $subJenisUjian): RedirectResponse
    {
        $subJenisUjian->update($request->validated());

        return redirect()->route('superadmin.sub-jenis-ujian.index')
            ->with('success', 'Sub jenis ujian berhasil diupdate.');
    }

    public function destroy(SubJenisUjian $subJenisUjian): RedirectResponse
    {
        $subJenisUjian->delete();

        return redirect()->route('superadmin.sub-jenis-ujian.index')
            ->with('success', 'Sub jenis ujian berhasil dihapus.');
    }
}
