<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

describe('Change Password', function () {
    beforeEach(function () {
        $this->superadmin = User::factory()->create([
            'is_superadmin' => true,
            'password' => Hash::make('oldpassword123'),
        ]);
    });

    it('shows change password form when authenticated', function () {
        $response = $this->actingAs($this->superadmin, 'web')
            ->get('/superadmin/change-password');

        $response->assertStatus(200);
        $response->assertViewIs('superadmin.change-password.index');
    });

    it('requires authentication to access change password page', function () {
        $response = $this->get('/superadmin/change-password');

        $response->assertRedirect('/login');
    });

    it('fails when current password is incorrect', function () {
        $response = $this->actingAs($this->superadmin, 'web')
            ->put('/superadmin/change-password', [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertSessionHasErrors('current_password');
    });

    it('fails when new password is less than 8 characters', function () {
        $response = $this->actingAs($this->superadmin, 'web')
            ->put('/superadmin/change-password', [
                'current_password' => 'oldpassword123',
                'password' => 'short',
                'password_confirmation' => 'short',
            ]);

        $response->assertSessionHasErrors('password');
    });

    it('fails when password confirmation does not match', function () {
        $response = $this->actingAs($this->superadmin, 'web')
            ->put('/superadmin/change-password', [
                'current_password' => 'oldpassword123',
                'password' => 'newpassword123',
                'password_confirmation' => 'differentpassword123',
            ]);

        $response->assertSessionHasErrors('password');
    });

    it('successfully changes password with valid data', function () {
        $response = $this->actingAs($this->superadmin, 'web')
            ->put('/superadmin/change-password', [
                'current_password' => 'oldpassword123',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect('/superadmin/change-password');
        $response->assertSessionHas('success', 'Password berhasil diubah.');

        $this->superadmin->refresh();
        $this->assertTrue(Hash::check('newpassword123', $this->superadmin->password));
        $this->assertFalse(Hash::check('oldpassword123', $this->superadmin->password));
    });

    it('allows user to login with new password after change', function () {
        $this->actingAs($this->superadmin, 'web')
            ->put('/superadmin/change-password', [
                'current_password' => 'oldpassword123',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $this->post('/superadmin/login', [
            'email' => $this->superadmin->email,
            'password' => 'newpassword123',
        ])->assertStatus(302);
    });
});
