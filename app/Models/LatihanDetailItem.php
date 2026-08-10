<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MODEL: Detail Item (Child baris dari Header)
 *
 * Ini menyimpan line items dari transaksi. Setiap item merujuk ke Header
 * dan merujuk ke satu Produk.
 */
class LatihanDetailItem extends Model
{
    protected $table = 'latihan_detail_items';

    protected $fillable = [
        'latihan_detail_id',
        'latihan_produk_id',
        'qty',
        'harga',
        'subtotal',
    ];

    /**
     * Cast array
     */
    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'harga' => 'float',
            'subtotal' => 'float',
        ];
    }

    /**
     * Booted hook untuk menghitung ulang subtotal otomatis sebelum
     * baris item di-save (creating atau updating) untuk memastikan
     * datanya konsisten.
     */
    protected static function booted(): void
    {
        $calculateSubtotal = function (LatihanDetailItem $item) {
            $item->subtotal = $item->qty * $item->harga;
        };

        static::creating($calculateSubtotal);
        static::updating($calculateSubtotal);
    }

    /**
     * RELASI: BelongsTo ke Header Transaksi
     */
    public function header(): BelongsTo
    {
        return $this->belongsTo(LatihanDetail::class, 'latihan_detail_id');
    }

    /**
     * RELASI: BelongsTo ke Produk
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(LatihanProduk::class, 'latihan_produk_id');
    }
}
