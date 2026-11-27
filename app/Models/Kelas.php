<?php

namespace App\Models;

use Database\Factories\KelasFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    /** @use HasFactory<KelasFactory> *test tambah kelas */
    use HasFactory;
    protected $table = 'panritta_kelas';

    protected $fillable = [
        'nama_kelas',
        'deskripsi',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
        ];
    }

    public function pesertaOffline(): HasMany
    {
        return $this->hasMany(PesertaOffline::class, 'kelas_id');
    }
}
