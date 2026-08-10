<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * MODEL: Latihan Sederhana
 *
 * Merepresentasikan satu baris di tabel "latihan_sederhana".
 * Ini contoh model paling dasar untuk CRUD 4 input text.
 *
 * @property int $id
 * @property string $judul
 * @property string $kode
 * @property string $penulis
 * @property string $keterangan
 */
class LatihanSederhana extends Model
{
    /**
     * Nama tabel di database.
     *
     * Secara default Laravel menebak nama tabel dari nama model
     * (LatihanSederhana -> latihan_sederhanas). Karena tabel kita
     * bernama "latihan_sederhana" (tanpa 's'), kita set manual.
     */
    protected $table = 'latihan_sederhana';

    /**
     * Kolom yang boleh diisi secara massal (mass assignment)
     * lewat create()/update(). Kolom di luar daftar ini akan diabaikan.
     *
     * @var list<string>
     */
    protected $fillable = [
        'judul',
        'kode',
        'penulis',
        'keterangan',
    ];
}
