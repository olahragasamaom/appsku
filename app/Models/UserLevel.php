<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserLevel extends Model
{
    /** @use HasFactory<\Database\Factories\UserLevelFactory> */
    use HasFactory;

    protected $table = 'panritta_user_levels';

    protected $fillable = [
        'nama',
        'slug',
        'role_id',
        'keterangan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'role_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'user_level_id');
    }

    /**
     * Ensure this level is backed by a dedicated Spatie role (null team = superadmin context).
     */
    public function ensureRole(): Role
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);

        if ($this->role) {
            return $this->role;
        }

        $role = Role::firstOrCreate([
            'name' => 'level:'.$this->slug,
            'guard_name' => 'web',
        ]);

        $this->role()->associate($role);
        $this->saveQuietly();

        return $role;
    }

    /**
     * Sync the module permissions granted to this level.
     *
     * @param  array<string, list<string>>  $moduleActions  e.g. ['ujian' => ['view','edit'], ...]
     */
    public function syncModulePermissions(array $moduleActions): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);

        $role = $this->ensureRole();

        $permissionNames = [];
        foreach ($moduleActions as $moduleKey => $actions) {
            foreach ($actions as $action) {
                $permissionNames[] = "{$moduleKey}.{$action}";
            }
        }

        $permissions = Permission::whereIn('name', $permissionNames)
            ->where('guard_name', 'web')
            ->get();

        $role->syncPermissions($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Map of module key => granted actions, derived from the role's permissions.
     *
     * @return array<string, list<string>>
     */
    public function permittedActions(): array
    {
        if (! $this->role) {
            return [];
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId(null);

        $map = [];
        foreach ($this->role->permissions as $permission) {
            if (! str_contains($permission->name, '.')) {
                continue;
            }

            [$moduleKey, $action] = explode('.', $permission->name, 2);
            $map[$moduleKey][] = $action;
        }

        return $map;
    }

    /**
     * Whether this level grants a specific action on a module.
     */
    public function allows(string $moduleKey, string $action = 'view'): bool
    {
        if (! $this->role) {
            return false;
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId(null);

        return $this->role->hasPermissionTo("{$moduleKey}.{$action}");
    }

    protected static function booted(): void
    {
        static::deleting(function (UserLevel $userLevel) {
            $userLevel->role?->delete();
        });
    }
}
