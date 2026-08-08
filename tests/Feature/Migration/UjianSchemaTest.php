<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

describe('panritta_ujian schema', function () {
    it('has a sub_jenis_ujian_id column', function () {
        expect(Schema::hasColumn('panritta_ujian', 'sub_jenis_ujian_id'))->toBeTrue();
    });

    it('stores a sub_jenis_ujian_id foreign key value', function () {
        $subJenis = \App\Models\SubJenisUjian::factory()->create();

        $ujian = \App\Models\Ujian::factory()->create();
        $ujian->forceFill(['sub_jenis_ujian_id' => $subJenis->id])->save();

        $stored = DB::table('panritta_ujian')->where('id', $ujian->id)->value('sub_jenis_ujian_id');

        expect($stored)->toBe($subJenis->id);
    });

    it('allows sub_jenis_ujian_id to be null', function () {
        $ujian = \App\Models\Ujian::factory()->create();

        $stored = DB::table('panritta_ujian')->where('id', $ujian->id)->value('sub_jenis_ujian_id');

        expect($stored)->toBeNull();
    });
});

describe('panritta_ujian_peserta schema', function () {
    it('allows re-takes by permitting duplicate ujian_id and user_id (AD-9)', function () {
        $ujian = \App\Models\Ujian::factory()->create();
        $user = \App\Models\User::factory()->create();

        $insert = fn (): int => DB::table('panritta_ujian_peserta')->insertGetId([
            'ujian_id' => $ujian->id,
            'user_id' => $user->id,
            'status' => 'selesai',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $first = $insert();
        $second = $insert();

        expect($first)->not->toBe($second);

        $count = DB::table('panritta_ujian_peserta')
            ->where('ujian_id', $ujian->id)
            ->where('user_id', $user->id)
            ->count();

        expect($count)->toBe(2);
    });

    it('has the attempt lifecycle columns (AD-10)', function () {
        expect(Schema::hasColumn('panritta_ujian_peserta', 'langganan_id'))->toBeTrue();
        expect(Schema::hasColumn('panritta_ujian_peserta', 'batas_waktu'))->toBeTrue();
        expect(Schema::hasColumn('panritta_ujian_peserta', 'auto_submitted'))->toBeTrue();
    });

    it('defaults auto_submitted to false and allows nullable lifecycle fields', function () {
        $ujian = \App\Models\Ujian::factory()->create();
        $user = \App\Models\User::factory()->create();

        $id = DB::table('panritta_ujian_peserta')->insertGetId([
            'ujian_id' => $ujian->id,
            'user_id' => $user->id,
            'status' => 'sedang_ujian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = DB::table('panritta_ujian_peserta')->where('id', $id)->first();

        expect($row->langganan_id)->toBeNull();
        expect($row->batas_waktu)->toBeNull();
        expect((int) $row->auto_submitted)->toBe(0);
    });
});
