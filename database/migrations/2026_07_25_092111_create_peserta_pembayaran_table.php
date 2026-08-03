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
        Schema::create('panritta_peserta_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('paket_id');
            $table->unsignedBigInteger('langganan_id')->nullable();
            $table->string('nomor_pembayaran', 50)->unique();
            $table->string('gateway', 50)->nullable();
            $table->string('gateway_reference', 191)->nullable();
            $table->string('invoice_url', 500)->nullable();
            $table->decimal('jumlah', 12, 2);
            $table->enum('status', ['pending', 'success', 'failed', 'expired'])->default('pending');
            $table->string('metode_pembayaran', 50)->nullable();
            $table->json('gateway_response')->nullable();
            $table->dateTime('dibayar_pada')->nullable();
            $table->dateTime('kedaluwarsa_pada')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('paket_id')
                ->references('id')
                ->on('panritta_paket')
                ->cascadeOnDelete();

            $table->foreign('langganan_id')
                ->references('id')
                ->on('panritta_peserta_langganan')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panritta_peserta_pembayaran');
    }
};
