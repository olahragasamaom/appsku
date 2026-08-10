<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MODEL: Header Detail
 *
 * Ini adalah form transaksi utama. Mengandung data umum transaksi
 * dan otomatis meng-generate 'nomor' secara berurutan.
 */
class LatihanDetail extends Model
{
    protected $table = 'latihan_detail';

    protected $fillable = [
        'nomor',
        'nama_transaksi',
        'latihan_kategori_id',
        'tanggal',
        'total',
        'catatan',
    ];

    /**
     * Cast array - mengonversi nilai dari DB ke tipe data PHP
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'total' => 'float',
        ];
    }

    /**
     * EVENT HOOK: Booted
     *
     * Dijalankan otomatis oleh Laravel saat model dioperasikan.
     * Kita gunakan hook 'creating' untuk meng-generate nomor unik
     * sebelum data masuk ke database, sesuai konvensi project.
     */
    protected static function booted(): void
    {
        static::creating(function (LatihanDetail $detail) {
            // Jika nomor kosong, generate otomatis
            if (empty($detail->nomor)) {
                $detail->nomor = static::generateNomor();
            }
        });
    }

    /**
     * Method bantuan untuk men-generate nomor seperti DET20260001
     */
    public static function generateNomor(): string
    {
        $prefix = 'DET';
        $year = date('Y');

        // Cari transaksi terakhir di tahun ini
        $lastDetail = static::where('nomor', 'like', "{$prefix}{$year}%")
            ->orderBy('id', 'desc')
            ->first();

        // Ambil 4 digit terakhir, tambah 1
        $sequence = $lastDetail
            ? ((int) substr($lastDetail->nomor, -4)) + 1
            : 1;

        // Gabungkan: DET + 2026 + 0001
        return $prefix.$year.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * RELASI: BelongsTo ke Kategori
     * Setiap transaksi dimiliki oleh 1 kategori (nullable)
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(LatihanKategori::class, 'latihan_kategori_id');
    }

    /**
     * RELASI: HasMany ke Items (Child rows)
     * Satu transaksi punya banyak baris item.
     */
    public function items(): HasMany
    {
        return $this->hasMany(LatihanDetailItem::class, 'latihan_detail_id');
    }
}
