<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the IKU dashboard (dashboard2) for a superadmin', function () {
    $superadmin = User::factory()->create([
        'is_superadmin' => true,
        'company_id' => null,
    ]);

    $response = $this->actingAs($superadmin)->get(route('superadmin.dashboard2'));

    $response->assertSuccessful();
    $response->assertViewIs('superadmin.dashboard2');

    // Judul & 3 gauge card
    $response->assertSee('Capaian Indikator Kinerja Utama (IKU)');
    $response->assertSee('Capaian Perkin');
    $response->assertSee('Capaian Kokin');
    $response->assertSee('Capaian Renstra');

    // Section tabel
    $response->assertSee('Capaian Indikator Terendah');
    $response->assertSee('Capaian Jabatan Tertinggi');
    $response->assertSee('Capaian Jabatan Terendah');
    $response->assertSee('Daftar Capaian IKU Jabatan');
});

it('redirects guests away from the IKU dashboard', function () {
    $response = $this->get(route('superadmin.dashboard2'));

    $response->assertRedirect(route('login'));
});
