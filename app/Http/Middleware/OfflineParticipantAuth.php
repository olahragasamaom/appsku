<?php

namespace App\Http\Middleware;

use App\Models\OfflineParticipantSession;
use App\Models\UjianPeserta;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OfflineParticipantAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check if offline participant session exists
        $offlinePesertaId = $request->session()->get('offline_peserta_id');
        $offlineSessionToken = $request->session()->get('offline_session_token');
        $offlineUjianId = $request->session()->get('offline_ujian_id');
        $offlineAttemptId = $request->session()->get('offline_attempt_id');

        if (! $offlinePesertaId || ! $offlineSessionToken || ! $offlineUjianId || ! $offlineAttemptId) {
            abort(403, 'Sesi offline tidak valid.');
        }

        // 2. Validate session token (anti multi-device)
        $participantSession = OfflineParticipantSession::where('session_token', $offlineSessionToken)
            ->whereIn('status', ['logged_in', 'sedang_ujian'])
            ->first();

        if (! $participantSession) {
            $request->session()->forget([
                'offline_peserta_id',
                'offline_session_token',
                'offline_ujian_id',
                'offline_attempt_id',
            ]);
            abort(403, 'Sesi Anda telah berakhir karena login dari perangkat lain. Silakan login kembali.');
        }

        // 3. Update last_activity_at (untuk monitoring "online" status)
        $participantSession->update(['last_activity_at' => now()]);

        // 4. Check if ujian_id matches route parameter
        $routeUjian = $request->route('ujian');
        $routeUjianId = is_object($routeUjian) ? $routeUjian->id : (int) $routeUjian;

        if ($offlineUjianId != $routeUjianId) {
            abort(403, 'Akses ke ujian ini tidak diizinkan.');
        }

        // 5. Check attempt status
        $attempt = UjianPeserta::find($offlineAttemptId);
        if (! $attempt) {
            $request->session()->forget([
                'offline_peserta_id',
                'offline_session_token',
                'offline_ujian_id',
                'offline_attempt_id',
            ]);
            abort(403, 'Sesi ujian tidak ditemukan.');
        }

        if ($attempt->status === 'selesai') {
            $routeName = $request->route()->getName();
            // Allow access to pembahasan and hasil routes when selesai
            if (! in_array($routeName, ['peserta.ujian.pembahasan', 'peserta.ujian.hasil'])) {
                $ujian = $attempt->ujian;
                if ($ujian->tampilkan_hasil) {
                    return redirect()->route('peserta.ujian.hasil', $ujian->id);
                } else {
                    abort(403, 'Ujian sudah selesai.');
                }
            }
        }

        $routeName = $request->route()->getName();
        // Allow selesai status for pembahasan and hasil routes
        if ($attempt->status !== 'sedang_ujian' && ! in_array($routeName, ['peserta.ujian.pembahasan', 'peserta.ujian.hasil'])) {
            abort(403, 'Status ujian tidak valid.');
        }

        // 6. Validate activation is still active (admin dapat menonaktifkan mid-exam)
        $peserta = $attempt->pesertaOffline;
        if ($peserta) {
            // Check if peserta is blocked (bisa diblokir mid-exam)
            if ($peserta->is_blocked) {
                $request->session()->forget([
                    'offline_peserta_id',
                    'offline_session_token',
                    'offline_ujian_id',
                    'offline_attempt_id',
                ]);
                abort(403, 'Akun Anda telah diblokir oleh pengawas. Alasan: '.($peserta->blocked_reason ?? 'Tidak dijelaskan'));
            }

            // Jika admin menonaktifkan peserta mid-exam, hentikan akses ujian
            if (! $peserta->is_active) {
                abort(403, 'Ujian Anda dinonaktifkan oleh pengawas. Silakan hubungi pengawas.');
            }
        }

        return $next($request);
    }
}
