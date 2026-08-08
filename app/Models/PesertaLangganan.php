<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MODEL: PesertaLangganan
 * ========================
 * Mewakili tabel 'panritta_peserta_langganan' di database.
 * Merupakan catatan bahwa seorang user aktif berlangganan pada sebuah paket tertentu.
 */
class PesertaLangganan extends Model
{
    protected $table = 'panritta_peserta_langganan';

    protected $fillable = [
        'user_id',
        'paket_id',
        'status',             // 'pending', 'active', 'expired', 'cancelled'
        'mulai_pada',
        'berakhir_pada',
        'sisa_kuota_ujian',   // null = unlimited
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'user_id' => 'integer',
            'paket_id' => 'integer',
            'mulai_pada' => 'datetime',
            'berakhir_pada' => 'datetime',
            'sisa_kuota_ujian' => 'integer',
        ];
    }

    /**
     * RELASI: Peserta (User) yang memiliki langganan ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * RELASI: Paket yang dilanggan.
     */
    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class, 'paket_id');
    }

    /**
     * RELASI (One-to-Many): Daftar riwayat tagihan/pembayaran untuk langganan ini.
     */
    public function pembayaran(): HasMany
    {
        return $this->hasMany(PesertaPembayaran::class, 'langganan_id');
    }

    /**
     * Cek apakah status langganan saat ini masih aktif dan belum melewati batas waktu.
     */
    public function isActive(): bool
    {
        return $this->status === 'active'
            && ($this->berakhir_pada === null || $this->berakhir_pada->isFuture());
    }
}
