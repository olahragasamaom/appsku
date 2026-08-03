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
        Schema::create('panritta_ujian_jawaban', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ujian_peserta_id');
            $table->unsignedBigInteger('ujian_soal_id');
            $table->unsignedBigInteger('soal_id');
            $table->integer('jenis_ujian_id');
            $table->enum('jawaban', ['A', 'B', 'C', 'D', 'E'])->nullable();
            $table->decimal('nilai', 6, 2)->default(0);
            $table->boolean('benar')->nullable();
            $table->timestamps();

            $table->unique(['ujian_peserta_id', 'ujian_soal_id'], 'ujian_jawaban_unique');

            $table->foreign('ujian_peserta_id')
                ->references('id')
                ->on('panritta_ujian_peserta')
                ->cascadeOnDelete();

            $table->foreign('ujian_soal_id')
                ->references('id')
                ->on('panritta_ujian_soal')
                ->cascadeOnDelete();

            $table->foreign('soal_id')
                ->references('id')
                ->on('panritta_soal')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panritta_ujian_jawaban');
    }
};
