<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UjianJenisUjian extends Model
{
    protected $table = 'panritta_ujian_jenis_ujian';

    protected $fillable = [
        'ujian_id',
        'jenis_ujian_id',
        'passing_grade',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'ujian_id' => 'integer',
            'jenis_ujian_id' => 'integer',
            'passing_grade' => 'decimal:2',
        ];
    }

    public function ujian(): BelongsTo
    {
        return $this->belongsTo(Ujian::class, 'ujian_id');
    }

    public function jenisUjian(): BelongsTo
    {
        return $this->belongsTo(JenisUjian::class, 'jenis_ujian_id');
    }
}
