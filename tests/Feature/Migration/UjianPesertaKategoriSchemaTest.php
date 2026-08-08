<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function insertKategoriRow(int $ujianPesertaId, int $jenisUjianId): void
{
    DB::table('panritta_ujian_peserta_kategori')->insert([
        'ujian_peserta_id' => $ujianPesertaId,
        'jenis_ujian_id' => $jenisUjianId,
        'nilai_kategori' => 80.00,
        'passing_grade' => 65.00,
        'lulus_kategori' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function seedUjianPeserta(): int
{
    $ujian = \App\Models\Ujian::factory()->create();
    $user = \App\Models\User::factory()->create();

    return DB::table('panritta_ujian_peserta')->insertGetId([
        'ujian_id' => $ujian->id,
        'user_id' => $user->id,
        'status' => 'selesai',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

describe('panritta_ujian_peserta_kategori schema', function () {
    it('creates the table with the expected columns', function () {
        expect(Schema::hasTable('panritta_ujian_peserta_kategori'))->toBeTrue();

        foreach (['ujian_peserta_id', 'jenis_ujian_id', 'nilai_kategori', 'passing_grade', 'lulus_kategori'] as $column) {
            expect(Schema::hasColumn('panritta_ujian_peserta_kategori', $column))->toBeTrue();
        }
    });

    it('enforces a unique constraint on ujian_peserta_id and jenis_ujian_id', function () {
        $pesertaId = seedUjianPeserta();
        $jenis = \App\Models\JenisUjian::factory()->create();

        insertKategoriRow($pesertaId, $jenis->id);

        expect(fn () => insertKategoriRow($pesertaId, $jenis->id))->toThrow(QueryException::class);
    });

    it('cascades on ujian_peserta deletion', function () {
        $pesertaId = seedUjianPeserta();
        $jenis = \App\Models\JenisUjian::factory()->create();

        insertKategoriRow($pesertaId, $jenis->id);

        DB::table('panritta_ujian_peserta')->where('id', $pesertaId)->delete();

        expect(DB::table('panritta_ujian_peserta_kategori')->where('ujian_peserta_id', $pesertaId)->count())->toBe(0);
    });

    it('defaults nilai_kategori to 0 and allows nullable passing_grade and lulus_kategori', function () {
        $pesertaId = seedUjianPeserta();
        $jenis = \App\Models\JenisUjian::factory()->create();

        $id = DB::table('panritta_ujian_peserta_kategori')->insertGetId([
            'ujian_peserta_id' => $pesertaId,
            'jenis_ujian_id' => $jenis->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = DB::table('panritta_ujian_peserta_kategori')->where('id', $id)->first();

        expect((float) $row->nilai_kategori)->toBe(0.0);
        expect($row->passing_grade)->toBeNull();
        expect($row->lulus_kategori)->toBeNull();
    });
});
