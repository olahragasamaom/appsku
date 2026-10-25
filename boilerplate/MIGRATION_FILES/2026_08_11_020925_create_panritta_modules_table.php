<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('panritta_modules')) {
            return;
        }

        Schema::create('panritta_modules', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('label', 150);
            $table->string('route_name', 150)->nullable();
            $table->string('route_pattern', 150)->nullable();
            $table->string('icon', 5000)->nullable();
            $table->string('grup', 100)->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panritta_modules');
    }
};
