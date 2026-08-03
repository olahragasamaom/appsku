<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesertaPembayaran extends Model
{
    protected $table = 'panritta_peserta_pembayaran';

    public const STATUS_PENDING = 'pending';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'user_id',
        'paket_id',
        'langganan_id',
        'nomor_pembayaran',
        'gateway',
        'gateway_reference',
        'invoice_url',
        'jumlah',
        'status',
        'metode_pembayaran',
        'gateway_response',
        'dibayar_pada',
        'kedaluwarsa_pada',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'user_id' => 'integer',
            'paket_id' => 'integer',
            'langganan_id' => 'integer',
            'jumlah' => 'decimal:2',
            'gateway_response' => 'array',
            'dibayar_pada' => 'datetime',
            'kedaluwarsa_pada' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PesertaPembayaran $pembayaran): void {
            if (empty($pembayaran->nomor_pembayaran)) {
                $pembayaran->nomor_pembayaran = self::generateNomor();
            }
        });
    }

    public static function generateNomor(): string
    {
        $date = now()->format('Ymd');
        $last = self::whereDate('created_at', today())->orderByDesc('id')->first();
        $sequence = $last ? ((int) substr($last->nomor_pembayaran, -4)) + 1 : 1;

        return 'UJN'.$date.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class, 'paket_id');
    }

    public function langganan(): BelongsTo
    {
        return $this->belongsTo(PesertaLangganan::class, 'langganan_id');
    }

    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }
}
