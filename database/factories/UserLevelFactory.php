<?php

namespace Database\Factories;

use App\Models\UserLevel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserLevel>
 */
class UserLevelFactory extends Factory
{
    protected $model = UserLevel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nama = ucfirst($this->faker->unique()->word());

        return [
            'nama' => $nama,
            'slug' => Str::slug($nama).'-'.$this->faker->unique()->numberBetween(1, 999999),
            'keterangan' => $this->faker->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
