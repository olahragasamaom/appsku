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
        Schema::table('panritta_ujian', function (Blueprint $table) {
            $table->unsignedBigInteger('user_updated_by')->nullable()->after('dibuat_oleh');
            $table->foreign('user_updated_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::table('panritta_soal', function (Blueprint $table) {
            $table->unsignedBigInteger('user_updated_by')->nullable()->after('pembuat_soal_id');
            $table->foreign('user_updated_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('panritta_soal', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\User::class, 'user_updated_by');
            $table->dropColumn('user_updated_by');
        });

        Schema::table('panritta_ujian', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\User::class, 'user_updated_by');
            $table->dropColumn('user_updated_by');
        });
    }
};
