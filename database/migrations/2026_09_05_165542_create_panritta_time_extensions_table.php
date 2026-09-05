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
        Schema::create('panritta_time_extensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_peserta_id')
                ->constrained('panritta_ujian_peserta')
                ->cascadeOnDelete();
            $table->foreignId('peserta_offline_id')
                ->nullable()
                ->constrained('panritta_peserta_offline')
                ->nullOnDelete();
            $table->integer('added_minutes');
            $table->text('reason');
            $table->foreignId('granted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('granted_at');
            $table->timestamps();

            $table->index('ujian_peserta_id', 'te_ujian_peserta_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panritta_time_extensions');
    }
};
