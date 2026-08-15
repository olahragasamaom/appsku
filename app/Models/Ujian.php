<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MODEL: Ujian
 * =============
 * Mewakili tabel 'panritta_ujian' di database.
 * Ini adalah entitas utama yang menggabungkan pengaturan jadwal, tipe ujian (online/offline),
 * dan berisi kumpulan soal yang akan dikerjakan oleh peserta.
 */
class Ujian extends Model
{
    use HasFactory;

    /** Nama tabel di database. */
    protected $table = 'panritta_ujian';

    /**
     * Kolom-kolom yang boleh diisi (mass assignable).
     */
    protected $fillable = [
        'nama_ujian',
        'tipe_ujian',         // 'online_paket' atau 'offline_kelas'
        'sub_jenis_ujian_id',
        'jumlah_soal',
        'acak_soal',
        'tampilkan_hasil',
        'tanggal_ujian',
        'durasi_ujian',
        'batas_keterlambatan',
        'token_ujian',
        'akses_member',
        'status',             // 'draft', 'aktif', 'selesai'
        'finalized_at',       // waktu finalisasi susunan soal; null = belum final
        'dibuat_oleh',
    ];

    /**
     * Konversi tipe otomatis saat data dibaca/disimpan.
     * Contoh: tanggal_ujian otomatis jadi objek Carbon (datetime).
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'sub_jenis_ujian_id' => 'integer',
            'jumlah_soal' => 'integer',
            'acak_soal' => 'boolean',
            'tampilkan_hasil' => 'boolean',
            'tanggal_ujian' => 'datetime',
            'durasi_ujian' => 'integer',
            'batas_keterlambatan' => 'datetime',
            'akses_member' => 'array',
            'finalized_at' => 'datetime',
            'dibuat_oleh' => 'integer',
        ];
    }

    /**
     * Cek apakah susunan soal ujian ini sudah difinalisasi.
     */
    public function isFinalized(): bool
    {
        return $this->finalized_at !== null;
    }

    /**
     * RELASI: Admin pembuat ujian ini.
     */
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    /**
     * RELASI: Sub jenis ujian utama yang mengelompokkan ujian ini.
     */
    public function subJenisUjian(): BelongsTo
    {
        return $this->belongsTo(SubJenisUjian::class, 'sub_jenis_ujian_id');
    }

    /**
     * RELASI (Many-to-Many): Jenis-jenis ujian (kategori) yang ada di dalam ujian ini,
     * beserta passing grade untuk masing-masing kategori (disimpan di tabel pivot).
     */
    public function jenisUjians(): BelongsToMany
    {
        return $this->belongsToMany(JenisUjian::class, 'panritta_ujian_jenis_ujian', 'ujian_id', 'jenis_ujian_id')
            ->withPivot('passing_grade')
            ->withTimestamps();
    }

    /**
     * RELASI (One-to-Many): Detail kategori (jenis ujian) yang terhubung ke ujian ini.
     */
    public function ujianJenisUjians(): HasMany
    {
        return $this->hasMany(UjianJenisUjian::class, 'ujian_id');
    }

    /**
     * RELASI (One-to-Many): Daftar soal yang sudah dirakit masuk ke ujian ini.
     */
    public function ujianSoals(): HasMany
    {
        return $this->hasMany(UjianSoal::class, 'ujian_id');
    }

    /**
     * RELASI (Many-to-Many): Langsung ke master Soal (lewat tabel pivot ujian_soal).
     */
    public function soals(): BelongsToMany
    {
        return $this->belongsToMany(Soal::class, 'panritta_ujian_soal', 'ujian_id', 'soal_id')
            ->withPivot('jenis_ujian_id', 'urutan')
            ->withTimestamps();
    }

    /**
     * RELASI (One-to-Many): Riwayat partisipasi ujian (attempt) peserta.
     */
    public function peserta(): HasMany
    {
        return $this->hasMany(UjianPeserta::class, 'ujian_id');
    }

    /**
     * RELASI (Many-to-Many): Paket langganan yang memiliki hak akses ke ujian ini.
     */
    public function pakets(): BelongsToMany
    {
        return $this->belongsToMany(Paket::class, 'panritta_paket_ujian', 'ujian_id', 'paket_id')
            ->withTimestamps();
    }

    /**
     * RELASI (One-to-Many): Data kredensial peserta offline (hanya untuk tipe offline).
     */
    public function pesertaOffline(): HasMany
    {
        return $this->hasMany(PesertaOffline::class, 'ujian_id');
    }

    /**
     * Cek apakah ujian ini adalah tipe offline (dikerjakan serentak di kelas).
     */
    public function isOffline(): bool
    {
        return $this->tipe_ujian === 'offline_kelas';
    }

    /**
     * Cek apakah ujian ini adalah tipe online (dikerjakan kapan saja via paket).
     */
    public function isOnline(): bool
    {
        return $this->tipe_ujian === 'online_paket';
    }
}
