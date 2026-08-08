<?php

namespace Database\Factories;

use App\Models\JenisUjian;
use App\Models\UjianPesertaKategori;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UjianPesertaKategori>
 */
class UjianPesertaKategoriFactory extends Factory
{
    protected $model = UjianPesertaKategori::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'jenis_ujian_id' => JenisUjian::factory(),
            'nilai_kategori' => $this->faker->randomFloat(2, 0, 100),
            'passing_grade' => $this->faker->randomFloat(2, 40, 80),
            'lulus_kategori' => $this->faker->boolean(),
        ];
    }
}
