<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureExamAccess
{
    /**
     * Allow exam execution for either an authenticated peserta (online)
     * or a valid offline participant session (C-4/§8.3).
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->has('offline_peserta_id')) {
            return app(OfflineParticipantAuth::class)->handle($request, $next);
        }

        return app(EnsurePeserta::class)->handle($request, $next);
    }
}
