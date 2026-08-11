<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('panritta_level_module')) {
            return;
        }

        Schema::create('panritta_level_module', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_level_id')
                ->constrained('panritta_user_levels')
                ->cascadeOnDelete();
            $table->foreignId('module_id')
                ->constrained('panritta_modules')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_level_id', 'module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panritta_level_module');
    }
};
