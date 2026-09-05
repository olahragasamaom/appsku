<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeExtension extends Model
{
    protected $table = 'panritta_time_extensions';

    protected $fillable = [
        'ujian_peserta_id',
        'peserta_offline_id',
        'added_minutes',
        'reason',
        'granted_by',
        'granted_at',
    ];

    protected function casts(): array
    {
        return [
            'added_minutes' => 'integer',
            'granted_at' => 'datetime',
        ];
    }

    public function ujianPeserta(): BelongsTo
    {
        return $this->belongsTo(UjianPeserta::class, 'ujian_peserta_id');
    }

    public function pesertaOffline(): BelongsTo
    {
        return $this->belongsTo(PesertaOffline::class, 'peserta_offline_id');
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
