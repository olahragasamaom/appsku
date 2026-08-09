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
            // Simpan kode akses versi teks agar admin bisa menampilkan & mencetak ulang kartu
            $table->string('kode_akses_plain', 20)->nullable()->after('kode_akses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('panritta_peserta_offline', function (Blueprint $table) {
            $table->dropColumn('kode_akses_plain');
        });
    }
};
