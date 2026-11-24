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
        Schema::table('panritta_ujian_peserta', function (Blueprint $table) {
            $table->json('soal_order')->nullable()->comment('JSON array: [soal_id, soal_id, ...] urutan random soal dalam sub-indikator');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('panritta_ujian_peserta', function (Blueprint $table) {
            $table->dropColumn('soal_order');
        });
    }
};
