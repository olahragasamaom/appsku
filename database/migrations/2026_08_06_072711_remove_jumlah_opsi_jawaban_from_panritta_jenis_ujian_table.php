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
        Schema::table('panritta_jenis_ujian', function (Blueprint $table) {
            if (Schema::hasColumn('panritta_jenis_ujian', 'jumlah_opsi_jawaban')) {
                $table->dropColumn('jumlah_opsi_jawaban');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('panritta_jenis_ujian', function (Blueprint $table) {
            if (! Schema::hasColumn('panritta_jenis_ujian', 'jumlah_opsi_jawaban')) {
                $table->integer('jumlah_opsi_jawaban')->after('nama_jenis_ujian');
            }
        });
    }
};
