<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisUjian extends Model
{
    /** @use HasFactory<\Database\Factories\JenisUjianFactory> */
    use HasFactory;

    protected $table = 'panritta_jenis_ujian';

    public $timestamps = false;

    protected $fillable = [
        'nama_jenis_ujian',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
        ];
    }
}
