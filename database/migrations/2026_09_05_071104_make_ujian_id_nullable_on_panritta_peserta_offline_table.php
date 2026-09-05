<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('panritta_peserta_offline', function (Blueprint $table) {
            $table->foreignId('ujian_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('panritta_peserta_offline', function (Blueprint $table) {
            $table->foreignId('ujian_id')->nullable(false)->change();
        });
    }
};
