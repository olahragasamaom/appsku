<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesertaOffline extends Model
{
    /** @use HasFactory<\Database\Factories\PesertaOfflineFactory> */
    use HasFactory;

    protected $table = 'panritta_peserta_offline';

    protected $fillable = [
        'ujian_id',
        'nomor_peserta',
        'nama_peserta',
        'kode_akses',
        'kode_akses_plain',
        'ujian_peserta_id',
        'is_active',
        'is_blocked',
        'blocked_at',
        'blocked_reason',
        'kode_akses_reset_at',
        'kode_akses_reset_by',
    ];

    protected $hidden = [
        'kode_akses',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'ujian_id' => 'integer',
            'ujian_peserta_id' => 'integer',
            'is_active' => 'boolean',
            'is_blocked' => 'boolean',
            'blocked_at' => 'datetime',
            'kode_akses_reset_at' => 'datetime',
            'kode_akses_reset_by' => 'integer',
        ];
    }

    public function ujian(): BelongsTo
    {
        return $this->belongsTo(Ujian::class, 'ujian_id');
    }

    public function ujianPeserta(): BelongsTo
    {
        return $this->belongsTo(UjianPeserta::class, 'ujian_peserta_id');
    }
}
