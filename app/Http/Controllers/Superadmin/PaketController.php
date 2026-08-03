<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaketRequest;
use App\Models\Paket;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaketController extends Controller
{
    public function index(): View
    {
        $pakets = Paket::withCount('langganan')->orderBy('urutan')->orderBy('harga')->paginate(15);

        return view('superadmin.paket.index', compact('pakets'));
    }

    public function create(): View
    {
        return view('superadmin.paket.create');
    }

    public function store(PaketRequest $request): RedirectResponse
    {
        Paket::create($this->buildData($request));

        return redirect()->route('superadmin.paket.index')
            ->with('success', 'Paket berhasil dibuat.');
    }

    public function edit(Paket $paket): View
    {
        return view('superadmin.paket.edit', compact('paket'));
    }

    public function update(PaketRequest $request, Paket $paket): RedirectResponse
    {
        $paket->update($this->buildData($request));

        return redirect()->route('superadmin.paket.index')
            ->with('success', 'Paket berhasil diupdate.');
    }

    public function destroy(Paket $paket): RedirectResponse
    {
        $paket->delete();

        return redirect()->route('superadmin.paket.index')
            ->with('success', 'Paket berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildData(PaketRequest $request): array
    {
        $data = $request->safe()->only([
            'nama_paket',
            'slug',
            'deskripsi',
            'harga',
            'durasi_hari',
            'kuota_ujian',
            'urutan',
        ]);

        $data['video_pembahasan'] = $request->boolean('video_pembahasan');
        $data['analitik'] = $request->boolean('analitik');
        $data['sertifikat'] = $request->boolean('sertifikat');
        $data['is_active'] = $request->boolean('is_active');
        $data['urutan'] = $data['urutan'] ?? 0;

        return $data;
    }
}
