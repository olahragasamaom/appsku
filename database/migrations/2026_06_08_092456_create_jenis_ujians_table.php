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
        if (Schema::hasTable('panritta_jenis_ujian')) {
            return;
        }

        Schema::create('panritta_jenis_ujian', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama_jenis_ujian', 100);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panritta_jenis_ujian');
    }
};
