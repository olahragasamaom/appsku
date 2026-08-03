<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaRegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('peserta.auth.login');
    }

    public function showRegisterForm(): View
    {
        return view('peserta.auth.register');
    }

    public function register(PesertaRegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'company_id' => null,
            'is_active' => true,
            'is_peserta' => true,
        ]);

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('peserta.dashboard')
            ->with('success', 'Registrasi berhasil. Silakan verifikasi email Anda dan pilih paket.');
    }

    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('peserta.dashboard')->withErrors(['verify' => 'Tautan verifikasi tidak valid.']);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return redirect()->route('peserta.dashboard')->with('success', 'Email berhasil diverifikasi.');
    }

    public function resendVerification(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('peserta.dashboard');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('success', 'Tautan verifikasi telah dikirim ulang.');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if (! $user->isPeserta()) {
                Auth::logout();

                return back()->withErrors([
                    'username' => 'Akun ini tidak terdaftar sebagai peserta.',
                ])->onlyInput('username');
            }

            $request->session()->regenerate();

            return redirect()->route('peserta.dashboard');
        }

        return back()->withErrors([
            'username' => 'Username atau password tidak valid.',
        ])->onlyInput('username');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('peserta.login');
    }
}
