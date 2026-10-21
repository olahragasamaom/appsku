<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Mail\PaymentRejectedMail;
use App\Mail\PaymentVerifiedMail;
use App\Models\PaymentNotification;
use App\Models\PesertaLangganan;
use App\Models\PesertaPembayaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PembayaranController extends Controller
{
    public function index(Request $request): View
    {
        $pembayaran = PesertaPembayaran::with(['user', 'paket'])
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->search, function ($query) use ($request) {
                $search = "%{$request->search}%";
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search);
                })->orWhere('nomor_pembayaran', 'like', $search);
            })
            ->latest()
            ->paginate(15);

        return view('superadmin.pembayaran.index', compact('pembayaran'));
    }

    public function show(PesertaPembayaran $pembayaran): View
    {
        $pembayaran->load(['user', 'paket', 'langganan']);

        return view('superadmin.pembayaran.show', compact('pembayaran'));
    }

    public function verifikasi(Request $request, PesertaPembayaran $pembayaran): RedirectResponse
    {
        abort_unless($pembayaran->isPending(), 404);

        $validated = $request->validate([
            'catatan_verifikasi' => ['nullable', 'string', 'max:500'],
        ]);

        $pembayaran->verifikasiPembayaran(
            $request->user()->email,
            $validated['catatan_verifikasi'] ?? ''
        );

        // Aktivasi langganan jika belum aktif
        if (! $pembayaran->langganan || $pembayaran->langganan->status !== 'active') {
            PesertaLangganan::create([
                'user_id' => $pembayaran->user_id,
                'paket_id' => $pembayaran->paket_id,
                'status' => 'active',
                'mulai_pada' => now(),
                'berakhir_pada' => now()->addDays($pembayaran->paket->durasi_hari),
                'sisa_kuota_ujian' => $pembayaran->paket->kuota_ujian,
            ]);
        }

        // Send in-app notification
        PaymentNotification::create([
            'user_id' => $pembayaran->user_id,
            'pembayaran_id' => $pembayaran->id,
            'type' => 'verified',
            'title' => 'Pembayaran Berhasil Diverifikasi',
            'message' => 'Paket '.$pembayaran->paket->nama_paket.' sudah diaktifkan. Selamat belajar!',
            'data' => [
                'paket_id' => $pembayaran->paket_id,
                'paket_nama' => $pembayaran->paket->nama_paket,
            ],
        ]);

        // Send email
        Mail::to($pembayaran->user->email)->send(new PaymentVerifiedMail($pembayaran));

        return redirect()->route('superadmin.pembayaran.show', $pembayaran)
            ->with('success', 'Pembayaran berhasil diverifikasi dan langganan diaktifkan.');
    }

    public function tolak(Request $request, PesertaPembayaran $pembayaran): RedirectResponse
    {
        abort_unless($pembayaran->isPending(), 404);

        $validated = $request->validate([
            'alasan' => ['required', 'string', 'max:500'],
        ]);

        $pembayaran->tolakPembayaran(
            $request->user()->email,
            $validated['alasan']
        );

        // Send in-app notification
        PaymentNotification::create([
            'user_id' => $pembayaran->user_id,
            'pembayaran_id' => $pembayaran->id,
            'type' => 'rejected',
            'title' => 'Pembayaran Perlu Diverifikasi Ulang',
            'message' => 'Bukti pembayaran Anda tidak sesuai. Silakan upload bukti yang benar.',
            'data' => [
                'alasan' => $validated['alasan'],
            ],
        ]);

        // Send email
        Mail::to($pembayaran->user->email)->send(
            new PaymentRejectedMail($pembayaran, $validated['alasan'])
        );

        return redirect()->route('superadmin.pembayaran.show', $pembayaran)
            ->with('error', 'Pembayaran ditolak. Peserta dapat mengirim ulang bukti pembayaran.');
    }
}
