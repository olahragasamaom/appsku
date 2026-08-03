<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\JenisUjianRequest;
use App\Models\JenisUjian;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class JenisUjianController extends Controller
{
    public function index(): View
    {
        $jenisUjians = JenisUjian::orderBy('nama_jenis_ujian')->paginate(15);

        return view('superadmin.jenis-ujian.index', compact('jenisUjians'));
    }

    public function store(JenisUjianRequest $request): RedirectResponse
    {
        JenisUjian::create($request->validated());

        return redirect()->route('superadmin.jenis-ujian.index')
            ->with('success', 'Jenis ujian berhasil dibuat.');
    }

    public function update(JenisUjianRequest $request, JenisUjian $jenisUjian): RedirectResponse
    {
        $jenisUjian->update($request->validated());

        return redirect()->route('superadmin.jenis-ujian.index')
            ->with('success', 'Jenis ujian berhasil diupdate.');
    }

    public function destroy(JenisUjian $jenisUjian): RedirectResponse
    {
        $jenisUjian->delete();

        return redirect()->route('superadmin.jenis-ujian.index')
            ->with('success', 'Jenis ujian berhasil dihapus.');
    }
}
