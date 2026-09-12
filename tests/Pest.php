<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Create roles for a specific company with proper team context.
 *
 * @param  int|null  $companyId  The company ID to scope roles to
 * @param  array  $roleNames  Array of role names to create
 */
function createCompanyRoles(?int $companyId, array $roleNames = ['admin', 'hr-manager', 'employee']): void
{
    setPermissionsTeamId($companyId);

    foreach ($roleNames as $roleName) {
        \Spatie\Permission\Models\Role::firstOrCreate([
            'name' => $roleName,
            'guard_name' => 'web',
        ]);
    }
}

/**
 * Create standard company roles (admin, hr-manager, employee) for a company.
 */
function createStandardRoles(int $companyId): void
{
    createCompanyRoles($companyId, ['admin', 'hr-manager', 'employee']);
}

/**
 * Grant all permissions for a list of modules to a role.
 *
 * @param  \Spatie\Permission\Models\Role  $role  The role to grant permissions to
 * @param  array  $moduleKeys  Array of module keys (e.g., ['thr', 'thr-settings'])
 */
function grantModulePermissionsToRole(\Spatie\Permission\Models\Role $role, array $moduleKeys = []): void
{
    $actions = ['view', 'edit', 'delete'];

    foreach ($moduleKeys as $moduleKey) {
        foreach ($actions as $action) {
            $permission = \Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => "{$moduleKey}.{$action}",
                'guard_name' => 'web',
            ]);

            $role->givePermissionTo($permission);
        }
    }
}

/**
 * Grant ALL module permissions to a role (convenience helper for admin/hr/payroll roles).
 * Useful for integration tests that don't care about permission restrictions.
 *
 * @param  \Spatie\Permission\Models\Role  $role  The role to grant all permissions to
 */
function grantAllModulePermissionsToRole(\Spatie\Permission\Models\Role $role): void
{
    $actions = ['view', 'edit', 'delete'];

    foreach (\App\Models\Module::all() as $module) {
        foreach ($actions as $action) {
            $permission = \Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => "{$module->key}.{$action}",
                'guard_name' => 'web',
            ]);

            $role->givePermissionTo($permission);
        }
    }
}

/**
 * Create an offline participant session for testing offline exam flows.
 * Sets up both OfflineParticipantSession record and session keys required by OfflineParticipantAuth middleware.
 *
 * @param  \App\Models\Ujian  $ujian  The offline exam
 * @param  string|null  $nomor_peserta  The participant number (optional, for custom setup)
 * @return array ['peserta' => PesertaOffline, 'attempt' => UjianPeserta, 'sessionToken' => string]
 */
function createOfflineParticipantSessionForTest(\App\Models\Ujian $ujian, ?string $nomor_peserta = null): array
{
    $peserta = \App\Models\PesertaOffline::factory()->create([
        'ujian_id' => $ujian->id,
        'nomor_peserta' => $nomor_peserta ?? fake()->unique()->numerify('###'),
    ]);

    $kehadiran = \App\Models\PesertaOfflineKehadiran::factory()->create([
        'peserta_offline_id' => $peserta->id,
        'ujian_id' => $ujian->id,
        'status_kehadiran' => 'hadir',
    ]);

    $attempt = $ujian->peserta()->create([
        'user_id' => null,
        'peserta_offline_id' => $peserta->id,
        'status' => 'sedang_ujian',
        'waktu_mulai' => now(),
        'batas_waktu' => now()->addHours(2),
    ]);

    $sessionToken = \Illuminate\Support\Str::random(40);
    $session = \App\Models\OfflineParticipantSession::create([
        'peserta_offline_id' => $peserta->id,
        'ujian_id' => $ujian->id,
        'session_token' => $sessionToken,
        'status' => 'sedang_ujian',
        'login_at' => now(),
        'last_activity_at' => now(),
    ]);

    return [
        'peserta' => $peserta,
        'attempt' => $attempt,
        'sessionToken' => $sessionToken,
        'kehadiran' => $kehadiran,
    ];
}
