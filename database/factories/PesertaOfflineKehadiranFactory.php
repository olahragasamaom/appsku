<?php

namespace Database\Factories;

use App\Models\PesertaOffline;
use App\Models\PesertaOfflineKehadiran;
use App\Models\Ujian;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PesertaOfflineKehadiran>
 */
class PesertaOfflineKehadiranFactory extends Factory
{
    protected $model = PesertaOfflineKehadiran::class;

    public function definition(): array
    {
        return [
            'peserta_offline_id' => PesertaOffline::factory(),
            'ujian_id' => Ujian::factory()->offline(),
            'status_kehadiran' => 'tidak_hadir',
        ];
    }

    public function hadir(): self
    {
        return $this->state([
            'status_kehadiran' => 'hadir',
        ]);
    }
}
