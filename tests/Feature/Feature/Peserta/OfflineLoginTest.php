<?php

use App\Models\Ujian;
use App\Services\Ujian\OfflineParticipantService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Offline participant login flow (P5-T6)', function () {
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

            // ValidationException is caught and returns 302 redirect
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
        it('shows all offline exams after login', function () {
            $ujian1 = Ujian::factory()->offline()->active()->create();
            $ujian2 = Ujian::factory()->offline()->draft()->create();

            $result = $this->service->create($ujian1, [
                'nomor_peserta' => 'P001',
                'nama_peserta' => 'John Doe',
            ]);

            $peserta = $result['peserta'];
            $plaintext = $result['kode_akses'];

            $this->post('/peserta/ujian-offline/login', [
                'nomor_peserta' => 'P001',
                'kode_akses' => $plaintext,
            ]);

            $response = $this->get('/peserta/offline/daftar');

            $response->assertStatus(200);
            $response->assertViewHas('ujians');
        });

        it('shows attendance status (hadir/tidak_hadir) for each exam', function () {
            $ujian = Ujian::factory()->offline()->active()->create();

            $result = $this->service->create($ujian, [
                'nomor_peserta' => 'P001',
                'nama_peserta' => 'John Doe',
            ]);

            $peserta = $result['peserta'];
            $plaintext = $result['kode_akses'];

            $this->post('/peserta/ujian-offline/login', [
                'nomor_peserta' => 'P001',
                'kode_akses' => $plaintext,
            ]);

            $response = $this->get('/peserta/offline/daftar');

            $response->assertViewHas('ujians');
            $ujians = $response->viewData('ujians');
            // kehadiran adalah collection via whereHas eager loading
            expect($ujians->count())->toBeGreaterThan(0);
            $firstUjian = $ujians->first();
            expect($firstUjian->pesertaOfflineKehadiran)->not->toBeNull();
            expect($firstUjian->pesertaOfflineKehadiran->count())->toBeGreaterThan(0);
        });
    });

    describe('start exam', function () {
        it('button is enabled when ujian aktif AND kehadiran hadir', function () {
            $ujian = Ujian::factory()->offline()->active()->create();

            $result = $this->service->create($ujian, [
                'nomor_peserta' => 'P001',
                'nama_peserta' => 'John Doe',
            ]);

            $peserta = $result['peserta'];
            $plaintext = $result['kode_akses'];

            $this->service->markAttendance($peserta, $ujian, 'hadir');

            $this->post('/peserta/ujian-offline/login', [
                'nomor_peserta' => 'P001',
                'kode_akses' => $plaintext,
            ]);

            $response = $this->get('/peserta/offline/daftar');

            $response->assertStatus(200);
        });

        it('creates attempt and sets session keys when starting', function () {
            $ujian = Ujian::factory()->offline()->active()->create();

            $result = $this->service->create($ujian, [
                'nomor_peserta' => 'P001',
                'nama_peserta' => 'John Doe',
            ]);

            $peserta = $result['peserta'];
            $plaintext = $result['kode_akses'];

            $this->service->markAttendance($peserta, $ujian, 'hadir');

            $this->post('/peserta/ujian-offline/login', [
                'nomor_peserta' => 'P001',
                'kode_akses' => $plaintext,
            ]);

            $response = $this->post("/peserta/ujian/{$ujian->id}/offline/mulai");

            expect($response->status())->toBe(302);
            expect(session('offline_ujian_id'))->toBe($ujian->id);
            expect(session('offline_attempt_id'))->not->toBeNull();
        });

        it('rejects start if peserta tidak_hadir', function () {
            $ujian = Ujian::factory()->offline()->active()->create();

            $result = $this->service->create($ujian, [
                'nomor_peserta' => 'P001',
                'nama_peserta' => 'John Doe',
            ]);

            $peserta = $result['peserta'];
            $plaintext = $result['kode_akses'];

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

            $peserta = $result['peserta'];
            $plaintext = $result['kode_akses'];

            $this->service->markAttendance($peserta, $ujian, 'hadir');

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
