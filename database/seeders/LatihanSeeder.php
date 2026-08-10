<?php

namespace Database\Seeders;

use App\Models\LatihanKategori;
use App\Models\LatihanProduk;
use Illuminate\Database\Seeder;

class LatihanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi data master untuk form Latihan Detail.
     */
    public function run(): void
    {
        // 1. Buat Kategori (untuk dropdown)
        $kategoris = [
            'Elektronik',
            'Pakaian',
            'Buku',
            'Alat Tulis',
            'Peralatan Rumah',
        ];

        foreach ($kategoris as $kategori) {
            LatihanKategori::firstOrCreate(['nama' => $kategori]);
        }

        // 2. Buat Produk (untuk modal picker)
        // Kita gunakan factory untuk membuat 50 produk secara acak
        if (LatihanProduk::count() === 0) {
            LatihanProduk::factory()->count(50)->create();
        }
    }
}
