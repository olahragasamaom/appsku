<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OAuthController extends Controller
{
    private function isEnabled(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'));
    }

    public function redirect(Request $request): RedirectResponse
    {
        abort_unless($this->isEnabled(), 404);

        $state = Str::random(40);
        Cache::put("oauth_state_{$state}", true, now()->addMinutes(5));

        $query = http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => route('peserta.auth.google.callback'),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
        ]);

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?'.$query);
    }

    public function callback(Request $request): RedirectResponse
    {
        abort_unless($this->isEnabled(), 404);

        $state = $request->query('state', '');
        if (! Cache::pull("oauth_state_{$state}")) {
            return redirect()->route('peserta.login')->withErrors(['username' => 'State OAuth tidak valid.']);
        }

        $code = $request->query('code');
        if (! $code) {
            return redirect()->route('peserta.login')->withErrors(['username' => 'Otorisasi Google gagal.']);
        }

        $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri' => route('peserta.auth.google.callback'),
            'grant_type' => 'authorization_code',
        ]);

        if (! $tokenResponse->successful()) {
            return redirect()->route('peserta.login')->withErrors(['username' => 'Gagal menukar token Google.']);
        }

        $accessToken = $tokenResponse->json('access_token');

        $profileResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v3/userinfo');

        if (! $profileResponse->successful()) {
            return redirect()->route('peserta.login')->withErrors(['username' => 'Gagal mengambil profil Google.']);
        }

        $profile = $profileResponse->json();
        $email = $profile['email'] ?? null;
        $name = $profile['name'] ?? 'Peserta Google';

        if (! $email) {
            return redirect()->route('peserta.login')->withErrors(['username' => 'Akun Google tidak memiliki email.']);
        }

        $user = User::where('email', $email)->first();

        if ($user && ! $user->isPeserta()) {
            return redirect()->route('peserta.login')->withErrors(['username' => 'Email ini terdaftar sebagai akun lain.']);
        }

        if (! $user) {
            $username = Str::slug(explode('@', $email)[0]).Str::random(4);

            while (User::where('username', $username)->exists()) {
                $username = Str::slug(explode('@', $email)[0]).Str::random(4);
            }

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'username' => $username,
                'password' => Hash::make(Str::random(32)),
                'email_verified_at' => now(),
                'company_id' => null,
                'is_active' => true,
                'is_peserta' => true,
            ]);
        }

        if (! $user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('peserta.dashboard');
    }
}
