<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserLevel;
use Illuminate\Database\Seeder;

class CreatePembuatSoalUserSeeder extends Seeder
{
    public function run(): void
    {
        $userLevel = UserLevel::where('slug', 'pembuat_soal')->first();
        if (!$userLevel) {
            $this->command->error('UserLevel pembuat_soal tidak ditemukan');
            return;
        }

        $existing = User::where('username', 'pembuat')->first();
        if ($existing) {
            $existing->update(['password' => bcrypt('password123')]);
            $this->command->info('User pembuat password diupdate: password123');
            return;
        }

        User::create([
            'name' => 'Pembuat Soal Demo',
            'username' => 'pembuat',
            'email' => 'pembuat@demo.local',
            'password' => bcrypt('password123'),
            'user_level_id' => $userLevel->id,
            'is_active' => true,
            'company_id' => null,
        ]);

        $this->command->info('User pembuat berhasil dibuat');
    }
}
