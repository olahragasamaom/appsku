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
        Schema::create('panritta_ujian_jenis_ujian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ujian_id');
            $table->integer('jenis_ujian_id');
            $table->decimal('passing_grade', 6, 2)->nullable();
            $table->timestamps();

            $table->unique(['ujian_id', 'jenis_ujian_id'], 'ujian_jenis_ujian_unique');

            $table->foreign('ujian_id')
                ->references('id')
                ->on('panritta_ujian')
                ->cascadeOnDelete();

            $table->foreign('jenis_ujian_id')
                ->references('id')
                ->on('panritta_jenis_ujian')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panritta_ujian_jenis_ujian');
    }
};
