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
            $table->foreignId('peserta_offline_id')->nullable()->constrained('panritta_peserta_offline')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('panritta_ujian_peserta', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['peserta_offline_id_foreign']);
            $table->dropColumn('peserta_offline_id');
        });
    }
};
