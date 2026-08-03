<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ujian extends Model
{
    /** @use HasFactory<\Database\Factories\UjianFactory> */
    use HasFactory;

    protected $table = 'panritta_ujian';

    protected $fillable = [
        'nama_ujian',
        'tipe_ujian',
        'jumlah_soal',
        'acak_soal',
        'tampilkan_hasil',
        'tanggal_ujian',
        'durasi_ujian',
        'batas_keterlambatan',
        'token_ujian',
        'akses_member',
        'status',
        'dibuat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'jumlah_soal' => 'integer',
            'acak_soal' => 'boolean',
            'tampilkan_hasil' => 'boolean',
            'tanggal_ujian' => 'datetime',
            'durasi_ujian' => 'integer',
            'batas_keterlambatan' => 'datetime',
            'akses_member' => 'array',
            'dibuat_oleh' => 'integer',
        ];
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function jenisUjians(): BelongsToMany
    {
        return $this->belongsToMany(JenisUjian::class, 'panritta_ujian_jenis_ujian', 'ujian_id', 'jenis_ujian_id')
            ->withPivot('passing_grade')
            ->withTimestamps();
    }

    public function ujianJenisUjians(): HasMany
    {
        return $this->hasMany(UjianJenisUjian::class, 'ujian_id');
    }

    public function ujianSoals(): HasMany
    {
        return $this->hasMany(UjianSoal::class, 'ujian_id');
    }

    public function soals(): BelongsToMany
    {
        return $this->belongsToMany(Soal::class, 'panritta_ujian_soal', 'ujian_id', 'soal_id')
            ->withPivot('jenis_ujian_id', 'urutan')
            ->withTimestamps();
    }

    public function peserta(): HasMany
    {
        return $this->hasMany(UjianPeserta::class, 'ujian_id');
    }

    public function isOffline(): bool
    {
        return $this->tipe_ujian === 'offline_kelas';
    }

    public function isOnline(): bool
    {
        return $this->tipe_ujian === 'online_paket';
    }
}
