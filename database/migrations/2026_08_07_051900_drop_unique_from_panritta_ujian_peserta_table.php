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
            $table->dropForeign(['ujian_id']);
            $table->dropForeign(['user_id']);
            $table->dropUnique('ujian_peserta_unique');

            $table->foreign('ujian_id')
                ->references('id')
                ->on('panritta_ujian')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('panritta_ujian_peserta', function (Blueprint $table) {
            $table->unique(['ujian_id', 'user_id'], 'ujian_peserta_unique');
        });
    }
};
