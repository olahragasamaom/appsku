<?php

namespace Database\Factories;

use App\Models\JenisUjian;
use App\Models\SubJenisUjian;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubJenisUjian>
 */
class SubJenisUjianFactory extends Factory
{
    protected $model = SubJenisUjian::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'jenis_ujian_id' => JenisUjian::factory(),
            'nama_sub_jenis_ujian' => $this->faker->unique()->randomElement([
                'Hukum Materil',
                'Psikotes',
                'TKP',
                'Wawasan Kebangsaan',
            ]),
            'keterangan' => $this->faker->optional()->sentence(),
            'urutan' => $this->faker->numberBetween(0, 10),
            'sistem_penilaian' => 'benar_salah',
            'jumlah_jawaban_pilihan_ganda' => 5,
            'nilai_benar' => 5.00,
        ];
    }

    public function poinPerJawaban(): static
    {
        return $this->state(fn (array $attributes): array => [
            'sistem_penilaian' => 'tiap_jawaban_ada_poin',
        ]);
    }
}
