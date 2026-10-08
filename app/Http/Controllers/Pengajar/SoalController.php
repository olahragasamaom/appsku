<?php

namespace App\Http\Controllers\Pengajar;

use App\Http\Controllers\Controller;
use App\Models\Soal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SoalController extends Controller
{
    public function index(): View
    {
        $pengajar = auth()->user();

        $soal = Soal::where('created_by', $pengajar->id)
            ->latest()
            ->paginate(15);

        return view('pengajar.soal.index', compact('soal'));
    }

    public function create(): View
    {
        return view('pengajar.soal.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pertanyaan' => ['required', 'string'],
            'opsi_a' => ['required', 'string'],
            'opsi_b' => ['required', 'string'],
            'opsi_c' => ['required', 'string'],
            'opsi_d' => ['required', 'string'],
            'opsi_e' => ['required', 'string'],
            'jawaban' => ['required', 'in:A,B,C,D,E'],
            'sub_jenis_ujian_id' => ['required', 'exists:sub_jenis_ujians,id'],
            'sub_indikator_id' => ['required', 'exists:sub_indikators,id'],
        ]);

        $pengajar = auth()->user();

        Soal::create([
            ...$validated,
            'pembuat_soal_id' => $pengajar->id,
        ]);

        return redirect()->route('pengajar.soal.index')
            ->with('success', 'Soal berhasil dibuat.');
    }

    public function show(Soal $soal): View
    {
        $this->authorize('view', $soal);

        return view('pengajar.soal.show', compact('soal'));
    }

    public function edit(Soal $soal): View
    {
        $this->authorize('update', $soal);

        return view('pengajar.soal.edit', compact('soal'));
    }

    public function update(Request $request, Soal $soal): RedirectResponse
    {
        $this->authorize('update', $soal);

        $validated = $request->validate([
            'pertanyaan' => ['required', 'string'],
            'opsi_a' => ['required', 'string'],
            'opsi_b' => ['required', 'string'],
            'opsi_c' => ['required', 'string'],
            'opsi_d' => ['required', 'string'],
            'opsi_e' => ['required', 'string'],
            'jawaban' => ['required', 'in:A,B,C,D,E'],
            'sub_jenis_ujian_id' => ['required', 'exists:sub_jenis_ujians,id'],
            'sub_indikator_id' => ['required', 'exists:sub_indikators,id'],
        ]);

        $soal->update($validated);

        return redirect()->route('pengajar.soal.index')
            ->with('success', 'Soal berhasil diperbarui.');
    }

    public function destroy(Soal $soal): RedirectResponse
    {
        $this->authorize('delete', $soal);

        $soal->delete();

        return redirect()->route('pengajar.soal.index')
            ->with('success', 'Soal berhasil dihapus.');
    }
}
