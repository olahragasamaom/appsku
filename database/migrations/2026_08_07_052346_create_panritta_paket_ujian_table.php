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
        Schema::create('panritta_paket_ujian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paket_id');
            $table->unsignedBigInteger('ujian_id');
            $table->timestamps();

            $table->unique(['paket_id', 'ujian_id'], 'paket_ujian_unique');

            $table->foreign('paket_id')
                ->references('id')
                ->on('panritta_paket')
                ->cascadeOnDelete();

            $table->foreign('ujian_id')
                ->references('id')
                ->on('panritta_ujian')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panritta_paket_ujian');
    }
};
