<?php

use App\Models\Paket;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the landing page correctly', function () {
    // Siapkan data paket dummy
    Paket::factory()->create([
        'nama_paket' => 'Paket Gratis',
        'is_active' => true,
        'harga' => 0,
    ]);

    Paket::factory()->create([
        'nama_paket' => 'Paket Premium',
        'is_active' => true,
        'harga' => 150000,
    ]);

    $response = $this->get(route('home'));

    $response->assertSuccessful();
    $response->assertViewIs('pages.landing');

    // Pastikan konten khusus bimbel CPNS Panritta muncul
    $response->assertSee('Panritta');
    $response->assertSee('Simulasi CAT Asli');
    $response->assertSee('Paket Gratis');
    $response->assertSee('Paket Premium');
});

it('hides inactive packages from the landing page', function () {
    Paket::factory()->create([
        'nama_paket' => 'Paket Tersembunyi',
        'is_active' => false,
    ]);

    $response = $this->get(route('home'));

    $response->assertSuccessful();
    $response->assertDontSee('Paket Tersembunyi');
});
