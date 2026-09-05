<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesertaOfflineKehadiran extends Model
{
    /** @use HasFactory<\Database\Factories\PesertaOfflineKehadiranFactory> */
    use HasFactory;

    protected $table = 'panritta_peserta_offline_kehadiran';

    protected $fillable = [
        'peserta_offline_id',
        'ujian_id',
        'status_kehadiran',
    ];

    protected function casts(): array
    {
        return [
            'status_kehadiran' => 'string',
        ];
    }

    public function pesertaOffline(): BelongsTo
    {
        return $this->belongsTo(PesertaOffline::class, 'peserta_offline_id');
    }

    public function ujian(): BelongsTo
    {
        return $this->belongsTo(Ujian::class, 'ujian_id');
    }
}
