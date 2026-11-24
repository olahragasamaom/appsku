<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
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

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'login' => __('auth.failed'),
            ])->onlyInput('login');
        }

        $user = Auth::user();

        // Check if user is active
        if (! $user->is_active) {
            Auth::logout();

            return back()->withErrors([
                'login' => 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.',
            ])->onlyInput('login');
        }

        // Check if company is active (for non-super-admin)
        if ($user->company_id && $user->company && ! $user->company->is_active) {
            Auth::logout();

            return back()->withErrors([
                'login' => 'Perusahaan Anda telah dinonaktifkan. Silakan hubungi administrator.',
            ])->onlyInput('login');
        }

        $request->session()->regenerate();

        // Redirect pengawas to pengawas dashboard
        if ($user->isPengawas()) {
            return redirect()->intended(route('pengawas.dashboard'));
        }

        // Redirect employee to portal, admin/hr to dashboard
        if ($user->hasRole('employee') && ! $user->hasAnyRole(['admin', 'hr-manager', 'payroll-manager'])) {
            return redirect()->intended(route('portal.dashboard'));
        }

        return redirect()->intended('/dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
