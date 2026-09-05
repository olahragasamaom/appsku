<?php

namespace App\Http\Middleware;

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
        $offlineUjianId = $request->session()->get('offline_ujian_id');
        $offlineAttemptId = $request->session()->get('offline_attempt_id');

        if (! $offlinePesertaId || ! $offlineUjianId || ! $offlineAttemptId) {
            abort(403, 'Sesi offline tidak valid.');
        }

        // 2. Check if ujian_id matches route parameter
        $routeUjian = $request->route('ujian');
        $routeUjianId = is_object($routeUjian) ? $routeUjian->id : (int) $routeUjian;

        if ($offlineUjianId != $routeUjianId) {
            abort(403, 'Akses ke ujian ini tidak diizinkan.');
        }

        // 3. Check attempt status
        $attempt = UjianPeserta::find($offlineAttemptId);
        if (! $attempt) {
            $request->session()->forget(['offline_peserta_id', 'offline_ujian_id', 'offline_attempt_id']);
            abort(403, 'Sesi ujian tidak ditemukan.');
        }

        if ($attempt->status === 'selesai') {
            $ujian = $attempt->ujian;
            if ($ujian->tampilkan_hasil) {
                return redirect()->route('peserta.ujian.hasil', $ujian->id);
            } else {
                abort(403, 'Ujian sudah selesai.');
            }
        }

        if ($attempt->status !== 'sedang_ujian') {
            abort(403, 'Status ujian tidak valid.');
        }

        // Additional check: validate attendance is still 'hadir'
        $peserta = $attempt->pesertaOffline;
        if ($peserta) {
            $ujianForAttendance = $attempt->ujian ?? null;
            $ujianId = $ujianForAttendance ? $ujianForAttendance->id : $offlineUjianId;

            $kehadiran = $peserta->kehadiran()
                ->where('ujian_id', $ujianId)
                ->first();

            if (! $kehadiran || $kehadiran->status_kehadiran !== 'hadir') {
                abort(403, 'Status kehadiran tidak valid.');
            }
        }

        return $next($request);
    }
}
