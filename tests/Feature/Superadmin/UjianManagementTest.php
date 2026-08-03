<?php

use App\Models\JenisUjian;
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

describe('Ujian Index', function () {
    it('displays the ujian list page', function () {
        Ujian::factory()->count(2)->create(['dibuat_oleh' => $this->superadmin->id]);

        $response = $this->get('/superadmin/ujian');

        $response->assertSuccessful();
        $response->assertViewIs('superadmin.ujian.index');
        $response->assertSee('Manajemen Ujian');
    });

    it('can filter ujian by tipe', function () {
        Ujian::factory()->create(['nama_ujian' => 'Ujian Offline A', 'dibuat_oleh' => $this->superadmin->id]);
        Ujian::factory()->online()->create(['nama_ujian' => 'Ujian Online B', 'dibuat_oleh' => $this->superadmin->id]);

        $response = $this->get('/superadmin/ujian?tipe_ujian=online_paket');

        $response->assertSuccessful();
        $response->assertSee('Ujian Online B');
        $response->assertDontSee('Ujian Offline A');
    });
});

describe('Ujian Create', function () {
    it('displays the create form', function () {
        JenisUjian::factory()->count(2)->create();

        $response = $this->get('/superadmin/ujian/create');

        $response->assertSuccessful();
        $response->assertViewIs('superadmin.ujian.create');
        $response->assertSee('Tambah Ujian');
    });

    it('can create an offline ujian with passing grades', function () {
        $skd = JenisUjian::factory()->create(['nama_jenis_ujian' => 'SKD']);
        $skb = JenisUjian::factory()->create(['nama_jenis_ujian' => 'SKB']);

        $response = $this->post('/superadmin/ujian', [
            'nama_ujian' => 'Tryout CPNS Gelombang 1',
            'tipe_ujian' => 'offline_kelas',
            'jumlah_soal' => 100,
            'acak_soal' => 1,
            'tampilkan_hasil' => 1,
            'jenis_ujian_id' => [$skd->id, $skb->id],
            'passing_grade' => [$skd->id => 300, $skb->id => 271],
            'tanggal_ujian' => now()->addDay()->format('Y-m-d\TH:i'),
            'durasi_ujian' => 90,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $ujian = Ujian::firstWhere('nama_ujian', 'Tryout CPNS Gelombang 1');

        expect($ujian)->not->toBeNull();
        expect($ujian->token_ujian)->not->toBeNull();

        $this->assertDatabaseHas('panritta_ujian_jenis_ujian', [
            'ujian_id' => $ujian->id,
            'jenis_ujian_id' => $skd->id,
            'passing_grade' => 300,
        ]);
    });

    it('can create an online ujian with akses member', function () {
        $skd = JenisUjian::factory()->create(['nama_jenis_ujian' => 'SKD']);

        $response = $this->post('/superadmin/ujian', [
            'nama_ujian' => 'Paket Online Premium',
            'tipe_ujian' => 'online_paket',
            'jumlah_soal' => 50,
            'jenis_ujian_id' => [$skd->id],
            'akses_member' => ['Free', 'Basic'],
        ]);

        $response->assertRedirect();

        $ujian = Ujian::firstWhere('nama_ujian', 'Paket Online Premium');

        expect($ujian->akses_member)->toBe(['Free', 'Basic']);
        expect($ujian->token_ujian)->toBeNull();
    });

    it('requires a jenis ujian', function () {
        $response = $this->post('/superadmin/ujian', [
            'nama_ujian' => 'Ujian Tanpa Jenis',
            'tipe_ujian' => 'offline_kelas',
            'jumlah_soal' => 10,
            'tanggal_ujian' => now()->addDay()->format('Y-m-d\TH:i'),
            'durasi_ujian' => 60,
        ]);

        $response->assertSessionHasErrors('jenis_ujian_id');
    });

    it('requires tanggal and durasi for offline ujian', function () {
        $skd = JenisUjian::factory()->create();

        $response = $this->post('/superadmin/ujian', [
            'nama_ujian' => 'Ujian Offline Kurang Data',
            'tipe_ujian' => 'offline_kelas',
            'jumlah_soal' => 10,
            'jenis_ujian_id' => [$skd->id],
        ]);

        $response->assertSessionHasErrors(['tanggal_ujian', 'durasi_ujian']);
    });
});

describe('Ujian Update', function () {
    it('can update an ujian and resync jenis ujian', function () {
        $skd = JenisUjian::factory()->create(['nama_jenis_ujian' => 'SKD']);
        $skb = JenisUjian::factory()->create(['nama_jenis_ujian' => 'SKB']);

        $ujian = Ujian::factory()->create(['dibuat_oleh' => $this->superadmin->id]);
        $ujian->jenisUjians()->sync([$skd->id => ['passing_grade' => 300]]);

        $response = $this->put("/superadmin/ujian/{$ujian->id}", [
            'nama_ujian' => 'Ujian Diperbarui',
            'tipe_ujian' => 'offline_kelas',
            'jumlah_soal' => 120,
            'jenis_ujian_id' => [$skb->id],
            'passing_grade' => [$skb->id => 271],
            'tanggal_ujian' => now()->addDays(2)->format('Y-m-d\TH:i'),
            'durasi_ujian' => 100,
        ]);

        $response->assertRedirect('/superadmin/ujian');

        $this->assertDatabaseHas('panritta_ujian', [
            'id' => $ujian->id,
            'nama_ujian' => 'Ujian Diperbarui',
            'jumlah_soal' => 120,
        ]);

        $this->assertDatabaseMissing('panritta_ujian_jenis_ujian', [
            'ujian_id' => $ujian->id,
            'jenis_ujian_id' => $skd->id,
        ]);

        $this->assertDatabaseHas('panritta_ujian_jenis_ujian', [
            'ujian_id' => $ujian->id,
            'jenis_ujian_id' => $skb->id,
        ]);
    });
});

describe('Ujian Delete', function () {
    it('can delete an ujian', function () {
        $ujian = Ujian::factory()->create(['dibuat_oleh' => $this->superadmin->id]);

        $response = $this->delete("/superadmin/ujian/{$ujian->id}");

        $response->assertRedirect('/superadmin/ujian');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('panritta_ujian', ['id' => $ujian->id]);
    });
});
