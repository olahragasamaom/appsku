<?php

namespace Tests;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    /**
     * Create an admin user with all module permissions for a company.
     * Useful for integration tests that need admin access without permission restrictions.
     *
     * @param  Company|null  $company  The company to assign the admin to (created if null)
     * @return User The created admin user
     */
    protected function createAdminUserWithAllPermissions(?Company $company = null): User
    {
        if ($company === null) {
            $company = Company::factory()->create();
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);

        $role = Role::firstOrCreate(['name' => 'admin']);

        $admin = User::factory()->create([
            'company_id' => $company->id,
        ]);
        $admin->assignRole('admin');

        grantAllModulePermissionsToRole($role);

        return $admin;
    }

    /**
     * Create a company and set up Spatie Permission team context.
     * Helper to ensure permission checks use the correct team_id.
     */
    protected function createCompanyWithPermissionContext(): Company
    {
        $company = Company::factory()->create();
        app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);

        return $company;
    }
}
