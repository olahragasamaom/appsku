<?php

namespace Database\Factories;

use App\Models\SubIndikator;
use App\Models\SubJenisUjian;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubIndikator>
 */
class SubIndikatorFactory extends Factory
{
    protected $model = SubIndikator::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subJenis = SubJenisUjian::factory()->create();

        return [
            'sub_jenis_ujian_id' => $subJenis->id,
            'jenis_ujian_id' => $subJenis->jenis_ujian_id,
            'nama_sub_indikator' => $this->faker->randomElement([
                'Perdata',
                'Pidana',
                'Hukum Pidana Khusus',
                'Tata Negara',
            ]),
        ];
    }
}
