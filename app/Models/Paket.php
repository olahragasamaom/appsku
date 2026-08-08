<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MODEL: Paket
 * =============
 * Mewakili tabel 'panritta_paket' di database.
 * Merupakan paket belajar/berlangganan yang bisa dibeli oleh peserta online.
 * Menentukan hak akses peserta terhadap ujian, video pembahasan, dan analitik.
 */
class Paket extends Model
{
    use HasFactory;

    protected $table = 'panritta_paket';

    protected $fillable = [
        'nama_paket',
        'slug',
        'deskripsi',
        'harga',
        'durasi_hari',
        'kuota_ujian',
        'video_pembahasan',
        'analitik',
        'sertifikat',
        'is_active',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'harga' => 'decimal:2',
            'durasi_hari' => 'integer',
            'kuota_ujian' => 'integer',
            'video_pembahasan' => 'boolean',
            'analitik' => 'boolean',
            'sertifikat' => 'boolean',
            'is_active' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    /**
     * RELASI (One-to-Many): Daftar user/peserta yang berlangganan paket ini.
     */
    public function langganan(): HasMany
    {
        return $this->hasMany(PesertaLangganan::class, 'paket_id');
    }

    /**
     * RELASI (Many-to-Many): Daftar ujian online yang tergabung dalam paket ini.
     */
    public function ujians(): BelongsToMany
    {
        return $this->belongsToMany(Ujian::class, 'panritta_paket_ujian', 'paket_id', 'ujian_id')
            ->withTimestamps();
    }

    /**
     * Cek apakah paket ini gratis (harga 0).
     */
    public function isGratis(): bool
    {
        return (float) $this->harga <= 0;
    }
}
