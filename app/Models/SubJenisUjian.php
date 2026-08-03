<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubJenisUjian extends Model
{
    /** @use HasFactory<\Database\Factories\SubJenisUjianFactory> */
    use HasFactory;

    protected $table = 'panritta_sub_jenis_ujian';

    protected $fillable = [
        'jenis_ujian_id',
        'nama_sub_jenis_ujian',
        'sistem_penilaian',
        'jumlah_jawaban_pilihan_ganda',
        'nilai_benar',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'jenis_ujian_id' => 'integer',
            'jumlah_jawaban_pilihan_ganda' => 'integer',
            'nilai_benar' => 'decimal:2',
        ];
    }

    public function jenisUjian(): BelongsTo
    {
        return $this->belongsTo(JenisUjian::class, 'jenis_ujian_id');
    }

    public function subIndikator(): HasMany
    {
        return $this->hasMany(SubIndikator::class, 'sub_jenis_ujian_id');
    }
}
