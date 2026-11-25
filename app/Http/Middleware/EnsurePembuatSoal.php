<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePembuatSoal
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || (! $user->isPembuatSoal() && ! $user->isPengajar() && ! $user->isSuperAdmin())) {
            return redirect()->route('home');
        }

        if (! $user->is_active) {
            auth()->logout();

            return redirect()->route('home')->withErrors(['username' => 'Akun Anda tidak aktif.']);
        }

        return $next($request);
    }
}
