<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $legacyRows = collect();

        if (Schema::hasTable('panritta_sub_jenis_ujian')) {
            $legacyRows = DB::table('panritta_sub_jenis_ujian')->get();
            Schema::drop('panritta_sub_jenis_ujian');
        }

        Schema::create('panritta_sub_jenis_ujian', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('jenis_ujian_id');
            $table->string('nama_sub_jenis_ujian', 255);
            $table->enum('sistem_penilaian', ['benar_salah', 'tiap_jawaban_ada_poin'])->default('benar_salah');
            $table->smallInteger('jumlah_jawaban_pilihan_ganda')->default(5);
            $table->decimal('nilai_benar', 5, 2)->default(5.00);
            $table->timestamps();

            $table->foreign('jenis_ujian_id')
                ->references('id')
                ->on('panritta_jenis_ujian')
                ->cascadeOnDelete();
        });

        foreach ($legacyRows as $row) {
            $jenisUjianId = $row->jenis_ujian ?? $row->jenis_ujian_id ?? null;

            if (! $jenisUjianId || ! DB::table('panritta_jenis_ujian')->where('id', $jenisUjianId)->exists()) {
                continue;
            }

            $sistem = str_replace('-', '_', (string) ($row->sistem_penilaian ?? 'benar_salah'));

            DB::table('panritta_sub_jenis_ujian')->insert([
                'id' => $row->id,
                'jenis_ujian_id' => $jenisUjianId,
                'nama_sub_jenis_ujian' => $row->nama_sub_jenis_ujian ?? 'Tanpa Nama',
                'sistem_penilaian' => in_array($sistem, ['benar_salah', 'tiap_jawaban_ada_poin'], true) ? $sistem : 'benar_salah',
                'jumlah_jawaban_pilihan_ganda' => (int) ($row->jumlah_jawaban_pilihan_ganda ?? 5),
                'nilai_benar' => (float) ($row->nilai_jawaban_benar ?? $row->nilai_benar ?? 5),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panritta_sub_jenis_ujian');
    }
};
