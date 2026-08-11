<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'user_level_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('user_level_id')
                ->nullable()
                ->after('is_peserta')
                ->constrained('panritta_user_levels')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_level_id');
        });
    }
};
