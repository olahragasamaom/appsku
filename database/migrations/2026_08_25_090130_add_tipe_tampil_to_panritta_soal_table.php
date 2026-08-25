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
        Schema::table('panritta_soal', function (Blueprint $table) {
            $table->enum('tipe_tampil', ['vertical', 'horizontal'])
                ->default('vertical')
                ->after('pembuat_soal_id')
                ->comment('Tipe tampilan soal: vertical (default) atau horizontal untuk image-only questions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('panritta_soal', function (Blueprint $table) {
            $table->dropColumn('tipe_tampil');
        });
    }
};
