<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MODEL: Produk
 *
 * Tabel master yang akan dipilih menggunakan Modal Picker (karena
 * jumlah datanya diasumsikan banyak, sehingga tidak cocok dropdown).
 */
class LatihanProduk extends Model
{
    use HasFactory;

    protected $table = 'latihan_produk';

    protected $fillable = [
        'kode_produk',
        'nama',
        'harga',
    ];

    /**
     * Konversi tipe data otomatis (casting).
     * Kolom harga akan selalu menjadi float saat ditarik dari DB.
     */
    protected function casts(): array
    {
        return [
            'harga' => 'float',
        ];
    }

    /**
     * Relasi HasMany ke item-item transaksi.
     */
    public function detailItems(): HasMany
    {
        return $this->hasMany(LatihanDetailItem::class);
    }
}
