<?php

use App\Models\Ujian;
use App\Models\UjianPeserta;
use App\Models\User;
use App\Services\Ujian\OfflineParticipantService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

function offlineService(): OfflineParticipantService
{
    return app(OfflineParticipantService::class);
}

describe('OfflineParticipantService::create', function () {
    it('rejects creation on a non-offline exam', function () {
        $ujian = Ujian::factory()->online()->create();

        expect(fn () => offlineService()->create($ujian, [
            'nomor_peserta' => 'P-001',
            'nama_peserta' => 'Budi',
        ]))->toThrow(ValidationException::class);
    });

    it('returns the plaintext kode_akses exactly once', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);

        $result = offlineService()->create($ujian, [
            'nomor_peserta' => 'P-001',
            'nama_peserta' => 'Budi',
        ]);

        expect($result['kode_akses'])->toBeString();
        expect(strlen($result['kode_akses']))->toBe(8);
    });

    it('stores a bcrypt hash, not the plaintext', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);

        $result = offlineService()->create($ujian, [
            'nomor_peserta' => 'P-001',
            'nama_peserta' => 'Budi',
        ]);

        $stored = $result['peserta']->makeVisible('kode_akses')->kode_akses;

        expect($stored)->not->toBe($result['kode_akses']);
        expect(Hash::check($result['kode_akses'], $stored))->toBeTrue();
    });

    it('hides kode_akses from the returned model', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);

        $result = offlineService()->create($ujian, [
            'nomor_peserta' => 'P-001',
            'nama_peserta' => 'Budi',
        ]);

        expect($result['peserta']->toArray())->not->toHaveKey('kode_akses');
    });
});

describe('OfflineParticipantService::bulkCreate', function () {
    it('creates multiple participants and returns their plaintext credentials', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);

        $credentials = offlineService()->bulkCreate($ujian, [
            ['nomor_peserta' => 'P-001', 'nama_peserta' => 'Budi'],
            ['nomor_peserta' => 'P-002', 'nama_peserta' => 'Sari'],
        ]);

        expect($credentials)->toHaveCount(2);
        expect($credentials->pluck('nomor_peserta')->all())->toBe(['P-001', 'P-002']);
        expect($credentials->every(fn ($c) => strlen($c['kode_akses']) === 8))->toBeTrue();
    });
});

describe('OfflineParticipantService::blockParticipant', function () {
    it('sets the linked UjianPeserta status to diblokir', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas', 'status' => 'aktif']);
        $user = User::factory()->create();

        $attempt = $ujian->peserta()->create([
            'user_id' => $user->id,
            'status' => 'sedang_ujian',
        ]);

        $pesertaOffline = $ujian->pesertaOffline()->create([
            'nomor_peserta' => 'P-001',
            'nama_peserta' => 'Budi',
            'kode_akses' => bcrypt('rahasia'),
            'ujian_peserta_id' => $attempt->id,
        ]);

        offlineService()->blockParticipant($pesertaOffline);

        expect($attempt->fresh()->status)->toBe('diblokir');
    });

    it('is a no-op when the participant has no linked attempt', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);

        $pesertaOffline = $ujian->pesertaOffline()->create([
            'nomor_peserta' => 'P-001',
            'nama_peserta' => 'Budi',
            'kode_akses' => bcrypt('rahasia'),
            'ujian_peserta_id' => null,
        ]);

        offlineService()->blockParticipant($pesertaOffline);

        expect(UjianPeserta::count())->toBe(0);
    });
});
