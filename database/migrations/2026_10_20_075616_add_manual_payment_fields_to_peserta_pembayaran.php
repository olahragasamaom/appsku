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
        Schema::table('panritta_peserta_pembayaran', function (Blueprint $table) {
            $table->string('bukti_pembayaran')->nullable()->after('metode_pembayaran');
            $table->text('catatan_peserta')->nullable()->after('bukti_pembayaran');
            $table->string('bukti_pembayaran_verified_by')->nullable()->after('catatan_peserta');
            $table->text('catatan_verifikasi')->nullable()->after('bukti_pembayaran_verified_by');
            $table->dateTime('diverifikasi_pada')->nullable()->after('catatan_verifikasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('panritta_peserta_pembayaran', function (Blueprint $table) {
            $table->dropColumn([
                'bukti_pembayaran',
                'catatan_peserta',
                'bukti_pembayaran_verified_by',
                'catatan_verifikasi',
                'diverifikasi_pada',
            ]);
        });
    }
};
