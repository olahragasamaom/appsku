<?php

namespace Database\Factories;

use App\Models\Paket;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Paket>
 */
class PaketFactory extends Factory
{
    protected $model = Paket::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nama = $this->faker->unique()->randomElement(['Free', 'Basic', 'Pro', 'Platinum']).' '.$this->faker->numberBetween(1, 9999);

        return [
            'nama_paket' => $nama,
            'slug' => Str::slug($nama),
            'deskripsi' => $this->faker->sentence(),
            'harga' => $this->faker->randomElement([0, 50000, 150000, 300000]),
            'durasi_hari' => 30,
            'kuota_ujian' => $this->faker->randomElement([null, 5, 10, 50]),
            'video_pembahasan' => $this->faker->boolean(),
            'analitik' => $this->faker->boolean(),
            'sertifikat' => $this->faker->boolean(),
            'is_active' => true,
            'urutan' => 0,
        ];
    }

    public function gratis(): static
    {
        return $this->state(fn (array $attributes): array => ['harga' => 0]);
    }
}
