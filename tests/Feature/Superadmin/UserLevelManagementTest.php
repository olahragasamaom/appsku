<?php

use App\Models\Module;
use App\Models\User;
use App\Models\UserLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superadmin = User::factory()->create([
        'is_superadmin' => true,
        'company_id' => null,
        'is_active' => true,
    ]);

    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

/**
 * Create a module together with its Spatie view/edit/delete permissions.
 */
function seedModule(string $key, string $label = 'Modul', string $grup = 'Manajemen'): Module
{
    $module = Module::factory()->create(['key' => $key, 'label' => $label, 'grup' => $grup]);

    foreach (['view', 'edit', 'delete'] as $action) {
        Permission::firstOrCreate(['name' => "{$key}.{$action}", 'guard_name' => 'web']);
    }

    return $module;
}

describe('User Level Index', function () {
    it('displays the user level management page', function () {
        $this->actingAs($this->superadmin);

        UserLevel::factory()->count(2)->create();

        $response = $this->get('/superadmin/user-levels');

        $response->assertSuccessful();
        $response->assertViewIs('superadmin.user-levels.index');
        $response->assertSee('Manajemen Modul');
    });

    it('lists modules with view/edit/delete columns', function () {
        $this->actingAs($this->superadmin);

        seedModule('soal', 'Bank Soal');

        $response = $this->get('/superadmin/user-levels');

        $response->assertSuccessful();
        $response->assertSee('Bank Soal');
        $response->assertSee('Lihat');
        $response->assertSee('Ubah');
        $response->assertSee('Hapus');
    });
});

describe('User Level Create', function () {
    it('creates a level and grants the selected module permissions', function () {
        $this->actingAs($this->superadmin);

        seedModule('ujian', 'Manajemen Ujian');
        seedModule('soal', 'Bank Soal');

        $response = $this->post('/superadmin/user-levels', [
            'nama' => 'Operator',
            'keterangan' => 'Level operator',
            'is_active' => true,
            'permissions' => ['ujian.view', 'ujian.edit', 'soal.view'],
        ]);

        $response->assertRedirect('/superadmin/user-levels');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('panritta_user_levels', [
            'nama' => 'Operator',
            'slug' => 'operator',
        ]);

        $level = UserLevel::where('nama', 'Operator')->first();
        expect($level->allows('ujian', 'view'))->toBeTrue();
        expect($level->allows('ujian', 'edit'))->toBeTrue();
        expect($level->allows('ujian', 'delete'))->toBeFalse();
        expect($level->allows('soal', 'view'))->toBeTrue();
        expect($level->allows('soal', 'edit'))->toBeFalse();
    });

    it('can create a level without any permissions', function () {
        $this->actingAs($this->superadmin);

        $response = $this->post('/superadmin/user-levels', [
            'nama' => 'Kosong',
        ]);

        $response->assertRedirect('/superadmin/user-levels');

        $level = UserLevel::where('nama', 'Kosong')->first();
        expect($level->permittedActions())->toBe([]);
    });

    it('validates required nama on create', function () {
        $this->actingAs($this->superadmin);

        $response = $this->post('/superadmin/user-levels', []);

        $response->assertSessionHasErrors('nama');
    });

    it('validates unique nama level', function () {
        $this->actingAs($this->superadmin);

        UserLevel::factory()->create(['nama' => 'Admin Ujian']);

        $response = $this->post('/superadmin/user-levels', [
            'nama' => 'Admin Ujian',
        ]);

        $response->assertSessionHasErrors('nama');
    });

    it('rejects permission names that do not exist', function () {
        $this->actingAs($this->superadmin);

        $response = $this->post('/superadmin/user-levels', [
            'nama' => 'Level Invalid',
            'permissions' => ['tidak-ada.view'],
        ]);

        $response->assertSessionHasErrors('permissions.0');
    });
});

describe('User Level Update', function () {
    it('updates a level and re-syncs its permissions', function () {
        $this->actingAs($this->superadmin);

        seedModule('ujian', 'Manajemen Ujian');
        seedModule('soal', 'Bank Soal');

        $level = UserLevel::factory()->create(['nama' => 'Lama']);
        $level->syncModulePermissions(['ujian' => ['view', 'edit']]);

        $response = $this->put("/superadmin/user-levels/{$level->id}", [
            'nama' => 'Baru',
            'permissions' => ['soal.view'],
        ]);

        $response->assertRedirect('/superadmin/user-levels');
        $response->assertSessionHas('success');

        $level->refresh();
        expect($level->nama)->toBe('Baru');
        expect($level->allows('ujian', 'view'))->toBeFalse();
        expect($level->allows('soal', 'view'))->toBeTrue();
    });

    it('clears permissions when none are submitted on update', function () {
        $this->actingAs($this->superadmin);

        seedModule('ujian', 'Manajemen Ujian');

        $level = UserLevel::factory()->create();
        $level->syncModulePermissions(['ujian' => ['view']]);

        $this->put("/superadmin/user-levels/{$level->id}", [
            'nama' => $level->nama,
        ]);

        expect($level->fresh()->permittedActions())->toBe([]);
    });
});

describe('User Level Delete', function () {
    it('can delete a level and its backing role', function () {
        $this->actingAs($this->superadmin);

        seedModule('ujian', 'Manajemen Ujian');

        $level = UserLevel::factory()->create();
        $level->syncModulePermissions(['ujian' => ['view']]);
        $roleId = $level->role->id;

        $response = $this->delete("/superadmin/user-levels/{$level->id}");

        $response->assertRedirect('/superadmin/user-levels');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('panritta_user_levels', ['id' => $level->id]);
        $this->assertDatabaseMissing('roles', ['id' => $roleId]);
    });
});
