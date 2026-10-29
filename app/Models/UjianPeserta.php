<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UjianPeserta extends Model
{
    protected $table = 'panritta_ujian_peserta';

    protected $fillable = [
        'ujian_id',
        'user_id',
        'langganan_id',
        'status',
        'waktu_mulai',
        'waktu_selesai',
        'batas_waktu',
        'last_activity_at',
        'auto_submitted',
        'total_nilai',
        'lulus',
        'is_paused',
        'paused_at',
        'sisa_detik_saat_pause',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'ujian_id' => 'integer',
            'user_id' => 'integer',
            'langganan_id' => 'integer',
            'waktu_mulai' => 'datetime',
            'waktu_selesai' => 'datetime',
            'batas_waktu' => 'datetime',
            'last_activity_at' => 'datetime',
            'auto_submitted' => 'boolean',
            'total_nilai' => 'decimal:2',
            'lulus' => 'boolean',
            'is_paused' => 'boolean',
            'paused_at' => 'datetime',
            'sisa_detik_saat_pause' => 'integer',
        ];
    }

    public function ujian(): BelongsTo
    {
        return $this->belongsTo(Ujian::class, 'ujian_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke data peserta offline (jika attempt ini dari peserta offline).
     */
    public function pesertaOffline(): HasOne
    {
        return $this->hasOne(PesertaOffline::class, 'ujian_peserta_id');
    }

    public function jawaban(): HasMany
    {
        return $this->hasMany(UjianJawaban::class, 'ujian_peserta_id');
    }

    public function kategori(): HasMany
    {
        return $this->hasMany(UjianPesertaKategori::class, 'ujian_peserta_id');
    }

    public function langganan(): BelongsTo
    {
        return $this->belongsTo(PesertaLangganan::class, 'langganan_id');
    }

    public function timeExtensions(): HasMany
    {
        return $this->hasMany(TimeExtension::class, 'ujian_peserta_id')->orderByDesc('granted_at');
    }
}
