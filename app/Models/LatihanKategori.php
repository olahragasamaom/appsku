<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MODEL: Kategori
 *
 * Tabel lookup untuk dropdown. Satu kategori bisa dipakai
 * di banyak transaksi (LatihanDetail).
 */
class LatihanKategori extends Model
{
    protected $table = 'latihan_kategori';

    protected $fillable = [
        'nama',
    ];

    /**
     * Relasi HasMany ke transaksi detail.
     * Artinya: "Satu kategori ini memiliki banyak transaksi detail."
     */
    public function details(): HasMany
    {
        return $this->hasMany(LatihanDetail::class);
    }
}
