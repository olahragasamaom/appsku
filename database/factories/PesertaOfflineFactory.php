<?php

namespace Database\Factories;

use App\Models\PesertaOffline;
use App\Models\Ujian;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<PesertaOffline>
 */
class PesertaOfflineFactory extends Factory
{
    protected $model = PesertaOffline::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ujian_id' => Ujian::factory(),
            'nomor_peserta' => strtoupper($this->faker->unique()->bothify('P-####')),
            'nama_peserta' => $this->faker->name(),
            'kode_akses' => Hash::make('rahasia'),
            'ujian_peserta_id' => null,
        ];
    }
}
