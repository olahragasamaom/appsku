<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateDefaultUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Superadmin',
                'email' => 'superadmin@panritta.com',
                'password' => Hash::make('password123'),
                'is_active' => true,
                'is_superadmin' => true,
                'is_peserta' => false,
                'is_pengajar' => false,
            ]
        );

        User::firstOrCreate(
            ['username' => 'user_pengajar'],
            [
                'name' => 'Pengajar',
                'email' => 'pengajar@panritta.com',
                'password' => Hash::make('password123'),
                'is_active' => true,
                'is_superadmin' => false,
                'is_peserta' => false,
                'is_pengajar' => true,
            ]
        );
    }
}
