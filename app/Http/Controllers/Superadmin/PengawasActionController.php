<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\OfflineParticipantSession;
use App\Models\PesertaOffline;
use App\Models\TimeExtension;
use App\Models\Ujian;
use App\Models\UjianPeserta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * CONTROLLER: PengawasActionController
 * =====================================
 * Aksi-aksi pengawas untuk kontrol peserta ujian offline:
 * - Blokir/Unblokir peserta (tidak bisa login lagi)
 * - Add time extension (tambah waktu ke batas_waktu peserta)
 */
class PengawasActionController extends Controller
{
    /**
     * Blokir peserta - peserta tidak bisa login lagi.
     * Juga force logout session aktif.
     */
    public function block(Request $request, Ujian $ujian, PesertaOffline $pesertaOffline): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ], [
            'reason.required' => 'Alasan blokir wajib diisi.',
        ]);

        $pesertaOffline->update([
            'is_blocked' => true,
            'blocked_at' => now(),
            'blocked_reason' => $validated['reason'],
        ]);

        // Force logout all active sessions
        OfflineParticipantSession::where('peserta_offline_id', $pesertaOffline->id)
            ->whereIn('status', ['logged_in', 'sedang_ujian'])
            ->update([
                'status' => 'logout',
                'logout_at' => now(),
                'logout_reason' => 'Peserta diblokir: '.$validated['reason'],
            ]);

        // Jika sedang ujian, block attempt juga
        if ($pesertaOffline->ujian_peserta_id) {
            UjianPeserta::where('id', $pesertaOffline->ujian_peserta_id)
                ->update(['status' => 'diblokir']);
        }

        return redirect()->route('superadmin.ujian.pengawas.index', $ujian->id)
            ->with('success', "Peserta {$pesertaOffline->nomor_peserta} berhasil diblokir.");
    }

    /**
     * Unblokir peserta - bisa login kembali.
     */
    public function unblock(Ujian $ujian, PesertaOffline $pesertaOffline): RedirectResponse
    {
        $pesertaOffline->update([
            'is_blocked' => false,
            'blocked_at' => null,
            'blocked_reason' => null,
        ]);

        return redirect()->route('superadmin.ujian.pengawas.index', $ujian->id)
            ->with('success', "Peserta {$pesertaOffline->nomor_peserta} berhasil di-unblock.");
    }

    /**
     * Add time extension untuk attempt peserta yang sedang ujian.
     * Update batas_waktu attempt dengan tambahan menit.
     */
    public function addTimeExtension(Request $request, Ujian $ujian, UjianPeserta $ujianPeserta): RedirectResponse
    {
        $validated = $request->validate([
            'added_minutes' => ['required', 'integer', 'min:1', 'max:180'],
            'reason' => ['required', 'string', 'max:500'],
        ], [
            'added_minutes.required' => 'Jumlah menit wajib diisi.',
            'added_minutes.integer' => 'Jumlah menit harus angka.',
            'added_minutes.min' => 'Minimal 1 menit.',
            'added_minutes.max' => 'Maksimal 180 menit (3 jam).',
            'reason.required' => 'Alasan extension wajib diisi.',
        ]);

        // Validate attempt masih sedang berlangsung
        if ($ujianPeserta->status !== 'sedang_ujian') {
            return redirect()->route('superadmin.ujian.pengawas.index', $ujian->id)
                ->with('error', 'Extension hanya bisa untuk peserta yang sedang ujian.');
        }

        // Update batas_waktu (kalau null, set dari waktu_mulai + durasi + extension)
        $newBatasWaktu = $ujianPeserta->batas_waktu
            ? $ujianPeserta->batas_waktu->copy()->addMinutes($validated['added_minutes'])
            : now()->addMinutes($validated['added_minutes']);

        $ujianPeserta->update(['batas_waktu' => $newBatasWaktu]);

        // Log ke time_extensions untuk audit
        TimeExtension::create([
            'ujian_peserta_id' => $ujianPeserta->id,
            'peserta_offline_id' => $ujianPeserta->pesertaOffline?->id,
            'added_minutes' => $validated['added_minutes'],
            'reason' => $validated['reason'],
            'granted_by' => $request->user()->id,
            'granted_at' => now(),
        ]);

        return redirect()->route('superadmin.ujian.pengawas.index', $ujian->id)
            ->with('success', "Waktu ditambah {$validated['added_minutes']} menit untuk peserta ini.");
    }
}
