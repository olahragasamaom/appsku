<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserLevel;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PembatSoalUserLevelSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);

        $role = Role::where('name', 'level:pembuat_soal')->first();
        if (!$role) {
            return;
        }

        $userLevel = UserLevel::firstOrCreate(
            ['slug' => 'pembuat_soal'],
            [
                'nama' => 'Pembuat Soal',
                'role_id' => $role->id,
                'keterangan' => 'User yang mengelola ujian, soal, dan peserta',
                'is_active' => true,
            ]
        );

        $superadmin = User::where('is_superadmin', true)->first();
        if ($superadmin) {
            \DB::table('panritta_ujian')
                ->whereNull('user_updated_by')
                ->update(['user_updated_by' => $superadmin->id]);

            \DB::table('panritta_soal')
                ->whereNull('user_updated_by')
                ->update(['user_updated_by' => $superadmin->id]);
        }
    }
}
