<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('panritta_level_module');

        if (! Schema::hasColumn('panritta_user_levels', 'role_id')) {
            Schema::table('panritta_user_levels', function (Blueprint $table) {
                $table->foreignId('role_id')
                    ->nullable()
                    ->after('slug')
                    ->constrained('roles')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('panritta_user_levels', 'role_id')) {
            Schema::table('panritta_user_levels', function (Blueprint $table) {
                $table->dropConstrainedForeignId('role_id');
            });
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
};
