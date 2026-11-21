<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKeuangan
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isKeuangan()) {
            return redirect()->route('keuangan.login');
        }

        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('keuangan.login')->withErrors(['username' => 'Akun Anda tidak aktif.']);
        }

        return $next($request);
    }
}
