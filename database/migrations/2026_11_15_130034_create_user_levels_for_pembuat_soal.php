<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);

        $permissions = [
            'ujian.view',
            'ujian.create',
            'ujian.edit',
            'ujian.delete',
            'soal.view',
            'soal.create',
            'soal.edit',
            'soal.delete',
            'peserta.view',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(
            ['name' => 'level:pembuat_soal', 'guard_name' => 'web'],
        );

        $role->syncPermissions($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);
        Role::where('name', 'level:pembuat_soal')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
