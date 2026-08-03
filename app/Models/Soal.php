<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Soal extends Model
{
    /** @use HasFactory<\Database\Factories\SoalFactory> */
    use HasFactory;

    protected $table = 'panritta_soal';

    protected $fillable = [
        'sub_indikator_id',
        'soal',
        'gambar_soal',
        'opsi_a',
        'opsi_b',
        'opsi_c',
        'opsi_d',
        'opsi_e',
        'gambar_opsi_a',
        'gambar_opsi_b',
        'gambar_opsi_c',
        'gambar_opsi_d',
        'gambar_opsi_e',
        'kunci_jawaban',
        'nilai_bobot_benar',
        'nilai_bobot_a',
        'nilai_bobot_b',
        'nilai_bobot_c',
        'nilai_bobot_d',
        'nilai_bobot_e',
        'pembahasan',
        'gambar_pembahasan',
        'pembuat_soal_id',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'sub_indikator_id' => 'integer',
            'nilai_bobot_benar' => 'decimal:2',
            'nilai_bobot_a' => 'decimal:2',
            'nilai_bobot_b' => 'decimal:2',
            'nilai_bobot_c' => 'decimal:2',
            'nilai_bobot_d' => 'decimal:2',
            'nilai_bobot_e' => 'decimal:2',
            'pembuat_soal_id' => 'integer',
        ];
    }

    public function subIndikator(): BelongsTo
    {
        return $this->belongsTo(SubIndikator::class, 'sub_indikator_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembuat_soal_id');
    }
}
