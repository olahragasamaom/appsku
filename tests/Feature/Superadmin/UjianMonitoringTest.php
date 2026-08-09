<?php

use App\Exports\UjianRankingExport;
use App\Models\JenisUjian;
use App\Models\Soal;
use App\Models\SubIndikator;
use App\Models\SubJenisUjian;
use App\Models\Ujian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superadmin = User::factory()->create([
        'is_superadmin' => true,
        'company_id' => null,
        'is_active' => true,
    ]);

    $this->actingAs($this->superadmin);

    $this->jenis = JenisUjian::factory()->create(['nama_jenis_ujian' => 'SKD']);
    $this->ujian = Ujian::factory()->create(['dibuat_oleh' => $this->superadmin->id, 'status' => 'aktif']);
    $this->ujian->jenisUjians()->attach($this->jenis->id, ['passing_grade' => 4]);
});

describe('Live Scoring', function () {
    it('renders the live scoring page', function () {
        $response = $this->get(route('superadmin.ujian.monitoring.live', $this->ujian));

        $response->assertSuccessful();
        $response->assertViewIs('superadmin.ujian.monitoring.live');
    });

    it('returns live data ordered by nilai desc', function () {
        $p1 = User::factory()->create(['name' => 'Peserta Rendah', 'is_peserta' => true]);
        $p2 = User::factory()->create(['name' => 'Peserta Tinggi', 'is_peserta' => true]);

        $this->ujian->peserta()->create(['user_id' => $p1->id, 'status' => 'selesai', 'total_nilai' => 40]);
        $this->ujian->peserta()->create(['user_id' => $p2->id, 'status' => 'selesai', 'total_nilai' => 90]);

        $response = $this->getJson(route('superadmin.ujian.monitoring.live-data', $this->ujian));

        $response->assertSuccessful();
        $data = $response->json('peserta');

        expect($data[0]['nama'])->toBe('Peserta Tinggi');
        expect($data[0]['rank'])->toBe(1);
    });
});

describe('Ranking', function () {
    it('renders ranking ordered by cumulative score', function () {
        $peserta = User::factory()->create(['name' => 'Juara Satu', 'is_peserta' => true]);
        $this->ujian->peserta()->create(['user_id' => $peserta->id, 'status' => 'selesai', 'total_nilai' => 100, 'lulus' => true]);

        $response = $this->get(route('superadmin.ujian.monitoring.ranking', $this->ujian));

        $response->assertSuccessful();
        $response->assertSee('Juara Satu');
        $response->assertSee('Lulus');
    });
});

describe('Review Jawaban', function () {
    it('renders the answer review for a peserta', function () {
        $subJenis = SubJenisUjian::factory()->create(['jenis_ujian_id' => $this->jenis->id, 'sistem_penilaian' => 'benar_salah', 'nilai_benar' => 5]);
        $subIndikator = SubIndikator::factory()->create(['sub_jenis_ujian_id' => $subJenis->id, 'jenis_ujian_id' => $this->jenis->id]);
        $soal = Soal::factory()->create(['sub_indikator_id' => $subIndikator->id, 'kunci_jawaban' => 'B', 'nilai_bobot_benar' => 5]);
        $ujianSoal = $this->ujian->ujianSoals()->create(['soal_id' => $soal->id, 'jenis_ujian_id' => $this->jenis->id, 'urutan' => 1]);

        $peserta = User::factory()->create(['name' => 'Peserta Review', 'is_peserta' => true]);
        $up = $this->ujian->peserta()->create(['user_id' => $peserta->id, 'status' => 'selesai', 'total_nilai' => 5, 'lulus' => true]);
        $up->jawaban()->create([
            'ujian_soal_id' => $ujianSoal->id,
            'soal_id' => $soal->id,
            'jenis_ujian_id' => $this->jenis->id,
            'jawaban' => 'B',
            'nilai' => 5,
            'benar' => true,
        ]);

        $response = $this->get(route('superadmin.ujian.monitoring.review', ['ujian' => $this->ujian, 'peserta' => $up->id]));

        $response->assertSuccessful();
        $response->assertSee('Peserta Review');
        $response->assertSee('Kunci');
        $response->assertSee('Jawaban Peserta');
    });
});

describe('Simulasi Ujian Superadmin', function () {
    it('renders the full exam simulation page', function () {
        $subJenis = SubJenisUjian::factory()->create(['jenis_ujian_id' => $this->jenis->id, 'sistem_penilaian' => 'benar_salah', 'nilai_benar' => 5]);
        $subIndikator = SubIndikator::factory()->create(['sub_jenis_ujian_id' => $subJenis->id, 'jenis_ujian_id' => $this->jenis->id]);
        $soal = Soal::factory()->create(['sub_indikator_id' => $subIndikator->id, 'kunci_jawaban' => 'B', 'nilai_bobot_benar' => 5]);
        $this->ujian->ujianSoals()->create(['soal_id' => $soal->id, 'jenis_ujian_id' => $this->jenis->id, 'urutan' => 1]);

        $response = $this->get(route('superadmin.ujian.monitoring.simulasi', $this->ujian));

        $response->assertSuccessful();
        $response->assertViewIs('superadmin.ujian.monitoring.simulasi');
        $response->assertSee('Mode Simulasi');
        $response->assertSee('Navigasi Soal');
    });
});

describe('Export Ranking', function () {
    it('downloads the ranking as an excel file', function () {
        Excel::fake();

        $peserta = User::factory()->create(['name' => 'Juara Excel', 'is_peserta' => true]);
        $this->ujian->peserta()->create(['user_id' => $peserta->id, 'status' => 'selesai', 'total_nilai' => 88, 'lulus' => true]);

        $response = $this->get(route('superadmin.ujian.monitoring.ranking.export.excel', $this->ujian));

        $response->assertSuccessful();
        Excel::assertDownloaded('ranking-'.str($this->ujian->nama_ujian)->slug().'.xlsx', function (UjianRankingExport $export) {
            return $export->collection()->contains(fn ($row) => $row->user?->name === 'Juara Excel');
        });
    });

    it('downloads the ranking as a pdf file', function () {
        $peserta = User::factory()->create(['name' => 'Juara PDF', 'is_peserta' => true]);
        $this->ujian->peserta()->create(['user_id' => $peserta->id, 'status' => 'selesai', 'total_nilai' => 77, 'lulus' => true]);

        $response = $this->get(route('superadmin.ujian.monitoring.ranking.export.pdf', $this->ujian));

        $response->assertSuccessful();
        expect($response->headers->get('content-type'))->toContain('application/pdf');
    });

    it('maps ranking rows with rank, name, score and pass status', function () {
        $p1 = User::factory()->create(['name' => 'Rank Satu', 'is_peserta' => true]);
        $p2 = User::factory()->create(['name' => 'Rank Dua', 'is_peserta' => true]);
        $this->ujian->peserta()->create(['user_id' => $p1->id, 'status' => 'selesai', 'total_nilai' => 90, 'lulus' => true]);
        $this->ujian->peserta()->create(['user_id' => $p2->id, 'status' => 'selesai', 'total_nilai' => 50, 'lulus' => false]);

        $export = new UjianRankingExport($this->ujian);

        $first = $export->map($export->collection()->first());

        expect($first[0])->toBe('1');
        expect($first[1])->toBe('Rank Satu');
        expect($first)->toContain('Lulus');
    });
});
