<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('keuangan.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if (!$user->isKeuangan()) {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'Akun ini tidak memiliki akses keuangan.',
                ]);
            }

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'Akun Anda tidak aktif.',
                ]);
            }

            $request->session()->regenerate();
            return redirect()->route('keuangan.dashboard');
        }

        return back()->withErrors([
            'username' => 'Username atau password tidak valid.',
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('keuangan.login');
    }
}
