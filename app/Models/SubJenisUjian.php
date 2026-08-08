<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MODEL: SubJenisUjian (Sub Jenis Ujian)
 * =======================================
 * Level KE-2 dalam hierarki:
 *   JenisUjian  ->  [SubJenisUjian]  ->  SubIndikator  ->  Soal
 *
 * Model ini "menengah": ia PUNYA INDUK (JenisUjian) sekaligus PUNYA ANAK
 * (SubIndikator). Perhatikan ia memakai DUA jenis relasi: belongsTo & hasMany.
 *
 * Di sinilah aturan penilaian soal ditentukan, mis:
 *   - sistem_penilaian: "benar_salah" atau "tiap_jawaban_ada_poin"
 *   - jumlah_jawaban_pilihan_ganda: 4 (A-D) atau 5 (A-E)
 *   - nilai_benar: poin default bila jawaban benar
 */
class SubJenisUjian extends Model
{
    /** @use HasFactory<\Database\Factories\SubJenisUjianFactory> */
    use HasFactory;

    /** Nama tabel di database. */
    protected $table = 'panritta_sub_jenis_ujian';

    /**
     * Kolom yang boleh diisi massal.
     * Catatan: 'jenis_ujian_id' adalah foreign key -> penghubung ke tabel induk.
     */
    protected $fillable = [
        'jenis_ujian_id',
        'nama_sub_jenis_ujian',
        'keterangan',
        'urutan',
        'sistem_penilaian',
        'jumlah_jawaban_pilihan_ganda',
        'nilai_benar',
    ];

    /**
     * Konversi tipe otomatis. Contoh penting:
     * 'nilai_benar' => 'decimal:2' -> selalu 2 angka di belakang koma (mis. 5.00).
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'jenis_ujian_id' => 'integer',
            'urutan' => 'integer',
            'jumlah_jawaban_pilihan_ganda' => 'integer',
            'nilai_benar' => 'decimal:2',
        ];
    }

    /**
     * RELASI KE ATAS (induk): SubJenisUjian ini MILIK satu JenisUjian.
     * belongsTo dipakai di sisi yang MENYIMPAN foreign key (jenis_ujian_id).
     * Cara pakai: $subJenisUjian->jenisUjian->nama_jenis_ujian
     */
    public function jenisUjian(): BelongsTo
    {
        return $this->belongsTo(JenisUjian::class, 'jenis_ujian_id');
    }

    /**
     * RELASI KE BAWAH (anak): satu SubJenisUjian punya BANYAK SubIndikator.
     * Cara pakai: $subJenisUjian->subIndikator (koleksi SubIndikator).
     */
    public function subIndikator(): HasMany
    {
        return $this->hasMany(SubIndikator::class, 'sub_jenis_ujian_id');
    }
}
