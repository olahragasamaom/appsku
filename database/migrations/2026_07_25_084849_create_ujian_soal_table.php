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
        Schema::create('panritta_ujian_soal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ujian_id');
            $table->unsignedBigInteger('soal_id');
            $table->unsignedInteger('jenis_ujian_id');
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->unique(['ujian_id', 'soal_id'], 'ujian_soal_unique');

            $table->foreign('ujian_id')
                ->references('id')
                ->on('panritta_ujian')
                ->cascadeOnDelete();

            $table->foreign('soal_id')
                ->references('id')
                ->on('panritta_soal')
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
        Schema::dropIfExists('panritta_ujian_soal');
    }
};
