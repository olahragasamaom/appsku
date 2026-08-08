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
        Schema::table('panritta_ujian', function (Blueprint $table) {
            if (! Schema::hasColumn('panritta_ujian', 'sub_jenis_ujian_id')) {
                $table->unsignedBigInteger('sub_jenis_ujian_id')->nullable()->after('tipe_ujian');

                $table->foreign('sub_jenis_ujian_id')
                    ->references('id')
                    ->on('panritta_sub_jenis_ujian')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('panritta_ujian', function (Blueprint $table) {
            if (Schema::hasColumn('panritta_ujian', 'sub_jenis_ujian_id')) {
                $table->dropForeign(['sub_jenis_ujian_id']);
                $table->dropColumn('sub_jenis_ujian_id');
            }
        });
    }
};
