<?php

namespace App\Http\Controllers\PembuatSoal;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('pembuat-soal.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if (!$user->isPembuatSoal()) {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'Akun ini tidak memiliki akses pembuat soal.',
                ]);
            }

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'Akun Anda tidak aktif.',
                ]);
            }

            $request->session()->regenerate();
            return redirect()->route('pembuat-soal.dashboard');
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
        return redirect()->route('pembuat-soal.login');
    }
}
