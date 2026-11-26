<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('panritta_peserta_offline', function (Blueprint $table) {
            if (! Schema::hasColumn('panritta_peserta_offline', 'nama_kelas')) {
                $table->string('nama_kelas', 255)->nullable()->after('nama_peserta');
            }
            if (! Schema::hasColumn('panritta_peserta_offline', 'nomor_hp')) {
                $table->string('nomor_hp', 50)->nullable()->after('nama_kelas');
            }
        });

        if (! Schema::hasTable('panritta_ujian_peserta_offline')) {
            Schema::create('panritta_ujian_peserta_offline', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ujian_id')->constrained('panritta_ujian')->cascadeOnDelete();
                $table->foreignId('peserta_offline_id')->constrained('panritta_peserta_offline')->cascadeOnDelete();
                $table->boolean('is_active')->default(false);
                $table->foreignId('ujian_peserta_id')->nullable()->constrained('panritta_ujian_peserta')->nullOnDelete();
                $table->timestamps();

                $table->unique(['ujian_id', 'peserta_offline_id'], 'ujian_peserta_offline_unique');
            });

            // Migrate existing links
            $existing = DB::table('panritta_peserta_offline')->whereNotNull('ujian_id')->get();
            foreach ($existing as $row) {
                DB::table('panritta_ujian_peserta_offline')->insertOrIgnore([
                    'ujian_id' => $row->ujian_id,
                    'peserta_offline_id' => $row->id,
                    'is_active' => (bool) ($row->is_active ?? false),
                    'ujian_peserta_id' => $row->ujian_peserta_id ?? null,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('panritta_ujian_peserta_offline');

        Schema::table('panritta_peserta_offline', function (Blueprint $table) {
            if (Schema::hasColumn('panritta_peserta_offline', 'nama_kelas')) {
                $table->dropColumn('nama_kelas');
            }
            if (Schema::hasColumn('panritta_peserta_offline', 'nomor_hp')) {
                $table->dropColumn('nomor_hp');
            }
        });
    }
};
