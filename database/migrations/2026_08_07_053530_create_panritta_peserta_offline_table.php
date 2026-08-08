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
        Schema::create('panritta_peserta_offline', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ujian_id');
            $table->string('nomor_peserta', 50);
            $table->string('nama_peserta', 255);
            $table->string('kode_akses', 255);
            $table->unsignedBigInteger('ujian_peserta_id')->nullable();
            $table->timestamps();

            $table->unique(['ujian_id', 'nomor_peserta'], 'peserta_offline_unique');

            $table->foreign('ujian_id')
                ->references('id')
                ->on('panritta_ujian')
                ->cascadeOnDelete();

            $table->foreign('ujian_peserta_id')
                ->references('id')
                ->on('panritta_ujian_peserta')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panritta_peserta_offline');
    }
};
