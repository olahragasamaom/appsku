<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('panritta_user_levels')) {
            return;
        }

        Schema::create('panritta_user_levels', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('slug', 120)->unique();
            $table->string('keterangan', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panritta_user_levels');
    }
};
