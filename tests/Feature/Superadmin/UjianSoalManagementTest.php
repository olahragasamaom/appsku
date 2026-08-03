<?php

use App\Models\JenisUjian;
use App\Models\Soal;
use App\Models\SubIndikator;
use App\Models\SubJenisUjian;
use App\Models\Ujian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
