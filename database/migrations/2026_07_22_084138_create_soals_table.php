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
        Schema::create('panritta_soal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sub_indikator_id');
            $table->longText('soal');
            $table->string('gambar_soal', 255)->nullable();
            $table->text('opsi_a');
            $table->text('opsi_b');
            $table->text('opsi_c');
            $table->text('opsi_d');
            $table->text('opsi_e')->nullable();
            $table->string('gambar_opsi_a', 255)->nullable();
            $table->string('gambar_opsi_b', 255)->nullable();
            $table->string('gambar_opsi_c', 255)->nullable();
            $table->string('gambar_opsi_d', 255)->nullable();
            $table->string('gambar_opsi_e', 255)->nullable();
            $table->enum('kunci_jawaban', ['A', 'B', 'C', 'D', 'E'])->nullable();
            $table->decimal('nilai_bobot_benar', 5, 2)->nullable();
            $table->decimal('nilai_bobot_a', 5, 2)->nullable();
            $table->decimal('nilai_bobot_b', 5, 2)->nullable();
            $table->decimal('nilai_bobot_c', 5, 2)->nullable();
            $table->decimal('nilai_bobot_d', 5, 2)->nullable();
            $table->decimal('nilai_bobot_e', 5, 2)->nullable();
            $table->longText('pembahasan')->nullable();
            $table->string('gambar_pembahasan', 255)->nullable();
            $table->unsignedBigInteger('pembuat_soal_id');
            $table->timestamps();

            $table->foreign('sub_indikator_id')
                ->references('id')
                ->on('panritta_sub_indikator')
                ->cascadeOnDelete();

            $table->foreign('pembuat_soal_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panritta_soal');
    }
};
