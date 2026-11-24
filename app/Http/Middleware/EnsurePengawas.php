<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePengawas
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isPengawas()) {
            return redirect()->route('pengawas.login');
        }

        if (! $user->is_active) {
            auth()->logout();

            return redirect()->route('pengawas.login')->withErrors(['login' => 'Akun Anda tidak aktif.']);
        }

        return $next($request);
    }
}
