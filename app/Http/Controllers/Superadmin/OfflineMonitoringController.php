<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\OfflineParticipantSession;
use App\Models\PesertaOffline;
use App\Models\Ujian;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * CONTROLLER: OfflineMonitoringController
 * ========================================
 * Real-time monitoring pengawas untuk ujian offline.
 * Menampilkan status login, sedang ujian, dan selesai per peserta.
 * Support force logout jika terdeteksi kecurangan.
 */
class OfflineMonitoringController extends Controller
{
    public function index(Ujian $ujian): View
    {
        if ($ujian->tipe_ujian !== 'offline_kelas') {
            abort(404, 'Hanya untuk ujian offline.');
        }

        $pesertaOffline = PesertaOffline::where('ujian_id', $ujian->id)
            ->orderBy('nomor_peserta')
            ->get();

        return view('superadmin.ujian.monitoring.index', compact('ujian', 'pesertaOffline'));
    }

    public function live(Ujian $ujian): JsonResponse
    {
        if ($ujian->tipe_ujian !== 'offline_kelas') {
            abort(404);
        }

        $pesertaOffline = PesertaOffline::where('ujian_id', $ujian->id)->get();

        // Get all active sessions for this ujian's peserta
        $pesertaIds = $pesertaOffline->pluck('id');
        $activeSessions = OfflineParticipantSession::whereIn('peserta_offline_id', $pesertaIds)
            ->whereIn('status', ['logged_in', 'sedang_ujian'])
            ->latest('last_activity_at')
            ->get()
            ->keyBy('peserta_offline_id');

        $participants = $pesertaOffline->map(function ($peserta) use ($activeSessions) {
            $session = $activeSessions->get($peserta->id);

            $statusUjian = 'offline';
            $isOnline = false;
            $lastActivity = null;
            $ipAddress = null;
            $sessionId = null;
            $attemptId = $peserta->ujian_peserta_id;

            if ($session) {
                $sessionId = $session->id;
                $lastActivity = $session->last_activity_at?->toIso8601String();
                $ipAddress = $session->device_info['ip'] ?? null;

                // Consider online jika activity dalam 30 detik terakhir
                $isOnline = $session->last_activity_at
                    && $session->last_activity_at->gt(now()->subSeconds(30));

                if ($session->status === 'sedang_ujian') {
                    $statusUjian = $isOnline ? 'sedang_ujian_online' : 'sedang_ujian_idle';
                } elseif ($session->status === 'logged_in') {
                    $statusUjian = $isOnline ? 'logged_in_online' : 'logged_in_idle';
                }
            }

            return [
                'peserta_offline_id' => $peserta->id,
                'nomor_peserta' => $peserta->nomor_peserta,
                'nama_peserta' => $peserta->nama_peserta,
                'is_active' => (bool) $peserta->is_active,
                'status_ujian' => $statusUjian,
                'is_online' => $isOnline,
                'last_activity' => $lastActivity,
                'ip_address' => $ipAddress,
                'session_id' => $sessionId,
                'attempt_id' => $attemptId,
                'is_blocked' => (bool) $peserta->is_blocked,
                'blocked_reason' => $peserta->blocked_reason,
            ];
        });

        // Statistics
        $totalPeserta = $pesertaOffline->count();
        $totalAktif = $pesertaOffline->filter(fn ($p) => (bool) $p->is_active)->count();
        $loggedIn = $activeSessions->count();
        $sedangUjian = $activeSessions->filter(fn ($s) => $s->status === 'sedang_ujian')->count();
        $selesai = OfflineParticipantSession::whereIn('peserta_offline_id', $pesertaIds)
            ->where('status', 'selesai')
            ->count();

        return response()->json([
            'ujian_id' => $ujian->id,
            'ujian_nama' => $ujian->nama_ujian,
            'ujian_status' => $ujian->status,
            'stats' => [
                'total_peserta' => $totalPeserta,
                'total_aktif' => $totalAktif,
                'logged_in' => $loggedIn,
                'sedang_ujian' => $sedangUjian,
                'selesai' => $selesai,
            ],
            'participants' => $participants,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function forceLogout(Ujian $ujian, OfflineParticipantSession $session): RedirectResponse
    {
        $session->update([
            'status' => 'logout',
            'logout_at' => now(),
            'logout_reason' => 'Force logout oleh pengawas',
        ]);

        return redirect()->route('superadmin.ujian.pengawas.index', $ujian->id)
            ->with('success', "Peserta {$session->pesertaOffline->nomor_peserta} berhasil di-logout paksa.");
    }
}
