<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MODEL: JenisUjian (Jenis Ujian)
 * ================================
 * Model adalah "wakil" satu tabel di database dalam bentuk objek PHP.
 * Lewat model ini kita bisa membaca/menyimpan data tanpa menulis query SQL manual.
 *
 * POSISI DALAM HIERARKI (dari atas ke bawah):
 *   JenisUjian  ->  SubJenisUjian  ->  SubIndikator  ->  Soal
 *   (contoh: "SKD"  ->  "TWK"        ->  "Pancasila"    ->  butir soal)
 *
 * Ini adalah level PALING ATAS. Satu JenisUjian bisa punya banyak SubJenisUjian.
 */
class JenisUjian extends Model
{
    /**
     * HasFactory: memberi kemampuan membuat data dummy untuk testing/seeder,
     * mis. JenisUjian::factory()->create(). Definisi datanya ada di database/factories.
     *
     * @use HasFactory<\Database\Factories\JenisUjianFactory>
     */
    use HasFactory;

    /**
     * Nama tabel di database. Secara default Laravel menebak "jenis_ujians",
     * tapi karena tabel kita bernama "panritta_jenis_ujian", kita tulis eksplisit.
     */
    protected $table = 'panritta_jenis_ujian';

    /**
     * Tabel ini TIDAK punya kolom created_at & updated_at,
     * jadi fitur timestamp otomatis Laravel dimatikan (false).
     */
    public $timestamps = false;

    /**
     * $fillable = daftar kolom yang BOLEH diisi massal (mass assignment),
     * mis. lewat JenisUjian::create([...]) atau ->update([...]).
     * Ini pengaman agar kolom lain (seperti id) tidak bisa diisi sembarangan.
     */
    protected $fillable = [
        'nama_jenis_ujian',
        'keterangan',
    ];

    /**
     * casts() = konversi tipe otomatis saat data dibaca dari database.
     * Contoh: kolom 'id' selalu dikembalikan sebagai integer, bukan string.
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
        ];
    }

    /**
     * RELASI: satu JenisUjian memiliki BANYAK SubJenisUjian (one-to-many).
     * Cara pakai: $jenisUjian->subJenisUjian  -> menghasilkan koleksi SubJenisUjian.
     * Parameter kedua 'jenis_ujian_id' adalah nama kolom foreign key
     * di tabel panritta_sub_jenis_ujian yang menunjuk ke tabel ini.
     */
    public function subJenisUjian(): HasMany
    {
        return $this->hasMany(SubJenisUjian::class, 'jenis_ujian_id');
    }
}
