<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesertaOffline extends Model
{
    /** @use HasFactory<\Database\Factories\PesertaOfflineFactory> */
    use HasFactory;

    protected $table = 'panritta_peserta_offline';

    protected $fillable = [
        'ujian_id',
        'nomor_peserta',
        'nama_peserta',
        'kode_akses',
        'kode_akses_plain',
        'ujian_peserta_id',
    ];

    // Hanya sembunyikan hash bcrypt-nya. kode_akses_plain sengaja ditampilkan
    // agar admin bisa melihat & mencetak ulang kartu peserta.
    protected $hidden = [
        'kode_akses',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'ujian_id' => 'integer',
            'ujian_peserta_id' => 'integer',
        ];
    }

    public function ujian(): BelongsTo
    {
        return $this->belongsTo(Ujian::class, 'ujian_id');
    }

    public function ujianPeserta(): BelongsTo
    {
        return $this->belongsTo(UjianPeserta::class, 'ujian_peserta_id');
    }
}
