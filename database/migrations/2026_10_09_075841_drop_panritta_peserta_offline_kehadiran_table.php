<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop tabel kehadiran peserta offline.
     *
     * Alasan: Konsep kehadiran dihilangkan, digantikan sepenuhnya oleh
     * is_active di peserta_offline. Admin cukup aktifkan/nonaktifkan
     * peserta untuk mengizinkan/menolak ujian.
     */
    public function up(): void
    {
        Schema::dropIfExists('panritta_peserta_offline_kehadiran');
    }

    /**
     * Rollback: Buat ulang tabel dengan struktur awal.
     */
    public function down(): void
    {
        Schema::create('panritta_peserta_offline_kehadiran', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('peserta_offline_id');
            $table->unsignedBigInteger('ujian_id');
            $table->enum('status_kehadiran', ['hadir', 'tidak_hadir'])->default('tidak_hadir');
            $table->timestamp('waktu_kehadiran')->nullable();
            $table->timestamps();

            $table->foreign('peserta_offline_id')
                ->references('id')
                ->on('panritta_peserta_offline')
                ->cascadeOnDelete();

            $table->foreign('ujian_id')
                ->references('id')
                ->on('panritta_ujian')
                ->cascadeOnDelete();
        });
    }
};
