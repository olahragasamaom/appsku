<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paket extends Model
{
    /** @use HasFactory<\Database\Factories\PaketFactory> */
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

    public function langganan(): HasMany
    {
        return $this->hasMany(PesertaLangganan::class, 'paket_id');
    }

    public function isGratis(): bool
    {
        return (float) $this->harga <= 0;
    }
}
