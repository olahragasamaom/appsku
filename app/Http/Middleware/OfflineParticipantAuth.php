<?php

namespace App\Http\Middleware;

use App\Models\UjianPeserta;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OfflineParticipantAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $pesertaId = $request->session()->get('offline_peserta_id');

        if (! $pesertaId) {
            abort(403, 'Sesi peserta offline tidak ditemukan.');
        }

        $routeUjianId = (int) optional($request->route('ujian'))->id
            ?: (int) $request->route('ujian');

        $sessionUjianId = (int) $request->session()->get('offline_ujian_id');

        if ($routeUjianId && $sessionUjianId !== $routeUjianId) {
            abort(403, 'Akses ujian tidak valid.');
        }

        $attemptId = $request->session()->get('offline_attempt_id');
        $attempt = $attemptId ? UjianPeserta::find($attemptId) : null;

        if ($attempt && $attempt->status === 'selesai' && ! $this->allowsFinishedAttempt($request)) {
            $ujian = $attempt->ujian;

            if ($ujian?->tampilkan_hasil) {
                return redirect()->route('peserta.ujian.hasil', $ujian);
            }

            abort(403, 'Ujian sudah selesai.');
        }

        return $next($request);
    }

    /**
     * Routes that remain reachable after an attempt is already selesai
     * (e.g. viewing the result or the answer review).
     */
    private function allowsFinishedAttempt(Request $request): bool
    {
        return in_array($request->route()?->getName(), [
            'peserta.ujian.hasil',
            'peserta.ujian.pembahasan',
        ], true);
    }
}
