<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\PesertaOffline;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KelasController extends Controller
{
    public function index(Request $request): View
    {
        $query = Kelas::withCount('pesertaOffline');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_kelas', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $kelasList = $query->orderBy('nama_kelas', 'asc')
            ->paginate(15)
            ->withQueryString();

        $totalKelas = Kelas::count();
        $totalAktif = Kelas::where('status', 'aktif')->count();
        $totalPeserta = PesertaOffline::whereNotNull('kelas_id')->count();

        return view('superadmin.kelas.index', compact('kelasList', 'totalKelas', 'totalAktif', 'totalPeserta'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:255', 'unique:panritta_kelas,nama_kelas'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ], [
            'nama_kelas.required' => 'Nama kelas wajib diisi.',
            'nama_kelas.unique' => 'Nama kelas ini sudah terdaftar.',
            'status.in' => 'Status tidak valid.',
        ]);

        Kelas::create($validated);

        return redirect()->route('superadmin.kelas.index')
            ->with('success', "Kelas '{$validated['nama_kelas']}' berhasil ditambahkan.");
    }

    public function update(Request $request, Kelas $kelas): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:255', Rule::unique('panritta_kelas', 'nama_kelas')->ignore($kelas->id)],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ], [
            'nama_kelas.required' => 'Nama kelas wajib diisi.',
            'nama_kelas.unique' => 'Nama kelas ini sudah terdaftar.',
            'status.in' => 'Status tidak valid.',
        ]);

        $oldName = $kelas->nama_kelas;
        $kelas->update($validated);

        if ($oldName !== $kelas->nama_kelas) {
            PesertaOffline::where('kelas_id', $kelas->id)->update([
                'nama_kelas' => $kelas->nama_kelas,
            ]);
        }

        return redirect()->route('superadmin.kelas.index')
            ->with('success', "Data kelas '{$kelas->nama_kelas}' berhasil diperbarui.");
    }

    public function destroy(Kelas $kelas): RedirectResponse
    {
        $namaKelas = $kelas->nama_kelas;
        PesertaOffline::where('kelas_id', $kelas->id)->update([
            'kelas_id' => null,
        ]);

        $kelas->delete();

        return redirect()->route('superadmin.kelas.index')
            ->with('success', "Kelas '{$namaKelas}' berhasil dihapus.");
    }
}
