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
            $table->integer('attempt_number')->default(1)->comment('Attempt number (1-10) for multiple retakes');
            $table->integer('attempt_seed')->nullable()->comment('Random seed for deterministic shuffling');
            $table->json('question_order')->nullable()->comment('Shuffled question order: {soal_id: position}');
            $table->json('option_mapping')->nullable()->comment('Shuffled option mapping: {soal_id: {a: orig_a, b: orig_c, ...}}');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('panritta_ujian_peserta', function (Blueprint $table) {
            $table->dropColumn(['attempt_number', 'attempt_seed', 'question_order', 'option_mapping']);
        });
    }
};
