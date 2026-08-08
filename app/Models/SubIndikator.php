<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MODEL: SubIndikator (Sub Indikator)
 * ====================================
 * Level KE-3 dalam hierarki:
 *   JenisUjian  ->  SubJenisUjian  ->  [SubIndikator]  ->  Soal
 *
 * SubIndikator adalah topik/materi spesifik tempat Soal digantungkan.
 *
 * CATATAN DESAIN: model ini menyimpan DUA foreign key sekaligus:
 *   - sub_jenis_ujian_id -> induk langsungnya (SubJenisUjian)
 *   - jenis_ujian_id     -> "jalan pintas" ke kakeknya (JenisUjian)
 * Menyimpan jenis_ujian_id di sini adalah denormalisasi sengaja agar query
 * filter/laporan lebih cepat (tak perlu selalu menelusuri lewat SubJenisUjian).
 * Nilai jenis_ujian_id ini diisi OTOMATIS oleh SubIndikatorRequest.
 */
class SubIndikator extends Model
{
    /** @use HasFactory<\Database\Factories\SubIndikatorFactory> */
    use HasFactory;

    /** Nama tabel di database. */
    protected $table = 'panritta_sub_indikator';

    /** Kolom yang boleh diisi massal (dua-duanya foreign key + nama). */
    protected $fillable = [
        'jenis_ujian_id',
        'sub_jenis_ujian_id',
        'nama_sub_indikator',
    ];

    /** Konversi tipe otomatis. */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'jenis_ujian_id' => 'integer',
            'sub_jenis_ujian_id' => 'integer',
        ];
    }

    /**
     * RELASI KE ATAS (kakek langsung via jalan pintas): milik satu JenisUjian.
     */
    public function jenisUjian(): BelongsTo
    {
        return $this->belongsTo(JenisUjian::class, 'jenis_ujian_id');
    }

    /**
     * RELASI KE ATAS (induk langsung): milik satu SubJenisUjian.
     * Untuk mengambil aturan penilaian: $subIndikator->subJenisUjian->sistem_penilaian
     */
    public function subJenisUjian(): BelongsTo
    {
        return $this->belongsTo(SubJenisUjian::class, 'sub_jenis_ujian_id');
    }

    /**
     * RELASI KE BAWAH (anak): satu SubIndikator punya BANYAK Soal.
     */
    public function soal(): HasMany
    {
        return $this->hasMany(Soal::class, 'sub_indikator_id');
    }
}
