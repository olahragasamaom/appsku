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
        Schema::create('panritta_peserta_langganan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('paket_id');
            $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('pending');
            $table->dateTime('mulai_pada')->nullable();
            $table->dateTime('berakhir_pada')->nullable();
            $table->unsignedInteger('sisa_kuota_ujian')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('paket_id')
                ->references('id')
                ->on('panritta_paket')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panritta_peserta_langganan');
    }
};
