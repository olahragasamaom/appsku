<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LatihanSederhanaRequest;
use App\Models\LatihanSederhana;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LatihanSederhanaController extends Controller
{
    /**
     * Tampilkan daftar data (index)
     */
    public function index(): View
    {
        $items = LatihanSederhana::orderBy('id', 'desc')->paginate(15);

        return view('superadmin.latihan-sederhana.index', compact('items'));
    }

    /**
     * Form tambah data baru
     */
    public function create(): View
    {
        return view('superadmin.latihan-sederhana.create');
    }

    /**
     * Simpan data baru
     */
    public function store(LatihanSederhanaRequest $request): RedirectResponse
    {
        LatihanSederhana::create($request->validated());

        return redirect()->route('superadmin.latihan-sederhana.index')
            ->with('success', 'Data latihan sederhana berhasil disimpan.');
    }

    /**
     * Form edit data
     */
    public function edit(LatihanSederhana $latihan_sederhana): View
    {
        return view('superadmin.latihan-sederhana.edit', compact('latihan_sederhana'));
    }

    /**
     * Update data
     */
    public function update(LatihanSederhanaRequest $request, LatihanSederhana $latihan_sederhana): RedirectResponse
    {
        $latihan_sederhana->update($request->validated());

        return redirect()->route('superadmin.latihan-sederhana.index')
            ->with('success', 'Data latihan sederhana berhasil diupdate.');
    }

    /**
     * Hapus data
     */
    public function destroy(LatihanSederhana $latihan_sederhana): RedirectResponse
    {
        $latihan_sederhana->delete();

        return redirect()->route('superadmin.latihan-sederhana.index')
            ->with('success', 'Data latihan sederhana berhasil dihapus.');
    }
}
