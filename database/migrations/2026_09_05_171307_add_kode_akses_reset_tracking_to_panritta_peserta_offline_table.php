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
        Schema::table('panritta_peserta_offline', function (Blueprint $table) {
            $table->timestamp('kode_akses_reset_at')->nullable()->after('blocked_reason');
            $table->foreignId('kode_akses_reset_by')->nullable()->after('kode_akses_reset_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('panritta_peserta_offline', function (Blueprint $table) {
            $table->dropForeign(['kode_akses_reset_by']);
            $table->dropColumn(['kode_akses_reset_at', 'kode_akses_reset_by']);
        });
    }
};
