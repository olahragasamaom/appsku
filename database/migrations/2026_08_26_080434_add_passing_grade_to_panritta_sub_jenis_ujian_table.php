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
        Schema::table('panritta_sub_jenis_ujian', function (Blueprint $table) {
            $table->decimal('passing_grade', 6, 2)->nullable()->after('nilai_benar')->comment('Default passing grade untuk sub jenis ujian ini');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('panritta_sub_jenis_ujian', function (Blueprint $table) {
            $table->dropColumn('passing_grade');
        });
    }
};
