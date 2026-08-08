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
            if (! Schema::hasColumn('panritta_ujian_peserta', 'langganan_id')) {
                $table->unsignedBigInteger('langganan_id')->nullable()->after('user_id');

                $table->foreign('langganan_id')
                    ->references('id')
                    ->on('panritta_peserta_langganan')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('panritta_ujian_peserta', 'batas_waktu')) {
                $table->dateTime('batas_waktu')->nullable()->after('waktu_selesai');
            }

            if (! Schema::hasColumn('panritta_ujian_peserta', 'auto_submitted')) {
                $table->boolean('auto_submitted')->default(false)->after('batas_waktu');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('panritta_ujian_peserta', function (Blueprint $table) {
            if (Schema::hasColumn('panritta_ujian_peserta', 'langganan_id')) {
                $table->dropForeign(['langganan_id']);
                $table->dropColumn('langganan_id');
            }

            if (Schema::hasColumn('panritta_ujian_peserta', 'batas_waktu')) {
                $table->dropColumn('batas_waktu');
            }

            if (Schema::hasColumn('panritta_ujian_peserta', 'auto_submitted')) {
                $table->dropColumn('auto_submitted');
            }
        });
    }
};
