<?php

namespace App\Http\Controllers\Pengawas;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('pengawas.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $loginField = filter_var($request->input('login'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $loginField => $request->input('login'),
            'password' => $request->input('password'),
        ];

        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! auth()->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'login' => 'Email/username atau password salah.',
            ])->onlyInput('login');
        }

        $user = auth()->user();

        if (! $user->is_active || ! $user->isPengawas()) {
            auth()->logout();

            return back()->withErrors([
                'login' => 'Akun Anda tidak memiliki akses pengawas.',
            ])->onlyInput('login');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('pengawas.dashboard'));
    }
}
