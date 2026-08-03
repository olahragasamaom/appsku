<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePeserta
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isPeserta()) {
            return redirect()->route('peserta.login');
        }

        if (! $user->is_active) {
            return redirect()->route('peserta.login')->withErrors([
                'username' => 'Akun Anda tidak aktif.',
            ]);
        }

        return $next($request);
    }
}
