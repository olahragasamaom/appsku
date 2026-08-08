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
        Schema::create('panritta_ujian_peserta_kategori', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ujian_peserta_id');
            $table->unsignedInteger('jenis_ujian_id');
            $table->decimal('nilai_kategori', 8, 2)->default(0);
            $table->decimal('passing_grade', 6, 2)->nullable();
            $table->boolean('lulus_kategori')->nullable();
            $table->timestamps();

            $table->unique(['ujian_peserta_id', 'jenis_ujian_id'], 'ujian_peserta_kategori_unique');

            $table->foreign('ujian_peserta_id')
                ->references('id')
                ->on('panritta_ujian_peserta')
                ->cascadeOnDelete();

            $table->foreign('jenis_ujian_id')
                ->references('id')
                ->on('panritta_jenis_ujian')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panritta_ujian_peserta_kategori');
    }
};
