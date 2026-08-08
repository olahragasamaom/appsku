<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UjianPesertaKategori extends Model
{
    /** @use HasFactory<\Database\Factories\UjianPesertaKategoriFactory> */
    use HasFactory;

    protected $table = 'panritta_ujian_peserta_kategori';

    protected $fillable = [
        'ujian_peserta_id',
        'jenis_ujian_id',
        'nilai_kategori',
        'passing_grade',
        'lulus_kategori',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'ujian_peserta_id' => 'integer',
            'jenis_ujian_id' => 'integer',
            'nilai_kategori' => 'decimal:2',
            'passing_grade' => 'decimal:2',
            'lulus_kategori' => 'boolean',
        ];
    }

    public function ujianPeserta(): BelongsTo
    {
        return $this->belongsTo(UjianPeserta::class, 'ujian_peserta_id');
    }

    public function jenisUjian(): BelongsTo
    {
        return $this->belongsTo(JenisUjian::class, 'jenis_ujian_id');
    }
}
