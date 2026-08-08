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
        Schema::create('panritta_sub_indikator', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('jenis_ujian_id');
            $table->unsignedBigInteger('sub_jenis_ujian_id');
            $table->string('nama_sub_indikator', 255);
            $table->timestamps();

            $table->foreign('jenis_ujian_id')
                ->references('id')
                ->on('panritta_jenis_ujian')
                ->cascadeOnDelete();

            $table->foreign('sub_jenis_ujian_id')
                ->references('id')
                ->on('panritta_sub_jenis_ujian')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panritta_sub_indikator');
    }
};
