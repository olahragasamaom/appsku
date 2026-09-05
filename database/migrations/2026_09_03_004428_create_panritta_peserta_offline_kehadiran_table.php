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
        Schema::create('panritta_peserta_offline_kehadiran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peserta_offline_id')->constrained('panritta_peserta_offline')->cascadeOnDelete();
            $table->foreignId('ujian_id')->constrained('panritta_ujian')->cascadeOnDelete();
            $table->enum('status_kehadiran', ['hadir', 'tidak_hadir'])->default('tidak_hadir');
            $table->timestamps();

            $table->unique(['peserta_offline_id', 'ujian_id'], 'kehadiran_peserta_ujian_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panritta_peserta_offline_kehadiran');
    }
};
