<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| MIGRATION: Tabel-tabel untuk Menu "Latihan"
|--------------------------------------------------------------------------
| File ini membuat SEMUA tabel dummy yang dibutuhkan oleh menu Latihan.
| Tujuannya untuk media belajar Laravel (CRUD sederhana + form kompleks).
|
| Ada 2 modul di menu Latihan:
|
|  1. MODUL SEDERHANA  -> tabel: latihan_sederhana
|     CRUD biasa dengan 4 input text.
|
|  2. MODUL DETAIL     -> tabel: latihan_detail + latihan_detail_items
|     Form kompleks yang berkaitan dengan tabel lain (berantai):
|       - latihan_kategori : master data, dipilih lewat DROPDOWN biasa.
|       - latihan_produk   : master data, dipilih lewat MODAL PICKER.
|       - latihan_detail        : header/form utama.
|       - latihan_detail_items  : baris-baris item (child) dari header,
|                                 tiap item memilih produk lewat modal.
|
| Relasi (berantai):
|   latihan_kategori 1---* latihan_detail
|   latihan_detail   1---* latihan_detail_items *---1 latihan_produk
|--------------------------------------------------------------------------
*/
return new class extends Migration
{
    /**
     * Jalankan migration (membuat tabel).
     */
    public function up(): void
    {
        /*
        |----------------------------------------------------------------
        | 1) MODUL SEDERHANA
        |----------------------------------------------------------------
        | Hanya menyimpan 4 kolom text sederhana. Ini contoh CRUD dasar.
        */
        Schema::create('latihan_sederhana', function (Blueprint $table) {
            $table->id();
            $table->string('judul');          // Input text 1
            $table->string('kode');           // Input text 2
            $table->string('penulis');        // Input text 3
            $table->string('keterangan');     // Input text 4
            $table->timestamps();
        });

        /*
        |----------------------------------------------------------------
        | 2) MASTER: KATEGORI (untuk dropdown di modul Detail)
        |----------------------------------------------------------------
        | Tabel lookup sederhana. Dipakai sebagai pilihan dropdown
        | pada form Detail. Kita isi lewat seeder.
        */
        Schema::create('latihan_kategori', function (Blueprint $table) {
            $table->id();
            $table->string('nama');           // Nama kategori, mis: "Elektronik"
            $table->timestamps();
        });

        /*
        |----------------------------------------------------------------
        | 3) MASTER: PRODUK (untuk MODAL PICKER di modul Detail)
        |----------------------------------------------------------------
        | Tabel produk yang jumlahnya banyak, sehingga tidak cocok
        | ditampilkan sebagai dropdown. Karena itu pemilihannya lewat
        | modal (dengan pencarian). Setiap produk punya harga default
        | yang akan otomatis mengisi form saat produk dipilih.
        */
        Schema::create('latihan_produk', function (Blueprint $table) {
            $table->id();
            $table->string('kode_produk')->unique();  // mis: PRD-0001
            $table->string('nama');                    // Nama produk
            $table->decimal('harga', 15, 2)->default(0); // Harga default produk
            $table->timestamps();
        });

        /*
        |----------------------------------------------------------------
        | 4) HEADER: DETAIL (form utama modul Detail)
        |----------------------------------------------------------------
        | Ini adalah "header" transaksi. Berkaitan dengan kategori
        | (relasi belongsTo) dan memiliki banyak item (hasMany).
        | Kolom "total" dihitung dari penjumlahan seluruh subtotal item.
        */
        Schema::create('latihan_detail', function (Blueprint $table) {
            $table->id();
            $table->string('nomor')->unique();       // Auto-generate: DET20260001
            $table->string('nama_transaksi');        // Judul transaksi
            // Relasi ke kategori. nullOnDelete: jika kategori dihapus,
            // kolom ini jadi null (bukan ikut terhapus).
            $table->foreignId('latihan_kategori_id')
                ->nullable()
                ->constrained('latihan_kategori')
                ->nullOnDelete();
            $table->date('tanggal');                 // Tanggal transaksi
            $table->decimal('total', 15, 2)->default(0); // Total keseluruhan
            $table->text('catatan')->nullable();     // Catatan opsional
            $table->timestamps();
        });

        /*
        |----------------------------------------------------------------
        | 5) CHILD: DETAIL ITEMS (baris item dari header)
        |----------------------------------------------------------------
        | Setiap header bisa punya banyak item. Tiap item menunjuk ke
        | satu produk (dipilih lewat modal). cascadeOnDelete: jika
        | header dihapus, seluruh itemnya ikut terhapus.
        */
        Schema::create('latihan_detail_items', function (Blueprint $table) {
            $table->id();
            // Relasi ke header. Jika header dihapus, item ikut terhapus.
            $table->foreignId('latihan_detail_id')
                ->constrained('latihan_detail')
                ->cascadeOnDelete();
            // Relasi ke produk (dipilih lewat modal picker).
            $table->foreignId('latihan_produk_id')
                ->constrained('latihan_produk')
                ->cascadeOnDelete();
            $table->unsignedInteger('qty')->default(1);   // Jumlah
            $table->decimal('harga', 15, 2)->default(0);  // Harga saat transaksi
            $table->decimal('subtotal', 15, 2)->default(0); // qty * harga
            $table->timestamps();
        });
    }

    /**
     * Rollback migration (menghapus tabel).
     * Urutan penghapusan harus terbalik dari pembuatan agar tidak
     * melanggar foreign key constraint.
     */
    public function down(): void
    {
        Schema::dropIfExists('latihan_detail_items');
        Schema::dropIfExists('latihan_detail');
        Schema::dropIfExists('latihan_produk');
        Schema::dropIfExists('latihan_kategori');
        Schema::dropIfExists('latihan_sederhana');
    }
};
