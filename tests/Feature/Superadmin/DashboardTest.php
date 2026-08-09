<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the superadmin dashboard with correct CAT statistics', function () {
    $superadmin = User::factory()->create([
        'is_superadmin' => true,
        'company_id' => null,
    ]);

    $response = $this->actingAs($superadmin)->get(route('superadmin.dashboard'));

    $response->assertSuccessful();
    $response->assertViewIs('superadmin.dashboard');
    $response->assertSee('Bank Soal');
    $response->assertSee('Ujian Aktif');
    $response->assertSee('Jadwal Ujian Terdekat');
    $response->assertSee('Hall of Fame');
});
