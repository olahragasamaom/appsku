<?php

namespace App\Http\Controllers\Pengajar;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('pengajar.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if (! $user->isPengajar()) {
                Auth::logout();

                return back()->withErrors([
                    'username' => 'Akun ini tidak memiliki akses pengajar.',
                ]);
            }

            $request->session()->regenerate();

            return redirect()->route('pengajar.dashboard');
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

        return redirect()->route('pengajar.login');
    }
}
