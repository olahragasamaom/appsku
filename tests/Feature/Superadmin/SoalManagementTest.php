<?php

use App\Models\JenisUjian;
use App\Models\Soal;
use App\Models\SubIndikator;
use App\Models\SubJenisUjian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superadmin = User::factory()->create([
        'is_superadmin' => true,
        'company_id' => null,
        'is_active' => true,
    ]);
    $this->actingAs($this->superadmin);
});

describe('Soal Index & Create Pages', function () {
    it('displays bank soal list page', function () {
        $response = $this->get('/superadmin/soal');

        $response->assertSuccessful();
        $response->assertViewIs('superadmin.soal.index');
        $response->assertSee('Bank Soal');
    });

    it('displays the create form', function () {
        $response = $this->get('/superadmin/soal/create');

        $response->assertSuccessful();
        $response->assertViewIs('superadmin.soal.create');
    });

    it('loads CKEditor for the soal input', function () {
        $response = $this->get('/superadmin/soal/create');

        $response->assertSuccessful();
        $response->assertSee('ckeditorSoal(', false);
        $response->assertSee('x-ref="editor"', false);
    });

    it('prefills category when opened with sub_indikator_id in url', function () {
        $jenis = JenisUjian::factory()->create(['nama_jenis_ujian' => 'Ujian Kedinasan']);
        $subJenis = SubJenisUjian::factory()->create(['jenis_ujian_id' => $jenis->id]);
        $subIndikator = SubIndikator::factory()->create([
            'sub_jenis_ujian_id' => $subJenis->id,
            'jenis_ujian_id' => $jenis->id,
        ]);

        $response = $this->get('/superadmin/soal/create?sub_indikator_id='.$subIndikator->id);

        $response->assertSuccessful();
        // Sub indikator harus terkirim ke view dengan relasi lengkap ke atasnya
        $response->assertViewHas('subIndikator', fn ($si) => $si !== null
            && $si->id === $subIndikator->id
            && $si->subJenisUjian?->id === $subJenis->id
            && $si->subJenisUjian?->jenisUjian?->id === $jenis->id);
        // Kategori dikunci karena sudah ditentukan dari luar
        $response->assertViewHas('locked', true);
    });
});

describe('Soal Create - benar_salah', function () {
    it('can create a soal with kunci jawaban', function () {
        $subIndikator = SubIndikator::factory()->create();

        $response = $this->post('/superadmin/soal', [
            'sub_indikator_id' => $subIndikator->id,
            'soal' => 'Apa ibukota Indonesia?',
            'opsi_a' => 'Jakarta',
            'opsi_b' => 'Bandung',
            'opsi_c' => 'Surabaya',
            'opsi_d' => 'Medan',
            'opsi_e' => 'Bali',
            'kunci_jawaban' => 'A',
        ]);

        $response->assertRedirect('/superadmin/soal');
        $this->assertDatabaseHas('panritta_soal', [
            'sub_indikator_id' => $subIndikator->id,
            'kunci_jawaban' => 'A',
            'pembuat_soal_id' => $this->superadmin->id,
        ]);
    });

    it('attaches to exam and redirects to exam page when ujian_id is provided', function () {
        $jenis = JenisUjian::factory()->create();
        $subJenis = SubJenisUjian::factory()->create(['jenis_ujian_id' => $jenis->id, 'sistem_penilaian' => 'benar_salah']);
        $subIndikator = SubIndikator::factory()->create(['sub_jenis_ujian_id' => $subJenis->id, 'jenis_ujian_id' => $jenis->id]);

        $ujian = \App\Models\Ujian::factory()->create(['dibuat_oleh' => $this->superadmin->id, 'jumlah_soal' => 50]);
        $ujian->jenisUjians()->attach($jenis->id);

        $response = $this->post('/superadmin/soal', [
            'sub_indikator_id' => $subIndikator->id,
            'ujian_id' => $ujian->id,
            'soal' => 'Apa ibukota Indonesia?',
            'opsi_a' => 'Jakarta',
            'opsi_b' => 'Bandung',
            'opsi_c' => 'Surabaya',
            'opsi_d' => 'Medan',
            'opsi_e' => 'Bali',
            'kunci_jawaban' => 'A',
        ]);

        $response->assertRedirect(route('superadmin.ujian.soal.index', ['ujian' => $ujian, 'jenis_ujian_id' => $jenis->id]));
        $this->assertDatabaseHas('panritta_ujian_soal', [
            'ujian_id' => $ujian->id,
            'jenis_ujian_id' => $jenis->id,
        ]);
    });

    it('requires kunci jawaban for benar_salah system', function () {
        $subIndikator = SubIndikator::factory()->create();

        $response = $this->post('/superadmin/soal', [
            'sub_indikator_id' => $subIndikator->id,
            'soal' => 'Test',
            'opsi_a' => 'A',
            'opsi_b' => 'B',
            'opsi_c' => 'C',
            'opsi_d' => 'D',
            'opsi_e' => 'E',
        ]);

        $response->assertSessionHasErrors('kunci_jawaban');
    });
});

describe('Soal Create - tiap_jawaban_ada_poin', function () {
    it('can create a soal with bobot per jawaban', function () {
        $subJenis = SubJenisUjian::factory()->poinPerJawaban()->create();
        $subIndikator = SubIndikator::factory()->create([
            'sub_jenis_ujian_id' => $subJenis->id,
            'jenis_ujian_id' => $subJenis->jenis_ujian_id,
        ]);

        $response = $this->post('/superadmin/soal', [
            'sub_indikator_id' => $subIndikator->id,
            'soal' => 'Bagaimana sikap Anda?',
            'opsi_a' => 'A',
            'opsi_b' => 'B',
            'opsi_c' => 'C',
            'opsi_d' => 'D',
            'opsi_e' => 'E',
            'nilai_bobot_a' => 1,
            'nilai_bobot_b' => 2,
            'nilai_bobot_c' => 3,
            'nilai_bobot_d' => 4,
            'nilai_bobot_e' => 5,
        ]);

        $response->assertRedirect('/superadmin/soal');
        $this->assertDatabaseHas('panritta_soal', [
            'sub_indikator_id' => $subIndikator->id,
            'nilai_bobot_a' => 1,
            'nilai_bobot_e' => 5,
        ]);
    });

    it('requires bobot when using poin system', function () {
        $subJenis = SubJenisUjian::factory()->poinPerJawaban()->create();
        $subIndikator = SubIndikator::factory()->create([
            'sub_jenis_ujian_id' => $subJenis->id,
            'jenis_ujian_id' => $subJenis->jenis_ujian_id,
        ]);

        $response = $this->post('/superadmin/soal', [
            'sub_indikator_id' => $subIndikator->id,
            'soal' => 'Test',
            'opsi_a' => 'A',
            'opsi_b' => 'B',
            'opsi_c' => 'C',
            'opsi_d' => 'D',
            'opsi_e' => 'E',
        ]);

        $response->assertSessionHasErrors(['nilai_bobot_a', 'nilai_bobot_e']);
    });
});

describe('Soal Options Endpoints', function () {
    it('returns sub jenis ujian options for a jenis ujian', function () {
        $subJenis = SubJenisUjian::factory()->create();

        $response = $this->getJson("/superadmin/soal/options/sub-jenis-ujian/{$subJenis->jenis_ujian_id}");

        $response->assertSuccessful();
        $response->assertJsonFragment(['id' => $subJenis->id]);
    });

    it('returns sub indikator options for a sub jenis ujian', function () {
        $subIndikator = SubIndikator::factory()->create();

        $response = $this->getJson("/superadmin/soal/options/sub-indikator/{$subIndikator->sub_jenis_ujian_id}");

        $response->assertSuccessful();
        $response->assertJsonFragment(['id' => $subIndikator->id]);
    });
});

describe('Soal Update & Delete', function () {
    it('can update a soal', function () {
        $soal = Soal::factory()->create(['kunci_jawaban' => 'A']);

        $response = $this->put("/superadmin/soal/{$soal->id}", [
            'sub_indikator_id' => $soal->sub_indikator_id,
            'soal' => 'Pertanyaan diperbarui',
            'opsi_a' => 'A',
            'opsi_b' => 'B',
            'opsi_c' => 'C',
            'opsi_d' => 'D',
            'opsi_e' => 'E',
            'kunci_jawaban' => 'B',
        ]);

        $response->assertRedirect('/superadmin/soal');
        $this->assertDatabaseHas('panritta_soal', [
            'id' => $soal->id,
            'soal' => 'Pertanyaan diperbarui',
            'kunci_jawaban' => 'B',
        ]);
    });

    it('can delete a soal', function () {
        Storage::fake('public');
        $soal = Soal::factory()->create();

        $response = $this->delete("/superadmin/soal/{$soal->id}");

        $response->assertRedirect('/superadmin/soal');
        $this->assertDatabaseMissing('panritta_soal', ['id' => $soal->id]);
    });

    it('stores an uploaded gambar soal', function () {
        Storage::fake('public');
        $subIndikator = SubIndikator::factory()->create();

        $response = $this->post('/superadmin/soal', [
            'sub_indikator_id' => $subIndikator->id,
            'soal' => 'Dengan gambar',
            'opsi_a' => 'A',
            'opsi_b' => 'B',
            'opsi_c' => 'C',
            'opsi_d' => 'D',
            'opsi_e' => 'E',
            'kunci_jawaban' => 'C',
            'gambar_soal' => UploadedFile::fake()->image('soal.jpg'),
        ]);

        $response->assertRedirect('/superadmin/soal');
        $soal = Soal::first();
        expect($soal->gambar_soal)->not->toBeNull();
        Storage::disk('public')->assertExists($soal->gambar_soal);
    });
});

describe('WYSIWYG Editor Image Upload', function () {
    it('uploads an inline image and returns its public url', function () {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('inline.jpg');

        $response = $this->postJson(route('superadmin.soal.upload-editor'), [
            'gambar' => $file,
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure(['url']);
        expect($response->json('url'))->toContain('/storage/panritta/soal/editor');
    });

    it('rejects a non-image file', function () {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf');

        $response = $this->postJson(route('superadmin.soal.upload-editor'), [
            'gambar' => $file,
        ]);

        $response->assertStatus(422);
    });
});
