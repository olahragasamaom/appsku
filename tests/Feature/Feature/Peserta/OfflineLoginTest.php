<?php

use App\Models\Ujian;
use App\Services\Ujian\OfflineParticipantService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Offline participant login flow (is_active based)', function () {
    beforeEach(function () {
        $this->service = new OfflineParticipantService;
    });

    describe('login', function () {
        it('authenticates with correct credentials and sets offline_peserta_id session', function () {
            $ujian = Ujian::factory()->offline()->create();

            $result = $this->service->create($ujian, [
                'nomor_peserta' => 'P001',
                'nama_peserta' => 'John Doe',
            ]);

            $peserta = $result['peserta'];
            $plaintext = $result['kode_akses'];

            $response = $this->post('/peserta/ujian-offline/login', [
                'nomor_peserta' => 'P001',
                'kode_akses' => $plaintext,
            ]);

            expect($response->status())->toBe(302);
            expect(session('offline_peserta_id'))->toBe($peserta->id);
            $response->assertRedirectContains('/offline/daftar');
        });

        it('rejects with wrong kode_akses', function () {
            $ujian = Ujian::factory()->offline()->create();

            $this->service->create($ujian, [
                'nomor_peserta' => 'P001',
                'nama_peserta' => 'John Doe',
            ]);

            $response = $this->post('/peserta/ujian-offline/login', [
                'nomor_peserta' => 'P001',
                'kode_akses' => 'WRONGCODE',
            ]);

            expect($response->status())->toBe(302);
            expect(session('offline_peserta_id'))->toBeNull();
        });

        it('rejects with nonexistent nomor_peserta', function () {
            $response = $this->post('/peserta/ujian-offline/login', [
                'nomor_peserta' => 'INVALID',
                'kode_akses' => 'ANYCODE',
            ]);

            expect($response->status())->toBe(302);
            expect(session('offline_peserta_id'))->toBeNull();
        });
    });

    describe('exam list', function () {
        it('shows offline exams after login', function () {
            $ujian = Ujian::factory()->offline()->active()->create();

            $result = $this->service->create($ujian, [
                'nomor_peserta' => 'P001',
                'nama_peserta' => 'John Doe',
            ]);

            $plaintext = $result['kode_akses'];

            $this->post('/peserta/ujian-offline/login', [
                'nomor_peserta' => 'P001',
                'kode_akses' => $plaintext,
            ]);

            $response = $this->get('/peserta/offline/daftar');

            $response->assertStatus(200);
            $response->assertViewHas('ujians');
        });
    });

    describe('start exam', function () {
        it('allows start when ujian aktif AND peserta is_active=true', function () {
            // Ujian aktif, sudah dimulai (1 jam lalu), belum lewat batas keterlambatan
            $ujian = Ujian::factory()->offline()->active()->create([
                'tanggal_ujian' => now()->subHour(),
                'batas_keterlambatan' => now()->addDays(7),
            ]);

            $result = $this->service->create($ujian, [
                'nomor_peserta' => 'P001',
                'nama_peserta' => 'John Doe',
            ]);

            $peserta = $result['peserta'];
            $plaintext = $result['kode_akses'];

            // Peserta dibuat dengan is_active = true (default dari service)
            expect($peserta->is_active)->toBeTrue();

            $this->post('/peserta/ujian-offline/login', [
                'nomor_peserta' => 'P001',
                'kode_akses' => $plaintext,
            ]);

            $response = $this->post("/peserta/ujian/{$ujian->id}/offline/mulai");

            expect($response->status())->toBe(302);
            expect(session('offline_ujian_id'))->toBe($ujian->id);
            expect(session('offline_attempt_id'))->not->toBeNull();
        });

        it('rejects start if peserta is_active=false', function () {
            $ujian = Ujian::factory()->offline()->active()->create();

            $result = $this->service->create($ujian, [
                'nomor_peserta' => 'P001',
                'nama_peserta' => 'John Doe',
            ]);

            $peserta = $result['peserta'];
            $plaintext = $result['kode_akses'];

            // Nonaktifkan peserta
            $peserta->update(['is_active' => false]);

            $this->post('/peserta/ujian-offline/login', [
                'nomor_peserta' => 'P001',
                'kode_akses' => $plaintext,
            ]);

            $response = $this->post("/peserta/ujian/{$ujian->id}/offline/mulai");

            expect($response->status())->toBe(403);
            expect(session('offline_ujian_id'))->toBeNull();
        });

        it('rejects start if ujian not aktif', function () {
            $ujian = Ujian::factory()->offline()->draft()->create();

            $result = $this->service->create($ujian, [
                'nomor_peserta' => 'P001',
                'nama_peserta' => 'John Doe',
            ]);

            $plaintext = $result['kode_akses'];

            $this->post('/peserta/ujian-offline/login', [
                'nomor_peserta' => 'P001',
                'kode_akses' => $plaintext,
            ]);

            $response = $this->post("/peserta/ujian/{$ujian->id}/offline/mulai");

            expect($response->status())->toBe(403);
            expect(session('offline_ujian_id'))->toBeNull();
        });
    });
});
