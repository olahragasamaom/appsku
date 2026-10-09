<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom last_activity_at untuk tracking kapan terakhir peserta
     * berinteraksi dengan server (heartbeat/save answer).
     *
     * Digunakan untuk auto-finalize peserta yang stuck sedang_ujian
     * jika tidak ada aktivitas selama lebih dari N jam (default 4 jam).
     */
    public function up(): void
    {
        Schema::table('panritta_ujian_peserta', function (Blueprint $table) {
            $table->timestamp('last_activity_at')->nullable()->after('batas_waktu');
            $table->index('last_activity_at');
        });
    }

    public function down(): void
    {
        Schema::table('panritta_ujian_peserta', function (Blueprint $table) {
            $table->dropIndex(['last_activity_at']);
            $table->dropColumn('last_activity_at');
        });
    }
};
