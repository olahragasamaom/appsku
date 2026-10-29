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
            $table->boolean('is_paused')->default(false)->after('status');
            $table->dateTime('paused_at')->nullable()->after('is_paused');
            $table->integer('sisa_detik_saat_pause')->nullable()->after('paused_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('panritta_ujian_peserta', function (Blueprint $table) {
            $table->dropColumn(['is_paused', 'paused_at', 'sisa_detik_saat_pause']);
        });
    }
};
