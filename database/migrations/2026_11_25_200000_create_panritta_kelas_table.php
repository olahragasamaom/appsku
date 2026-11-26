<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('panritta_kelas')) {
            Schema::create('panritta_kelas', function (Blueprint $table) {
                $table->id();
                $table->string('nama_kelas', 255)->unique();
                $table->text('deskripsi')->nullable();
                $table->string('status', 20)->default('aktif');
                $table->timestamps();
            });
        }

        Schema::table('panritta_peserta_offline', function (Blueprint $table) {
            if (! Schema::hasColumn('panritta_peserta_offline', 'kelas_id')) {
                $table->foreignId('kelas_id')->nullable()->after('nama_peserta')->constrained('panritta_kelas')->nullOnDelete();
            }
        });

        // Migrate existing distinct nama_kelas from panritta_peserta_offline into panritta_kelas
        $distinctKelas = DB::table('panritta_peserta_offline')
            ->whereNotNull('nama_kelas')
            ->where('nama_kelas', '!=', '')
            ->distinct()
            ->pluck('nama_kelas');

        foreach ($distinctKelas as $namaKelas) {
            $existingId = DB::table('panritta_kelas')->where('nama_kelas', $namaKelas)->value('id');
            if (! $existingId) {
                $existingId = DB::table('panritta_kelas')->insertGetId([
                    'nama_kelas' => $namaKelas,
                    'status' => 'aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('panritta_peserta_offline')
                ->where('nama_kelas', $namaKelas)
                ->update(['kelas_id' => $existingId]);
        }
    }

    public function down(): void
    {
        Schema::table('panritta_peserta_offline', function (Blueprint $table) {
            if (Schema::hasColumn('panritta_peserta_offline', 'kelas_id')) {
                $table->dropForeign(['kelas_id']);
                $table->dropColumn('kelas_id');
            }
        });

        Schema::dropIfExists('panritta_kelas');
    }
};
