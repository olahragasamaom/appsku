<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MODEL: Soal (Butir Soal)
 * =========================
 * Level PALING BAWAH dalam hierarki:
 *   JenisUjian  ->  SubJenisUjian  ->  SubIndikator  ->  [Soal]
 *
 * Ini model dengan kolom TERBANYAK karena satu soal menyimpan:
 *   - teks & gambar soal
 *   - opsi jawaban A-E (teks & gambar)
 *   - kunci jawaban (untuk sistem "benar_salah")
 *   - nilai bobot per opsi (untuk sistem "tiap_jawaban_ada_poin")
 *   - pembahasan (teks & gambar)
 *   - siapa pembuatnya (pembuat_soal_id)
 *
 * Catatan: kolom bobot bersifat KONDISIONAL. Tergantung sistem_penilaian di
 * SubJenisUjian, sebagian kolom akan diisi & sebagian dikosongkan (dikelola
 * di SoalController & SoalRequest).
 */
class Soal extends Model
{
    /** @use HasFactory<\Database\Factories\SoalFactory> */
    use HasFactory;

    /** Nama tabel di database. */
    protected $table = 'panritta_soal';

    /** Semua kolom yang boleh diisi massal saat create()/update(). */
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

    /**
     * Konversi tipe otomatis. Semua kolom nilai bobot dijadikan decimal:2
     * agar konsisten 2 angka di belakang koma saat dibaca.
     */
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

    /**
     * RELASI KE ATAS (induk): Soal ini milik satu SubIndikator.
     * Dari sini bisa menelusuri ke atas:
     *   $soal->subIndikator->subJenisUjian->jenisUjian
     */
    public function subIndikator(): BelongsTo
    {
        return $this->belongsTo(SubIndikator::class, 'sub_indikator_id');
    }

    /**
     * RELASI: Soal ini dibuat oleh satu User (admin pembuat soal).
     * foreign key-nya 'pembuat_soal_id' menunjuk ke tabel users.
     * Cara pakai: $soal->pembuat->name
     */
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembuat_soal_id');
    }
}
