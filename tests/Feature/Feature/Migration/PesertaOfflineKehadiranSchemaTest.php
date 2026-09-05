<?php

use Illuminate\Support\Facades\Schema;

describe('panritta_peserta_offline_kehadiran schema', function () {
    it('has all required columns', function () {
        expect(Schema::hasColumns('panritta_peserta_offline_kehadiran', [
            'id',
            'peserta_offline_id',
            'ujian_id',
            'status_kehadiran',
            'created_at',
            'updated_at',
        ]))->toBeTrue();
    });

    it('has correct column types', function () {
        $columns = Schema::getColumns('panritta_peserta_offline_kehadiran');

        $columnMap = collect($columns)->keyBy('name');

        expect($columnMap['peserta_offline_id']['type'])->toBe('integer');
        expect($columnMap['ujian_id']['type'])->toBe('integer');
        expect($columnMap['status_kehadiran']['type'])->toBe('varchar');
    });

    it('enforces unique constraint on peserta_offline_id and ujian_id', function () {
        $indexes = Schema::getIndexes('panritta_peserta_offline_kehadiran');

        $uniqueIndexes = collect($indexes)->filter(fn ($index) => $index['unique']);

        expect($uniqueIndexes->pluck('columns')->flatten()->all())->toContain('peserta_offline_id', 'ujian_id');
    });

    it('has default status_kehadiran of tidak_hadir', function () {
        $columns = Schema::getColumns('panritta_peserta_offline_kehadiran');
        $statusColumn = collect($columns)->firstWhere('name', 'status_kehadiran');

        expect($statusColumn['default'])->toContain('tidak_hadir');
    });
});
