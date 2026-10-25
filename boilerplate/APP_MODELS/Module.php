<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    /** @use HasFactory<\Database\Factories\ModuleFactory> */
    use HasFactory;

    protected $table = 'panritta_modules';

    protected $fillable = [
        'key',
        'label',
        'route_name',
        'route_pattern',
        'icon',
        'grup',
        'urutan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'urutan' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The Spatie permission name for a given action on this module.
     */
    public function permissionName(string $action): string
    {
        return "{$this->key}.{$action}";
    }
}
