<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserLevel;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class KeuanganUserSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);

        $role = Role::where('name', 'level:keuangan')->first();
        if (!$role) {
            $this->command->error('Role level:keuangan tidak ditemukan');
            return;
        }

        $userLevel = UserLevel::firstOrCreate(
            ['slug' => 'keuangan'],
            [
                'nama' => 'Keuangan',
                'role_id' => $role->id,
                'keterangan' => 'User yang mengelola peserta, paket, dan verifikasi pembayaran',
                'is_active' => true,
            ]
        );

        $existing = User::where('username', 'keuangan')->first();
        if ($existing) {
            $existing->update(['password' => bcrypt('password123')]);
            $this->command->info('User keuangan password diupdate: password123');
            return;
        }

        User::create([
            'name' => 'Keuangan Demo',
            'username' => 'keuangan',
            'email' => 'keuangan@demo.local',
            'password' => bcrypt('password123'),
            'user_level_id' => $userLevel->id,
            'is_active' => true,
            'company_id' => null,
        ]);

        $this->command->info('User keuangan berhasil dibuat');
    }
}
