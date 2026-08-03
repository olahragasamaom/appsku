<?php

namespace Database\Factories;

use App\Models\Ujian;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ujian>
 */
class UjianFactory extends Factory
{
    protected $model = Ujian::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_ujian' => 'Tryout '.$this->faker->unique()->numberBetween(1, 9999),
            'tipe_ujian' => 'offline_kelas',
            'jumlah_soal' => $this->faker->numberBetween(10, 100),
            'acak_soal' => $this->faker->boolean(),
            'tampilkan_hasil' => true,
            'tanggal_ujian' => now()->addDays(3),
            'durasi_ujian' => 90,
            'batas_keterlambatan' => now()->addDays(3)->addMinutes(15),
            'token_ujian' => strtoupper($this->faker->lexify('??????')),
            'akses_member' => null,
            'status' => 'draft',
            'dibuat_oleh' => User::factory(),
        ];
    }

    public function online(): static
    {
        return $this->state(fn (array $attributes): array => [
            'tipe_ujian' => 'online_paket',
            'tanggal_ujian' => null,
            'durasi_ujian' => null,
            'batas_keterlambatan' => null,
            'token_ujian' => null,
            'akses_member' => ['Free', 'Basic'],
        ]);
    }
}
