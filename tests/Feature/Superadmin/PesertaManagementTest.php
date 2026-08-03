<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superadmin = User::factory()->create([
        'is_superadmin' => true,
        'company_id' => null,
        'is_active' => true,
    ]);

    $this->actingAs($this->superadmin);
});

describe('Peserta Index', function () {
    it('lists only peserta users', function () {
        User::factory()->create(['name' => 'Budi Peserta', 'is_peserta' => true]);
        User::factory()->create(['name' => 'Andi Karyawan', 'is_peserta' => false]);

        $response = $this->get('/superadmin/peserta');

        $response->assertSuccessful();
        $response->assertViewIs('superadmin.peserta.index');
        $response->assertSee('Budi Peserta');
        $response->assertDontSee('Andi Karyawan');
    });
});

describe('Peserta Create', function () {
    it('can create a peserta', function () {
        $response = $this->post('/superadmin/peserta', [
            'name' => 'Siti Peserta',
            'username' => 'siti_peserta',
            'password' => 'rahasia123',
            'is_active' => 1,
        ]);

        $response->assertRedirect('/superadmin/peserta');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'username' => 'siti_peserta',
            'is_peserta' => true,
        ]);
    });

    it('generates an email when not provided', function () {
        $this->post('/superadmin/peserta', [
            'name' => 'Tanpa Email',
            'username' => 'tanpa_email',
            'password' => 'rahasia123',
        ]);

        $user = User::where('username', 'tanpa_email')->first();

        expect($user)->not->toBeNull();
        expect($user->email)->not->toBeNull();
    });

    it('validates required fields', function () {
        $response = $this->post('/superadmin/peserta', []);

        $response->assertSessionHasErrors(['name', 'username', 'password']);
    });

    it('validates unique username', function () {
        User::factory()->create(['username' => 'duplikat', 'is_peserta' => true]);

        $response = $this->post('/superadmin/peserta', [
            'name' => 'Peserta Baru',
            'username' => 'duplikat',
            'password' => 'rahasia123',
        ]);

        $response->assertSessionHasErrors('username');
    });
});

describe('Peserta Update', function () {
    it('can update a peserta without changing password', function () {
        $peserta = User::factory()->create([
            'name' => 'Nama Lama',
            'username' => 'nama_lama',
            'is_peserta' => true,
        ]);
        $originalPassword = $peserta->password;

        $response = $this->put("/superadmin/peserta/{$peserta->id}", [
            'name' => 'Nama Baru',
            'username' => 'nama_baru',
        ]);

        $response->assertRedirect('/superadmin/peserta');

        $peserta->refresh();
        expect($peserta->name)->toBe('Nama Baru');
        expect($peserta->password)->toBe($originalPassword);
    });

    it('returns 404 when editing a non peserta user', function () {
        $karyawan = User::factory()->create(['is_peserta' => false]);

        $response = $this->get("/superadmin/peserta/{$karyawan->id}/edit");

        $response->assertNotFound();
    });
});

describe('Peserta Delete', function () {
    it('can delete a peserta', function () {
        $peserta = User::factory()->create(['is_peserta' => true]);

        $response = $this->delete("/superadmin/peserta/{$peserta->id}");

        $response->assertRedirect('/superadmin/peserta');
        $this->assertSoftDeleted('users', ['id' => $peserta->id]);
    });
});
