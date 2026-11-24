<?php

namespace Database\Seeders;

use App\Models\UserLevel;
use Illuminate\Database\Seeder;

class PengawasUserLevelSeeder extends Seeder
{
    public function run(): void
    {
        $level = UserLevel::firstOrCreate(['slug' => 'pengawas'], [
            'nama' => 'Pengawas Ujian',
            'is_active' => true,
        ]);

        $level->syncModulePermissions([
            'absensi' => ['view'],
            'hasil-ujian' => ['view'],
        ]);
    }
}
