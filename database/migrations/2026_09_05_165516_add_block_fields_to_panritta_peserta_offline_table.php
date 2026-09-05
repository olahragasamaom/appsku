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
        Schema::table('panritta_peserta_offline', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false)->after('ujian_peserta_id');
            $table->timestamp('blocked_at')->nullable()->after('is_blocked');
            $table->text('blocked_reason')->nullable()->after('blocked_at');
        });
    }

    public function down(): void
    {
        Schema::table('panritta_peserta_offline', function (Blueprint $table) {
            $table->dropColumn(['is_blocked', 'blocked_at', 'blocked_reason']);
        });
    }
};
