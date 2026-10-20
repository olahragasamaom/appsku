<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

describe('Peserta OAuth Google', function () {

    it('redirects to google oauth when google credentials are configured', function () {
        Config::set('services.google.client_id', 'test-client-id');
        Config::set('services.google.client_secret', 'test-client-secret');

        $response = $this->get(route('peserta.auth.google'));

        $response->assertRedirect();
        $response->assertRedirectContains('accounts.google.com/o/oauth2/v2/auth');
    });

    it('returns 404 when google credentials are not configured', function () {
        Config::set('services.google.client_id', null);
        Config::set('services.google.client_secret', null);

        $response = $this->get(route('peserta.auth.google'));

        $response->assertStatus(404);
    });

    it('redirects to login with error when state is invalid', function () {
        Config::set('services.google.client_id', 'test-client-id');
        Config::set('services.google.client_secret', 'test-client-secret');

        $response = $this->get(route('peserta.auth.google.callback'), [
            'code' => 'auth-code',
            'state' => 'invalid-state',
        ]);

        $response->assertRedirect(route('peserta.login'));
        $response->assertSessionHasErrors('username');
    });

    it('redirects to login with error when authorization code is missing', function () {
        Config::set('services.google.client_id', 'test-client-id');
        Config::set('services.google.client_secret', 'test-client-secret');

        $state = 'valid-state';
        Cache::put("oauth_state_{$state}", true, now()->addMinutes(5));

        $response = $this->get(route('peserta.auth.google.callback'), [
            'state' => $state,
        ]);

        $response->assertRedirect(route('peserta.login'));
        $response->assertSessionHasErrors('username');
    });

    it('creates new peserta account when google oauth succeeds', function () {
        Config::set('services.google.client_id', 'test-client-id');
        Config::set('services.google.client_secret', 'test-client-secret');

        Http::fake(function ($request) {
            if (str_contains($request->url(), 'oauth2.googleapis.com/token')) {
                return Http::response([
                    'access_token' => 'test-access-token',
                    'token_type' => 'Bearer',
                ]);
            }

            if (str_contains($request->url(), 'googleapis.com/oauth2/v3/userinfo')) {
                return Http::response([
                    'email' => 'newpeserta@gmail.com',
                    'name' => 'New Peserta',
                ]);
            }

            return Http::response(['error' => 'Not found'], 404);
        });

        $state = 'valid-state';
        Cache::put("oauth_state_{$state}", true, now()->addMinutes(5));

        $response = $this->get(route('peserta.auth.google.callback').'?code=test-auth-code&state='.$state);

        $response->assertRedirect(route('peserta.dashboard'));

        $user = User::where('email', 'newpeserta@gmail.com')->first();
        expect($user)->not->toBeNull();
        expect($user->is_peserta)->toBe(true);
    });

    it('logs in existing peserta on google callback', function () {
        Config::set('services.google.client_id', 'test-client-id');
        Config::set('services.google.client_secret', 'test-client-secret');

        $existingUser = User::factory()->create([
            'email' => 'existing@gmail.com',
            'is_peserta' => true,
        ]);

        Http::fake(function ($request) {
            if (str_contains($request->url(), 'oauth2.googleapis.com/token')) {
                return Http::response([
                    'access_token' => 'test-access-token',
                    'token_type' => 'Bearer',
                ]);
            }

            if (str_contains($request->url(), 'googleapis.com/oauth2/v3/userinfo')) {
                return Http::response([
                    'email' => 'existing@gmail.com',
                    'name' => 'Existing Peserta',
                ]);
            }

            return Http::response(['error' => 'Not found'], 404);
        });

        $state = 'valid-state';
        Cache::put("oauth_state_{$state}", true, now()->addMinutes(5));

        $response = $this->get(route('peserta.auth.google.callback').'?code=test-auth-code&state='.$state);

        $response->assertRedirect(route('peserta.dashboard'));
        $this->assertAuthenticated();

        $authenticatedUser = User::where('email', 'existing@gmail.com')->first();
        expect($authenticatedUser->id)->toBe($existingUser->id);
    });

    it('rejects login when email belongs to non-peserta user', function () {
        Config::set('services.google.client_id', 'test-client-id');
        Config::set('services.google.client_secret', 'test-client-secret');

        $adminUser = User::factory()->create([
            'email' => 'admin@gmail.com',
            'is_peserta' => false,
        ]);

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'test-access-token',
                'token_type' => 'Bearer',
            ]),
            'https://www.googleapis.com/oauth2/v3/userinfo' => Http::response([
                'email' => 'admin@gmail.com',
                'name' => 'Admin User',
            ]),
        ]);

        $state = 'valid-state';
        Cache::put("oauth_state_{$state}", true, now()->addMinutes(5));

        $response = $this->get(route('peserta.auth.google.callback').'?code=test-auth-code&state='.$state);

        $response->assertRedirect(route('peserta.login'));
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    });

    it('handles google profile without email', function () {
        Config::set('services.google.client_id', 'test-client-id');
        Config::set('services.google.client_secret', 'test-client-secret');

        Http::fake([
            'oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'test-access-token',
                'token_type' => 'Bearer',
            ]),
            'www.googleapis.com/oauth2/v3/userinfo' => Http::response([
                'name' => 'User Without Email',
            ]),
        ]);

        $state = 'valid-state';
        Cache::put("oauth_state_{$state}", true, now()->addMinutes(5));

        $response = $this->get(route('peserta.auth.google.callback'), [
            'code' => 'test-auth-code',
            'state' => $state,
        ]);

        $response->assertRedirect(route('peserta.login'));
        $response->assertSessionHasErrors('username');
    });

});
