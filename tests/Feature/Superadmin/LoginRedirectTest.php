<?php

use App\Models\User;

describe('Superadmin Login Access', function () {
    it('shows login page when accessing /superadmin/login', function () {
        $response = $this->get('/superadmin/login');

        $response->assertStatus(200);
        $response->assertViewIs('superadmin.auth.login');
    });

    it('shows dashboard when accessing /superadmin as authenticated superadmin', function () {
        $superadmin = User::factory()->create([
            'is_superadmin' => true,
        ]);

        $response = $this->actingAs($superadmin, 'web')
            ->get('/superadmin');

        $response->assertStatus(200);
        $response->assertViewIs('superadmin.dashboard');
    });

    it('can login directly from superadmin login page', function () {
        $superadmin = User::factory()->create([
            'is_superadmin' => true,
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/superadmin/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/superadmin');
        $this->assertAuthenticatedAs($superadmin);
    });
});
