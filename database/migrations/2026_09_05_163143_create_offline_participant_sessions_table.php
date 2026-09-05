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
        Schema::create('offline_participant_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peserta_offline_id')
                ->constrained('panritta_peserta_offline')
                ->cascadeOnDelete();
            $table->foreignId('ujian_id')
                ->nullable()
                ->constrained('panritta_ujian')
                ->cascadeOnDelete();
            $table->string('session_token', 64)->unique();
            $table->enum('status', ['logged_in', 'sedang_ujian', 'selesai', 'logout'])
                ->default('logged_in');
            $table->json('device_info')->nullable();
            $table->timestamp('login_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('logout_at')->nullable();
            $table->string('logout_reason', 255)->nullable();
            $table->timestamps();

            $table->index(['peserta_offline_id', 'ujian_id'], 'ops_peserta_ujian_idx');
            $table->index('status', 'ops_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_participant_sessions');
    }
};
