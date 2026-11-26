<?php

namespace App\Models;

use Database\Factories\PesertaOfflineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PesertaOffline extends Model
{
    /** @use HasFactory<PesertaOfflineFactory> */
    use HasFactory;

    protected $table = 'panritta_peserta_offline';

    protected $fillable = [
        'ujian_id',
        'kelas_id',
        'nomor_peserta',
        'nama_peserta',
        'nama_kelas',
        'nomor_hp',
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
            'kelas_id' => 'integer',
            'ujian_peserta_id' => 'integer',
            'is_active' => 'boolean',
            'is_blocked' => 'boolean',
            'blocked_at' => 'datetime',
            'kode_akses_reset_at' => 'datetime',
            'kode_akses_reset_by' => 'integer',
        ];
    }

    public function getIsActiveAttribute($value): bool
    {
        if ($this->pivot && isset($this->pivot->is_active)) {
            return (bool) $this->pivot->is_active;
        }

        return (bool) $value;
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function ujian(): BelongsTo
    {
        return $this->belongsTo(Ujian::class, 'ujian_id');
    }

    public function ujians(): BelongsToMany
    {
        return $this->belongsToMany(Ujian::class, 'panritta_ujian_peserta_offline', 'peserta_offline_id', 'ujian_id')
            ->withPivot(['id', 'is_active', 'ujian_peserta_id'])
            ->withTimestamps();
    }

    public function ujianPeserta(): BelongsTo
    {
        return $this->belongsTo(UjianPeserta::class, 'ujian_peserta_id');
    }

    public function ujianPesertas(): HasMany
    {
        return $this->hasMany(UjianPeserta::class, 'peserta_offline_id');
    }
}
