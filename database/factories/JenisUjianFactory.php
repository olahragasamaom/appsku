<?php

namespace Database\Factories;

use App\Models\JenisUjian;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JenisUjian>
 */
class JenisUjianFactory extends Factory
{
    protected $model = JenisUjian::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_jenis_ujian' => $this->faker->unique()->randomElement([
                'Ujian Harian',
                'Ujian Tengah Semester',
                'Ujian Akhir Semester',
                'Ujian Praktik',
            ]),
            'keterangan' => $this->faker->optional()->sentence(),
        ];
    }
}
