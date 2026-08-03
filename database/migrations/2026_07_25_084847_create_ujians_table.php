<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('panritta_ujian', function (Blueprint $table) {
            $table->id();
            $table->string('nama_ujian', 255);
            $table->enum('tipe_ujian', ['offline_kelas', 'online_paket'])->default('offline_kelas');
            $table->unsignedSmallInteger('jumlah_soal')->default(0);
            $table->boolean('acak_soal')->default(false);
            $table->boolean('tampilkan_hasil')->default(true);
            $table->dateTime('tanggal_ujian')->nullable();
            $table->unsignedInteger('durasi_ujian')->nullable();
            $table->dateTime('batas_keterlambatan')->nullable();
            $table->string('token_ujian', 50)->nullable();
            $table->json('akses_member')->nullable();
            $table->enum('status', ['draft', 'aktif', 'selesai'])->default('draft');
            $table->unsignedBigInteger('dibuat_oleh');
            $table->timestamps();

            $table->foreign('dibuat_oleh')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panritta_ujian');
    }
};
