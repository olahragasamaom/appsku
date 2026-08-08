<?php

use App\Models\SubJenisUjian;
use App\Models\Ujian;

describe('Ujian subJenisUjian relation', function () {
    it('mass-assigns sub_jenis_ujian_id', function () {
        $subJenis = SubJenisUjian::factory()->create();

        $ujian = Ujian::factory()->create(['sub_jenis_ujian_id' => $subJenis->id]);

        expect($ujian->fresh()->sub_jenis_ujian_id)->toBe($subJenis->id);
    });

    it('belongs to a sub jenis ujian', function () {
        $subJenis = SubJenisUjian::factory()->create();
        $ujian = Ujian::factory()->create(['sub_jenis_ujian_id' => $subJenis->id]);

        expect($ujian->subJenisUjian->id)->toBe($subJenis->id);
    });

    it('has a null sub jenis ujian when not set', function () {
        $ujian = Ujian::factory()->create(['sub_jenis_ujian_id' => null]);

        expect($ujian->subJenisUjian)->toBeNull();
    });
});
