<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfflineParticipantSession extends Model
{
    /** @use HasFactory<\Database\Factories\OfflineParticipantSessionFactory> */
    use HasFactory;

    protected $table = 'offline_participant_sessions';

    protected $fillable = [
        'peserta_offline_id',
        'ujian_id',
        'session_token',
        'status',
        'device_info',
        'login_at',
        'last_activity_at',
        'logout_at',
        'logout_reason',
    ];

    protected function casts(): array
    {
        return [
            'device_info' => 'array',
            'login_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'logout_at' => 'datetime',
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

    public function isActive(): bool
    {
        return in_array($this->status, ['logged_in', 'sedang_ujian']);
    }

    public function isOnline(): bool
    {
        if (! $this->last_activity_at) {
            return false;
        }

        // Consider online jika activity dalam 30 detik terakhir
        return $this->last_activity_at->gt(now()->subSeconds(30));
    }
}
