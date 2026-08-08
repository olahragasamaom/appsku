<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

describe('panritta_paket_ujian schema', function () {
    it('creates the pivot table with paket_id and ujian_id columns', function () {
        expect(Schema::hasTable('panritta_paket_ujian'))->toBeTrue();
        expect(Schema::hasColumn('panritta_paket_ujian', 'paket_id'))->toBeTrue();
        expect(Schema::hasColumn('panritta_paket_ujian', 'ujian_id'))->toBeTrue();
    });

    it('enforces a unique constraint on paket_id and ujian_id', function () {
        $paket = \App\Models\Paket::factory()->create();
        $ujian = \App\Models\Ujian::factory()->create();

        $insert = fn () => DB::table('panritta_paket_ujian')->insert([
            'paket_id' => $paket->id,
            'ujian_id' => $ujian->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $insert();

        expect($insert)->toThrow(QueryException::class);
    });

    it('cascades on paket deletion', function () {
        $paket = \App\Models\Paket::factory()->create();
        $ujian = \App\Models\Ujian::factory()->create();

        DB::table('panritta_paket_ujian')->insert([
            'paket_id' => $paket->id,
            'ujian_id' => $ujian->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $paket->delete();

        expect(DB::table('panritta_paket_ujian')->where('ujian_id', $ujian->id)->count())->toBe(0);
    });

    it('cascades on ujian deletion', function () {
        $paket = \App\Models\Paket::factory()->create();
        $ujian = \App\Models\Ujian::factory()->create();

        DB::table('panritta_paket_ujian')->insert([
            'paket_id' => $paket->id,
            'ujian_id' => $ujian->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $ujian->delete();

        expect(DB::table('panritta_paket_ujian')->where('paket_id', $paket->id)->count())->toBe(0);
    });
});
