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
        Schema::create('panritta_paket', function (Blueprint $table) {
            $table->id();
            $table->string('nama_paket', 100);
            $table->string('slug', 120)->unique();
            $table->text('deskripsi')->nullable();
            $table->decimal('harga', 12, 2)->default(0);
            $table->unsignedInteger('durasi_hari')->default(30);
            $table->unsignedInteger('kuota_ujian')->nullable();
            $table->boolean('video_pembahasan')->default(false);
            $table->boolean('analitik')->default(false);
            $table->boolean('sertifikat')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panritta_paket');
    }
};
