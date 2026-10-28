<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthToHome
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
