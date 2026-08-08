<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function insertPesertaOffline(int $ujianId, string $nomor): void
{
    DB::table('panritta_peserta_offline')->insert([
        'ujian_id' => $ujianId,
        'nomor_peserta' => $nomor,
        'nama_peserta' => 'Peserta '.$nomor,
        'kode_akses' => bcrypt('rahasia'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

describe('panritta_peserta_offline schema', function () {
    it('creates the table with the expected columns', function () {
        expect(Schema::hasTable('panritta_peserta_offline'))->toBeTrue();

        foreach (['ujian_id', 'nomor_peserta', 'nama_peserta', 'kode_akses', 'ujian_peserta_id'] as $column) {
            expect(Schema::hasColumn('panritta_peserta_offline', $column))->toBeTrue();
        }
    });

    it('enforces a unique nomor_peserta per ujian', function () {
        $ujian = \App\Models\Ujian::factory()->create();

        insertPesertaOffline($ujian->id, 'A-001');

        expect(fn () => insertPesertaOffline($ujian->id, 'A-001'))->toThrow(QueryException::class);
    });

    it('allows the same nomor_peserta on a different ujian', function () {
        $ujianA = \App\Models\Ujian::factory()->create();
        $ujianB = \App\Models\Ujian::factory()->create();

        insertPesertaOffline($ujianA->id, 'A-001');
        insertPesertaOffline($ujianB->id, 'A-001');

        expect(DB::table('panritta_peserta_offline')->where('nomor_peserta', 'A-001')->count())->toBe(2);
    });

    it('cascades on ujian deletion', function () {
        $ujian = \App\Models\Ujian::factory()->create();

        insertPesertaOffline($ujian->id, 'A-001');

        $ujian->delete();

        expect(DB::table('panritta_peserta_offline')->where('nomor_peserta', 'A-001')->count())->toBe(0);
    });

    it('allows a nullable ujian_peserta_id', function () {
        $ujian = \App\Models\Ujian::factory()->create();

        $id = DB::table('panritta_peserta_offline')->insertGetId([
            'ujian_id' => $ujian->id,
            'nomor_peserta' => 'A-001',
            'nama_peserta' => 'Peserta A-001',
            'kode_akses' => bcrypt('rahasia'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        expect(DB::table('panritta_peserta_offline')->where('id', $id)->value('ujian_peserta_id'))->toBeNull();
    });
});
