<?php

use App\Models\Module;
use App\Models\User;
use App\Models\UserLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

/**
 * Create a module together with its Spatie view/edit/delete permissions.
 */
function makeModuleWithPermissions(string $key): Module
{
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);

    $module = Module::factory()->create(['key' => $key]);

    foreach (['view', 'edit', 'delete'] as $action) {
        Permission::firstOrCreate(['name' => "{$key}.{$action}", 'guard_name' => 'web']);
    }

    return $module;
}

describe('UserLevel role backing', function () {
    it('creates a backing spatie role when syncing permissions', function () {
        makeModuleWithPermissions('ujian');
        $level = UserLevel::factory()->create();

        $level->syncModulePermissions(['ujian' => ['view', 'edit']]);

        expect($level->fresh()->role)->not->toBeNull();
        expect($level->role->name)->toBe('level:'.$level->slug);
    });

    it('grants only the selected actions on a module', function () {
        makeModuleWithPermissions('soal');
        $level = UserLevel::factory()->create();

        $level->syncModulePermissions(['soal' => ['view']]);

        expect($level->allows('soal', 'view'))->toBeTrue();
        expect($level->allows('soal', 'edit'))->toBeFalse();
        expect($level->allows('soal', 'delete'))->toBeFalse();
    });

    it('exposes a permitted actions map derived from the role', function () {
        makeModuleWithPermissions('peserta');
        $level = UserLevel::factory()->create();

        $level->syncModulePermissions(['peserta' => ['view', 'delete']]);

        $map = $level->permittedActions();

        expect($map)->toHaveKey('peserta');
        expect($map['peserta'])->toContain('view');
        expect($map['peserta'])->toContain('delete');
        expect($map['peserta'])->not->toContain('edit');
    });

    it('re-syncing replaces previous permissions', function () {
        makeModuleWithPermissions('ujian');
        makeModuleWithPermissions('soal');
        $level = UserLevel::factory()->create();

        $level->syncModulePermissions(['ujian' => ['view', 'edit']]);
        $level->syncModulePermissions(['soal' => ['view']]);

        expect($level->allows('ujian', 'view'))->toBeFalse();
        expect($level->allows('soal', 'view'))->toBeTrue();
    });

    it('deletes the backing role when the level is deleted', function () {
        makeModuleWithPermissions('ujian');
        $level = UserLevel::factory()->create();
        $level->syncModulePermissions(['ujian' => ['view']]);
        $roleId = $level->role->id;

        $level->delete();

        $this->assertDatabaseMissing('roles', ['id' => $roleId]);
    });
});

describe('User module access', function () {
    it('grants superadmin every action regardless of level', function () {
        $superadmin = User::factory()->create([
            'is_superadmin' => true,
            'company_id' => null,
        ]);

        expect($superadmin->canAccessModule('anything'))->toBeTrue();
        expect($superadmin->canDoOnModule('anything', 'delete'))->toBeTrue();
    });

    it('limits a leveled user to the granted actions', function () {
        makeModuleWithPermissions('peserta');
        makeModuleWithPermissions('companies');
        $level = UserLevel::factory()->create();
        $level->syncModulePermissions(['peserta' => ['view', 'edit']]);

        $user = User::factory()->create([
            'is_superadmin' => false,
            'user_level_id' => $level->id,
        ]);

        expect($user->canAccessModule('peserta'))->toBeTrue();
        expect($user->canDoOnModule('peserta', 'edit'))->toBeTrue();
        expect($user->canDoOnModule('peserta', 'delete'))->toBeFalse();
        expect($user->canAccessModule('companies'))->toBeFalse();
    });

    it('denies module access to a user without a level', function () {
        $user = User::factory()->create([
            'is_superadmin' => false,
            'user_level_id' => null,
        ]);

        expect($user->canAccessModule('peserta'))->toBeFalse();
    });
});
