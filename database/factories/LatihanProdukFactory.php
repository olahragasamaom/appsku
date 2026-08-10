<?php

namespace Database\Factories;

use App\Models\LatihanProduk;
use Illuminate\Database\Eloquent\Factories\Factory;

class LatihanProdukFactory extends Factory
{
    protected $model = LatihanProduk::class;

    public function definition(): array
    {
        return [
            'kode_produk' => 'PRD-'.$this->faker->unique()->numerify('####'),
            'nama' => $this->faker->words(3, true),
            'harga' => $this->faker->randomFloat(2, 10000, 500000),
        ];
    }
}
