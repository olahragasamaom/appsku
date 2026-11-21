<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);

        $permissions = [
            'peserta.view', 'peserta.create', 'peserta.edit', 'peserta.delete',
            'paket.view', 'paket.create', 'paket.edit', 'paket.delete',
            'pembayaran.view', 'pembayaran.verify',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(
            ['name' => 'level:keuangan', 'guard_name' => 'web'],
        );
        $role->syncPermissions($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);
        Role::where('name', 'level:keuangan')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
