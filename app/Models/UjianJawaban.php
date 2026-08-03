<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UjianJawaban extends Model
{
    protected $table = 'panritta_ujian_jawaban';

    protected $fillable = [
        'ujian_peserta_id',
        'ujian_soal_id',
        'soal_id',
        'jenis_ujian_id',
        'jawaban',
        'nilai',
        'benar',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'ujian_peserta_id' => 'integer',
            'ujian_soal_id' => 'integer',
            'soal_id' => 'integer',
            'jenis_ujian_id' => 'integer',
            'nilai' => 'decimal:2',
            'benar' => 'boolean',
        ];
    }

    public function ujianPeserta(): BelongsTo
    {
        return $this->belongsTo(UjianPeserta::class, 'ujian_peserta_id');
    }

    public function soal(): BelongsTo
    {
        return $this->belongsTo(Soal::class, 'soal_id');
    }

    public function jenisUjian(): BelongsTo
    {
        return $this->belongsTo(JenisUjian::class, 'jenis_ujian_id');
    }
}
