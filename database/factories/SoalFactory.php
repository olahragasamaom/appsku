<?php

namespace Database\Factories;

use App\Models\Soal;
use App\Models\SubIndikator;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Soal>
 */
class SoalFactory extends Factory
{
    protected $model = Soal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sub_indikator_id' => SubIndikator::factory(),
            'soal' => $this->faker->sentence().'?',
            'opsi_a' => $this->faker->word(),
            'opsi_b' => $this->faker->word(),
            'opsi_c' => $this->faker->word(),
            'opsi_d' => $this->faker->word(),
            'opsi_e' => $this->faker->word(),
            'kunci_jawaban' => $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']),
            'nilai_bobot_benar' => null,
            'pembahasan' => $this->faker->sentence(),
            'pembuat_soal_id' => User::factory(),
        ];
    }

    public function poinPerJawaban(): static
    {
        return $this->state(fn (array $attributes): array => [
            'kunci_jawaban' => null,
            'nilai_bobot_a' => 1,
            'nilai_bobot_b' => 2,
            'nilai_bobot_c' => 3,
            'nilai_bobot_d' => 4,
            'nilai_bobot_e' => 5,
        ]);
    }
}
