<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubIndikator extends Model
{
    /** @use HasFactory<\Database\Factories\SubIndikatorFactory> */
    use HasFactory;

    protected $table = 'panritta_sub_indikator';

    protected $fillable = [
        'jenis_ujian_id',
        'sub_jenis_ujian_id',
        'nama_sub_indikator',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'jenis_ujian_id' => 'integer',
            'sub_jenis_ujian_id' => 'integer',
        ];
    }

    public function jenisUjian(): BelongsTo
    {
        return $this->belongsTo(JenisUjian::class, 'jenis_ujian_id');
    }

    public function subJenisUjian(): BelongsTo
    {
        return $this->belongsTo(SubJenisUjian::class, 'sub_jenis_ujian_id');
    }

    public function soal(): HasMany
    {
        return $this->hasMany(Soal::class, 'sub_indikator_id');
    }
}
