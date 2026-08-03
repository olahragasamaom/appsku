<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Paket;
use App\Models\PesertaLangganan;
use App\Services\Ujian\PesertaLanggananService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LanggananController extends Controller
{
    public function __construct(
        private readonly PesertaLanggananService $service
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $aktif = PesertaLangganan::query()
            ->with('paket')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        $riwayat = PesertaLangganan::query()
            ->with(['paket', 'pembayaran'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $pakets = Paket::where('is_active', true)->orderBy('urutan')->orderBy('harga')->get();

        return view('peserta.langganan.index', compact('aktif', 'riwayat', 'pakets'));
    }

    public function pilih(Request $request, Paket $paket): RedirectResponse
    {
        $user = $request->user();

        $adaAktif = PesertaLangganan::where('user_id', $user->id)
            ->where('status', 'active')
            ->whereHas('paket', fn ($q) => $q->where('id', $paket->id))
            ->exists();

        if ($adaAktif) {
            return back()->with('error', 'Anda sudah memiliki langganan aktif untuk paket ini.');
        }

        $result = $this->service->subscribe($user, $paket);

        if ($paket->isGratis()) {
            return redirect()->route('peserta.langganan.index')
                ->with('success', 'Paket gratis berhasil diaktifkan!');
        }

        if ($result['invoice_url']) {
            return redirect()->away($result['invoice_url']);
        }

        return redirect()->route('peserta.langganan.bayar', $result['pembayaran']->id);
    }

    public function bayar(Request $request, int $pembayaran): View
    {
        $bayar = \App\Models\PesertaPembayaran::where('user_id', $request->user()->id)
            ->with('paket')
            ->findOrFail($pembayaran);

        abort_unless($bayar->status === 'pending', 404);

        return view('peserta.langganan.bayar', compact('bayar'));
    }

    public function statusPembayaran(Request $request, int $pembayaran): View
    {
        $bayar = \App\Models\PesertaPembayaran::where('user_id', $request->user()->id)
            ->with('paket', 'langganan')
            ->findOrFail($pembayaran);

        return view('peserta.langganan.status', compact('bayar'));
    }
}
