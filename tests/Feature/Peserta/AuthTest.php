<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;

uses(RefreshDatabase::class);

it('renders the login page', function () {
    $response = $this->get(route('peserta.login'));

    $response->assertSuccessful();
    $response->assertViewIs('peserta.auth.login');
});

it('renders the registration page', function () {
    $response = $this->get(route('peserta.register'));

    $response->assertSuccessful();
    $response->assertViewIs('peserta.auth.register');
    $response->assertSee('Daftar Akun Baru');
});

it('registers a new user and logs them in', function () {
    Event::fake([Registered::class]);

    $response = $this->post(route('peserta.register'), [
        'name' => 'John Doe',
        'username' => 'johndoe',
        'email' => 'john@example.com',
        'phone' => '081234567890',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('peserta.dashboard'));
    $this->assertAuthenticated();

    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
        'username' => 'johndoe',
        'is_peserta' => true,
    ]);

    Event::assertDispatched(Registered::class);
});
