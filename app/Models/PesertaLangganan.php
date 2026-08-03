<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PesertaLangganan extends Model
{
    protected $table = 'panritta_peserta_langganan';

    protected $fillable = [
        'user_id',
        'paket_id',
        'status',
        'mulai_pada',
        'berakhir_pada',
        'sisa_kuota_ujian',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class, 'paket_id');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(PesertaPembayaran::class, 'langganan_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active'
            && ($this->berakhir_pada === null || $this->berakhir_pada->isFuture());
    }
}
