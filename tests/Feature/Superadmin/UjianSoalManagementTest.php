<?php

use App\Models\JenisUjian;
use App\Models\Soal;
use App\Models\SubIndikator;
use App\Models\SubJenisUjian;
use App\Models\Ujian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

function makeSoalForJenis(JenisUjian $jenis): Soal
{
    $subJenis = SubJenisUjian::factory()->create(['jenis_ujian_id' => $jenis->id]);
    $subIndikator = SubIndikator::factory()->create([
        'sub_jenis_ujian_id' => $subJenis->id,
        'jenis_ujian_id' => $jenis->id,
    ]);

    return Soal::factory()->create(['sub_indikator_id' => $subIndikator->id]);
}

describe('Kelola Soal Ujian Index', function () {
    it('displays the manage soal page with jenis ujian tabs', function () {
        $jenis = JenisUjian::factory()->create(['nama_jenis_ujian' => 'SKD']);
        $ujian = Ujian::factory()->create(['dibuat_oleh' => $this->superadmin->id]);
        $ujian->jenisUjians()->attach($jenis->id, ['passing_grade' => 300]);

        $response = $this->get(route('superadmin.ujian.soal.index', $ujian));

        $response->assertSuccessful();
        $response->assertViewIs('superadmin.ujian.soal.index');
        $response->assertSee('SKD');
    });

    it('shows only soal for the active jenis ujian', function () {
        $skd = JenisUjian::factory()->create(['nama_jenis_ujian' => 'SKD']);
        $skb = JenisUjian::factory()->create(['nama_jenis_ujian' => 'SKB']);
        $ujian = Ujian::factory()->create(['dibuat_oleh' => $this->superadmin->id]);
        $ujian->jenisUjians()->attach([$skd->id, $skb->id]);

        $soalSkd = makeSoalForJenis($skd);
        $soalSkb = makeSoalForJenis($skb);

        $ujian->ujianSoals()->create(['soal_id' => $soalSkd->id, 'jenis_ujian_id' => $skd->id, 'urutan' => 1]);
        $ujian->ujianSoals()->create(['soal_id' => $soalSkb->id, 'jenis_ujian_id' => $skb->id, 'urutan' => 2]);

        $response = $this->get(route('superadmin.ujian.soal.index', ['ujian' => $ujian, 'jenis_ujian_id' => $skd->id]));

        $response->assertSuccessful();
        $response->assertSee(Str::limit(strip_tags($soalSkd->soal), 100));
    });

    it('shows sub indikator panel and per-sub-indikator grouping data', function () {
        $jenis = JenisUjian::factory()->create(['nama_jenis_ujian' => 'SKD']);
        $subJenis = SubJenisUjian::factory()->create(['jenis_ujian_id' => $jenis->id]);
        $subIndikator = SubIndikator::factory()->create([
            'sub_jenis_ujian_id' => $subJenis->id,
            'jenis_ujian_id' => $jenis->id,
            'nama_sub_indikator' => 'Pancasila',
        ]);

        $ujian = Ujian::factory()->create(['dibuat_oleh' => $this->superadmin->id]);
        $ujian->jenisUjians()->attach($jenis->id);

        $soal = Soal::factory()->create(['sub_indikator_id' => $subIndikator->id]);
        $ujian->ujianSoals()->create(['soal_id' => $soal->id, 'jenis_ujian_id' => $jenis->id, 'urutan' => 1]);

        $response = $this->get(route('superadmin.ujian.soal.index', ['ujian' => $ujian, 'jenis_ujian_id' => $jenis->id]));

        $response->assertSuccessful();
        $response->assertSee('Pancasila');
        $response->assertViewHas('subIndikatorGroups');
        $response->assertViewHas('ujianSoalsPerSubIndikator');
        $response->assertViewHas('jumlahSoalPerSubIndikator');
    });
});

describe('Import Soal Excel per Sub Indikator', function () {
    it('imports soal from excel and attaches them to the ujian', function () {
        Excel::fake();

        $jenis = JenisUjian::factory()->create();
        $subJenis = SubJenisUjian::factory()->create(['jenis_ujian_id' => $jenis->id]);
        $subIndikator = SubIndikator::factory()->create([
            'sub_jenis_ujian_id' => $subJenis->id,
            'jenis_ujian_id' => $jenis->id,
        ]);

        $ujian = Ujian::factory()->create(['dibuat_oleh' => $this->superadmin->id, 'jumlah_soal' => 50]);
        $ujian->jenisUjians()->attach($jenis->id);

        $file = UploadedFile::fake()->create('soal.xlsx', 10, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $response = $this->post(route('superadmin.ujian.soal.import', $ujian), [
            'sub_indikator_id' => $subIndikator->id,
            'file' => $file,
        ]);

        $response->assertRedirect();
        Excel::assertImported('soal.xlsx');
    });

    it('rejects import when sub indikator does not belong to the ujian jenis', function () {
        $jenis = JenisUjian::factory()->create();
        $otherJenis = JenisUjian::factory()->create();
        $otherSubJenis = SubJenisUjian::factory()->create(['jenis_ujian_id' => $otherJenis->id]);
        $otherSubIndikator = SubIndikator::factory()->create([
            'sub_jenis_ujian_id' => $otherSubJenis->id,
            'jenis_ujian_id' => $otherJenis->id,
        ]);

        $ujian = Ujian::factory()->create(['dibuat_oleh' => $this->superadmin->id]);
        $ujian->jenisUjians()->attach($jenis->id);

        $file = UploadedFile::fake()->create('soal.xlsx', 10, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $response = $this->post(route('superadmin.ujian.soal.import', $ujian), [
            'sub_indikator_id' => $otherSubIndikator->id,
            'file' => $file,
        ]);

        $response->assertNotFound();
    });
});

describe('SoalImport parsing', function () {
    it('creates soal grouped under the given sub indikator', function () {
        $jenis = JenisUjian::factory()->create();
        $subJenis = SubJenisUjian::factory()->create(['jenis_ujian_id' => $jenis->id]);
        $subIndikator = SubIndikator::factory()->create([
            'sub_jenis_ujian_id' => $subJenis->id,
            'jenis_ujian_id' => $jenis->id,
        ]);

        $import = new \App\Imports\SoalImport($subIndikator->id, $this->superadmin->id);
        $import->collection(collect([
            collect(['soal' => 'Ibukota Indonesia?', 'opsi_a' => 'Jakarta', 'opsi_b' => 'Bandung', 'kunci_jawaban' => 'a', 'nilai_bobot_benar' => 5]),
            collect(['soal' => '', 'opsi_a' => 'X', 'opsi_b' => 'Y']), // dilewati karena soal kosong
        ]));

        expect($import->getSuccessCount())->toBe(1);
        expect($import->getSkipCount())->toBe(1);
        expect($import->getCreatedSoalIds())->toHaveCount(1);

        $this->assertDatabaseHas('panritta_soal', [
            'sub_indikator_id' => $subIndikator->id,
            'soal' => 'Ibukota Indonesia?',
            'kunci_jawaban' => 'A',
        ]);
    });
});

describe('Bank Soal Options', function () {
    it('returns bank soal filtered by jenis ujian excluding already attached', function () {
        $jenis = JenisUjian::factory()->create();
        $ujian = Ujian::factory()->create(['dibuat_oleh' => $this->superadmin->id]);
        $ujian->jenisUjians()->attach($jenis->id);

        $attached = makeSoalForJenis($jenis);
        $available = makeSoalForJenis($jenis);

        $ujian->ujianSoals()->create(['soal_id' => $attached->id, 'jenis_ujian_id' => $jenis->id, 'urutan' => 1]);

        $response = $this->getJson(route('superadmin.ujian.soal.bank-options', ['ujian' => $ujian, 'jenis_ujian_id' => $jenis->id]));

        $response->assertSuccessful();
        $ids = collect($response->json())->pluck('id');

        expect($ids)->toContain($available->id);
        expect($ids)->not->toContain($attached->id);
    });
});

describe('Attach Soal', function () {
    it('attaches selected soal to the ujian', function () {
        $jenis = JenisUjian::factory()->create();
        $ujian = Ujian::factory()->create(['dibuat_oleh' => $this->superadmin->id]);
        $ujian->jenisUjians()->attach($jenis->id);

        $soal = makeSoalForJenis($jenis);

        $response = $this->post(route('superadmin.ujian.soal.attach', $ujian), [
            'jenis_ujian_id' => $jenis->id,
            'soal_id' => [$soal->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('panritta_ujian_soal', [
            'ujian_id' => $ujian->id,
            'soal_id' => $soal->id,
            'jenis_ujian_id' => $jenis->id,
        ]);
    });

    it('does not duplicate an already attached soal', function () {
        $jenis = JenisUjian::factory()->create();
        $ujian = Ujian::factory()->create(['dibuat_oleh' => $this->superadmin->id]);
        $ujian->jenisUjians()->attach($jenis->id);

        $soal = makeSoalForJenis($jenis);
        $ujian->ujianSoals()->create(['soal_id' => $soal->id, 'jenis_ujian_id' => $jenis->id, 'urutan' => 1]);

        $this->post(route('superadmin.ujian.soal.attach', $ujian), [
            'jenis_ujian_id' => $jenis->id,
            'soal_id' => [$soal->id],
        ]);

        expect($ujian->ujianSoals()->where('soal_id', $soal->id)->count())->toBe(1);
    });

    it('rejects a jenis ujian not part of the ujian', function () {
        $jenis = JenisUjian::factory()->create();
        $otherJenis = JenisUjian::factory()->create();
        $ujian = Ujian::factory()->create(['dibuat_oleh' => $this->superadmin->id]);
        $ujian->jenisUjians()->attach($jenis->id);

        $soal = makeSoalForJenis($otherJenis);

        $response = $this->post(route('superadmin.ujian.soal.attach', $ujian), [
            'jenis_ujian_id' => $otherJenis->id,
            'soal_id' => [$soal->id],
        ]);

        $response->assertNotFound();
    });
});

describe('Detach Soal', function () {
    it('detaches a soal from the ujian', function () {
        $jenis = JenisUjian::factory()->create();
        $ujian = Ujian::factory()->create(['dibuat_oleh' => $this->superadmin->id]);
        $ujian->jenisUjians()->attach($jenis->id);

        $soal = makeSoalForJenis($jenis);
        $ujianSoal = $ujian->ujianSoals()->create(['soal_id' => $soal->id, 'jenis_ujian_id' => $jenis->id, 'urutan' => 1]);

        $response = $this->delete(route('superadmin.ujian.soal.detach', ['ujian' => $ujian, 'ujianSoal' => $ujianSoal->id]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('panritta_ujian_soal', ['id' => $ujianSoal->id]);
    });
});

describe('Reorder Soal', function () {
    it('reorders soal within a sub indikator and preserves urutan slots', function () {
        $jenis = JenisUjian::factory()->create();
        $subJenis = SubJenisUjian::factory()->create(['jenis_ujian_id' => $jenis->id]);
        $subIndikator = SubIndikator::factory()->create([
            'sub_jenis_ujian_id' => $subJenis->id,
            'jenis_ujian_id' => $jenis->id,
        ]);

        $ujian = Ujian::factory()->create(['dibuat_oleh' => $this->superadmin->id]);
        $ujian->jenisUjians()->attach($jenis->id);

        $soalA = Soal::factory()->create(['sub_indikator_id' => $subIndikator->id]);
        $soalB = Soal::factory()->create(['sub_indikator_id' => $subIndikator->id]);
        $soalC = Soal::factory()->create(['sub_indikator_id' => $subIndikator->id]);

        $first = $ujian->ujianSoals()->create(['soal_id' => $soalA->id, 'jenis_ujian_id' => $jenis->id, 'urutan' => 1]);
        $second = $ujian->ujianSoals()->create(['soal_id' => $soalB->id, 'jenis_ujian_id' => $jenis->id, 'urutan' => 2]);
        $third = $ujian->ujianSoals()->create(['soal_id' => $soalC->id, 'jenis_ujian_id' => $jenis->id, 'urutan' => 3]);

        $response = $this->postJson(route('superadmin.ujian.soal.reorder', $ujian), [
            'ujian_soal_ids' => [$third->id, $first->id, $second->id],
        ]);

        $response->assertSuccessful();

        expect($third->fresh()->urutan)->toBe(1);
        expect($first->fresh()->urutan)->toBe(2);
        expect($second->fresh()->urutan)->toBe(3);
    });

    it('rejects reorder containing ujian soal from another ujian', function () {
        $jenis = JenisUjian::factory()->create();
        $ujian = Ujian::factory()->create(['dibuat_oleh' => $this->superadmin->id]);
        $otherUjian = Ujian::factory()->create(['dibuat_oleh' => $this->superadmin->id]);
        $ujian->jenisUjians()->attach($jenis->id);
        $otherUjian->jenisUjians()->attach($jenis->id);

        $soal = makeSoalForJenis($jenis);
        $foreign = $otherUjian->ujianSoals()->create(['soal_id' => $soal->id, 'jenis_ujian_id' => $jenis->id, 'urutan' => 1]);

        $response = $this->postJson(route('superadmin.ujian.soal.reorder', $ujian), [
            'ujian_soal_ids' => [$foreign->id],
        ]);

        $response->assertNotFound();
    });
});

describe('Finalisasi Ujian', function () {
    it('displays the finalisasi page', function () {
        $jenis = JenisUjian::factory()->create(['nama_jenis_ujian' => 'SKD']);
        $ujian = Ujian::factory()->create(['dibuat_oleh' => $this->superadmin->id]);
        $ujian->jenisUjians()->attach($jenis->id);

        $response = $this->get(route('superadmin.ujian.soal.finalisasi', $ujian));

        $response->assertSuccessful();
        $response->assertViewIs('superadmin.ujian.soal.finalisasi');
        $response->assertSee('SKD');
    });

    it('marks the ujian as finalized', function () {
        $ujian = Ujian::factory()->create(['dibuat_oleh' => $this->superadmin->id, 'finalized_at' => null]);

        $response = $this->post(route('superadmin.ujian.soal.finalize', $ujian));

        $response->assertRedirect();
        expect($ujian->fresh()->finalized_at)->not->toBeNull();
        expect($ujian->fresh()->isFinalized())->toBeTrue();
    });

    it('reopens a finalized ujian', function () {
        $ujian = Ujian::factory()->create(['dibuat_oleh' => $this->superadmin->id, 'finalized_at' => now()]);

        $response = $this->delete(route('superadmin.ujian.soal.unfinalize', $ujian));

        $response->assertRedirect();
        expect($ujian->fresh()->finalized_at)->toBeNull();
    });
});

describe('Remaining Slots', function () {
    it('returns the remaining slots JSON', function () {
        $skd = \App\Models\JenisUjian::factory()->create();
        $ujian = Ujian::factory()->create(['dibuat_oleh' => $this->superadmin->id, 'jumlah_soal' => 5]);
        $ujian->jenisUjians()->attach($skd->id);
        $soal = makeSoalForJenis($skd);
        $ujian->ujianSoals()->create(['soal_id' => $soal->id, 'jenis_ujian_id' => $skd->id, 'urutan' => 1]);

        $response = $this->getJson(route('superadmin.ujian.soal.remaining', $ujian));

        $response->assertSuccessful();
        $response->assertJson([
            'remaining' => 4, // 5 minus 1
            'jumlah_soal' => 5,
        ]);
    });
});
