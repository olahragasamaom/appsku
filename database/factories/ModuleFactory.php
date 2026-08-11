<?php

namespace Database\Factories;

use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Module>
 */
class ModuleFactory extends Factory
{
    protected $model = Module::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $label = ucwords($this->faker->unique()->words(2, true));

        return [
            'key' => Str::slug($label).'-'.$this->faker->unique()->numberBetween(1, 999999),
            'label' => $label,
            'route_name' => 'superadmin.dashboard',
            'route_pattern' => 'superadmin.dashboard',
            'icon' => 'M3 12l2-2m0 0l7-7 7 7',
            'grup' => 'Manajemen',
            'urutan' => $this->faker->numberBetween(1, 100),
            'is_active' => true,
        ];
    }
}
