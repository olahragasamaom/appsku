<?php

use App\Models\PesertaOffline;
use App\Models\PesertaOfflineKehadiran;
use App\Models\Ujian;
use App\Services\Ujian\OfflineParticipantService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('OfflineParticipantService', function () {
    beforeEach(function () {
        $this->service = new OfflineParticipantService;
    });

    describe('create', function () {
        it('creates offline participant and auto-initializes kehadiran', function () {
            $ujian = Ujian::factory()->offline()->create();

            $result = $this->service->create($ujian, [
                'nomor_peserta' => 'P001',
                'nama_peserta' => 'John Doe',
            ]);

            expect($result['peserta']->nomor_peserta)->toBe('P001');
            expect($result['peserta']->nama_peserta)->toBe('John Doe');
            expect($result['kode_akses'])->toHaveLength(8);

            $kehadiran = PesertaOfflineKehadiran::where('peserta_offline_id', $result['peserta']->id)->first();
            expect($kehadiran)->not->toBeNull();
            expect($kehadiran->status_kehadiran)->toBe('tidak_hadir');
        });

        it('rejects creation on online exam', function () {
            $ujian = Ujian::factory()->online()->create();

            expect(function () {
                $this->service->create($ujian, [
                    'nomor_peserta' => 'P001',
                    'nama_peserta' => 'John Doe',
                ]);
            })->toThrow(Exception::class);
        });

        it('returns plaintext kode_akses', function () {
            $ujian = Ujian::factory()->offline()->create();

            $result = $this->service->create($ujian, [
                'nomor_peserta' => 'P001',
                'nama_peserta' => 'John Doe',
            ]);

            expect($result['kode_akses'])->toBeString();
            expect($result['kode_akses'])->not->toContain('$2y$');
        });

        it('hashes kode_akses in database', function () {
            $ujian = Ujian::factory()->offline()->create();

            $result = $this->service->create($ujian, [
                'nomor_peserta' => 'P001',
                'nama_peserta' => 'John Doe',
            ]);

            $peserta = PesertaOffline::find($result['peserta']->id);
            expect($peserta->kode_akses)->toContain('$2y$');
        });
    });

    describe('bulkCreate', function () {
        it('creates multiple participants with auto-kehadiran', function () {
            $ujian = Ujian::factory()->offline()->create();

            $result = $this->service->bulkCreate($ujian, [
                ['nomor_peserta' => 'P001', 'nama_peserta' => 'John Doe'],
                ['nomor_peserta' => 'P002', 'nama_peserta' => 'Jane Doe'],
            ]);

            expect($result)->toHaveCount(2);
            expect($result->pluck('nomor_peserta')->all())->toContain('P001', 'P002');

            $kehadiranCount = PesertaOfflineKehadiran::where('ujian_id', $ujian->id)->count();
            expect($kehadiranCount)->toBe(2);
        });
    });

    describe('markAttendance', function () {
        it('updates attendance status to hadir', function () {
            $ujian = Ujian::factory()->offline()->create();
            $result = $this->service->create($ujian, [
                'nomor_peserta' => 'P001',
                'nama_peserta' => 'John Doe',
            ]);

            $peserta = $result['peserta'];

            $this->service->markAttendance($peserta, $ujian, 'hadir');

            $kehadiran = PesertaOfflineKehadiran::where('peserta_offline_id', $peserta->id)->first();
            expect($kehadiran->status_kehadiran)->toBe('hadir');
        });

        it('toggles attendance from hadir to tidak_hadir', function () {
            $ujian = Ujian::factory()->offline()->create();
            $result = $this->service->create($ujian, [
                'nomor_peserta' => 'P001',
                'nama_peserta' => 'John Doe',
            ]);

            $peserta = $result['peserta'];

            $this->service->markAttendance($peserta, $ujian, 'hadir');
            expect(PesertaOfflineKehadiran::where('peserta_offline_id', $peserta->id)->first()->status_kehadiran)->toBe('hadir');

            $this->service->markAttendance($peserta, $ujian, 'tidak_hadir');
            expect(PesertaOfflineKehadiran::where('peserta_offline_id', $peserta->id)->first()->status_kehadiran)->toBe('tidak_hadir');
        });

        it('is idempotent', function () {
            $ujian = Ujian::factory()->offline()->create();
            $result = $this->service->create($ujian, [
                'nomor_peserta' => 'P001',
                'nama_peserta' => 'John Doe',
            ]);

            $peserta = $result['peserta'];

            $this->service->markAttendance($peserta, $ujian, 'hadir');
            $this->service->markAttendance($peserta, $ujian, 'hadir');

            $kehadiran = PesertaOfflineKehadiran::where('peserta_offline_id', $peserta->id)->get();
            expect($kehadiran)->toHaveCount(1);
        });

        it('rejects invalid status', function () {
            $ujian = Ujian::factory()->offline()->create();
            $result = $this->service->create($ujian, [
                'nomor_peserta' => 'P001',
                'nama_peserta' => 'John Doe',
            ]);

            $peserta = $result['peserta'];

            expect(function () {
                $this->service->markAttendance($peserta, $ujian, 'invalid');
            })->toThrow(Exception::class);
        });
    });
});
