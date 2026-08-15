<?php

use App\Exports\Templates\PesertaOfflineTemplateExport;
use App\Models\Ujian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superadmin = User::factory()->create([
        'is_superadmin' => true,
        'company_id' => null,
        'is_active' => true,
    ]);

    $this->actingAs($this->superadmin);
});

describe('Peserta Offline Index', function () {
    it('displays the peserta offline page', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);

        $response = $this->get(route('superadmin.ujian.peserta-offline.index', $ujian));

        $response->assertSuccessful();
        $response->assertViewIs('superadmin.ujian.peserta-offline.index');
    });

    it('displays the plaintext kode akses in the participant list', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);

        // Tambah peserta lewat controller agar kode_akses_plain terisi
        $this->post(route('superadmin.ujian.peserta-offline.store', $ujian), [
            'nomor_peserta' => 'P-010',
            'nama_peserta' => 'Dewi',
        ]);

        $kodeAkses = $ujian->pesertaOffline()->where('nomor_peserta', 'P-010')->first()->kode_akses_plain;

        $response = $this->get(route('superadmin.ujian.peserta-offline.index', $ujian));

        $response->assertSuccessful();
        $response->assertSee($kodeAkses);
    });
});

describe('Peserta Offline Store', function () {
    it('creates a peserta and flashes the plaintext kode akses once', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);

        $response = $this->post(route('superadmin.ujian.peserta-offline.store', $ujian), [
            'nomor_peserta' => 'P-001',
            'nama_peserta' => 'Budi',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('kode_akses');

        $this->assertDatabaseHas('panritta_peserta_offline', [
            'ujian_id' => $ujian->id,
            'nomor_peserta' => 'P-001',
            'nama_peserta' => 'Budi',
        ]);
    });

    it('stores a hashed kode akses, not the plaintext', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);

        $this->post(route('superadmin.ujian.peserta-offline.store', $ujian), [
            'nomor_peserta' => 'P-002',
            'nama_peserta' => 'Sari',
        ]);

        $plaintext = session('kode_akses');
        $peserta = $ujian->pesertaOffline()->where('nomor_peserta', 'P-002')->first();

        expect($peserta->kode_akses)->not->toBe($plaintext);
        expect(Hash::check($plaintext, $peserta->kode_akses))->toBeTrue();
        // Versi teks harus tersimpan & sama dengan yang di-flash
        expect($peserta->kode_akses_plain)->toBe($plaintext);
    });

    it('rejects a duplicate nomor peserta within the same ujian', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);
        $ujian->pesertaOffline()->create([
            'nomor_peserta' => 'P-001',
            'nama_peserta' => 'Budi',
            'kode_akses' => Hash::make('x'),
        ]);

        $response = $this->post(route('superadmin.ujian.peserta-offline.store', $ujian), [
            'nomor_peserta' => 'P-001',
            'nama_peserta' => 'Andi',
        ]);

        $response->assertSessionHasErrors('nomor_peserta');
    });

    it('rejects creating a peserta on a non-offline exam', function () {
        $ujian = Ujian::factory()->online()->create();

        $response = $this->post(route('superadmin.ujian.peserta-offline.store', $ujian), [
            'nomor_peserta' => 'P-001',
            'nama_peserta' => 'Budi',
        ]);

        $response->assertSessionHasErrors('ujian');
    });
});

describe('Peserta Offline Destroy', function () {
    it('deletes a peserta offline', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);
        $peserta = $ujian->pesertaOffline()->create([
            'nomor_peserta' => 'P-001',
            'nama_peserta' => 'Budi',
            'kode_akses' => Hash::make('x'),
        ]);

        $response = $this->delete(route('superadmin.ujian.peserta-offline.destroy', [$ujian, $peserta]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('panritta_peserta_offline', ['id' => $peserta->id]);
    });
});

describe('Peserta Offline Bulk Destroy', function () {
    it('deletes multiple selected peserta', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);

        $a = $ujian->pesertaOffline()->create(['nomor_peserta' => '001', 'nama_peserta' => 'A', 'kode_akses' => Hash::make('x')]);
        $b = $ujian->pesertaOffline()->create(['nomor_peserta' => '002', 'nama_peserta' => 'B', 'kode_akses' => Hash::make('x')]);
        $c = $ujian->pesertaOffline()->create(['nomor_peserta' => '003', 'nama_peserta' => 'C', 'kode_akses' => Hash::make('x')]);

        $response = $this->post(route('superadmin.ujian.peserta-offline.bulk-destroy', $ujian), [
            'ids' => [$a->id, $b->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('panritta_peserta_offline', ['id' => $a->id]);
        $this->assertDatabaseMissing('panritta_peserta_offline', ['id' => $b->id]);
        $this->assertDatabaseHas('panritta_peserta_offline', ['id' => $c->id]);
    });

    it('does not delete peserta from another ujian', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);
        $other = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);

        $foreign = $other->pesertaOffline()->create(['nomor_peserta' => '001', 'nama_peserta' => 'X', 'kode_akses' => Hash::make('x')]);

        $this->post(route('superadmin.ujian.peserta-offline.bulk-destroy', $ujian), [
            'ids' => [$foreign->id],
        ]);

        $this->assertDatabaseHas('panritta_peserta_offline', ['id' => $foreign->id]);
    });

    it('requires at least one id', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);

        $response = $this->post(route('superadmin.ujian.peserta-offline.bulk-destroy', $ujian), []);

        $response->assertSessionHasErrors('ids');
    });
});

describe('Peserta Offline Template', function () {
    it('downloads the excel import template', function () {
        Excel::fake();

        $response = $this->get(route('superadmin.ujian.peserta-offline.template'));

        $response->assertSuccessful();
        Excel::assertDownloaded('template_peserta_offline.xlsx', function (PesertaOfflineTemplateExport $export) {
            return $export->headings() === ['Nomor Peserta', 'Nama Peserta'];
        });
    });
});

describe('Peserta Offline Import', function () {
    it('imports peserta from a csv file and generates kode akses', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);

        $csv = "Nomor Peserta,Nama Peserta\n001,Budi Santoso\n002,Siti Aminah\n";
        $file = UploadedFile::fake()->createWithContent('peserta.csv', $csv);

        $response = $this->post(route('superadmin.ujian.peserta-offline.import', $ujian), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('panritta_peserta_offline', [
            'ujian_id' => $ujian->id,
            'nomor_peserta' => '001',
            'nama_peserta' => 'Budi Santoso',
        ]);
        $this->assertDatabaseHas('panritta_peserta_offline', [
            'ujian_id' => $ujian->id,
            'nomor_peserta' => '002',
            'nama_peserta' => 'Siti Aminah',
        ]);

        $peserta = $ujian->pesertaOffline()->where('nomor_peserta', '001')->first();
        expect($peserta->kode_akses_plain)->not->toBeNull();
        expect(Hash::check($peserta->kode_akses_plain, $peserta->kode_akses))->toBeTrue();
    });

    it('skips duplicate nomor peserta and reports it', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);
        $ujian->pesertaOffline()->create([
            'nomor_peserta' => '001',
            'nama_peserta' => 'Existing',
            'kode_akses' => Hash::make('x'),
        ]);

        $csv = "Nomor Peserta,Nama Peserta\n001,Duplikat\n002,Baru\n";
        $file = UploadedFile::fake()->createWithContent('peserta.csv', $csv);

        $response = $this->post(route('superadmin.ujian.peserta-offline.import', $ujian), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('import_errors');

        expect($ujian->pesertaOffline()->where('nomor_peserta', '001')->count())->toBe(1);
        $this->assertDatabaseHas('panritta_peserta_offline', [
            'ujian_id' => $ujian->id,
            'nomor_peserta' => '002',
            'nama_peserta' => 'Baru',
        ]);
    });

    it('skips rows with empty nomor or nama', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);

        $csv = "Nomor Peserta,Nama Peserta\n,Tanpa Nomor\n003,\n004,Valid\n";
        $file = UploadedFile::fake()->createWithContent('peserta.csv', $csv);

        $response = $this->post(route('superadmin.ujian.peserta-offline.import', $ujian), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        expect($ujian->pesertaOffline()->count())->toBe(1);
        $this->assertDatabaseHas('panritta_peserta_offline', [
            'nomor_peserta' => '004',
            'nama_peserta' => 'Valid',
        ]);
    });

    it('rejects import without a file', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);

        $response = $this->post(route('superadmin.ujian.peserta-offline.import', $ujian), []);

        $response->assertSessionHasErrors('file');
    });

    it('rejects import on a non-offline exam', function () {
        $ujian = Ujian::factory()->online()->create();

        $csv = "Nomor Peserta,Nama Peserta\n001,Budi\n";
        $file = UploadedFile::fake()->createWithContent('peserta.csv', $csv);

        $response = $this->post(route('superadmin.ujian.peserta-offline.import', $ujian), [
            'file' => $file,
        ]);

        $response->assertNotFound();
    });
});

describe('Peserta Offline Export', function () {
    it('exports the credential sheet as PDF', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);
        $ujian->pesertaOffline()->create([
            'nomor_peserta' => 'P-001',
            'nama_peserta' => 'Budi',
            'kode_akses' => Hash::make('x'),
        ]);

        $response = $this->get(route('superadmin.ujian.peserta-offline.export', $ujian));

        $response->assertSuccessful();
        expect($response->headers->get('content-type'))->toContain('application/pdf');
    });
});
