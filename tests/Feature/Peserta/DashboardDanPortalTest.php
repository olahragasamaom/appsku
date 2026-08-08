<?php

use App\Models\JenisUjian;
use App\Models\Paket;
use App\Models\Ujian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Dashboard Peserta (Online Member)', function () {
    it('only shows exams from active subscribed packages', function () {
        $user = User::factory()->create(['is_peserta' => true]);
        
        $paketAktif = Paket::factory()->create(['nama_paket' => 'Paket Aktif']);
        $paketLain = Paket::factory()->create(['nama_paket' => 'Paket Belum Dibeli']);

        $ujianAktif = Ujian::factory()->create(['tipe_ujian' => 'online_paket', 'status' => 'aktif', 'nama_ujian' => 'Ujian Boleh Diakses']);
        $ujianLain = Ujian::factory()->create(['tipe_ujian' => 'online_paket', 'status' => 'aktif', 'nama_ujian' => 'Ujian Terkunci']);

        $paketAktif->ujians()->attach($ujianAktif->id);
        $paketLain->ujians()->attach($ujianLain->id);

        // Beri langganan aktif ke $paketAktif
        $user->langganan()->create([
            'paket_id' => $paketAktif->id,
            'status' => 'active',
            'mulai_pada' => now(),
            'berakhir_pada' => now()->addDays(30),
        ]);

        $response = $this->actingAs($user)->get(route('peserta.dashboard'));

        $response->assertSuccessful();
        $response->assertSee('Ujian Boleh Diakses');
        $response->assertDontSee('Ujian Terkunci');
    });

    it('shows prompt to buy a package when no active subscription', function () {
        $user = User::factory()->create(['is_peserta' => true]);
        
        $response = $this->actingAs($user)->get(route('peserta.dashboard'));

        $response->assertSuccessful();
        $response->assertSee('Anda belum berlangganan paket apapun');
    });
});

describe('Portal Peserta Offline (Public)', function () {
    it('shows offline exams scheduled for today', function () {
        $ujianHariIni = Ujian::factory()->create([
            'nama_ujian' => 'Ujian Hari Ini',
            'tipe_ujian' => 'offline_kelas',
            'status' => 'aktif',
            'tanggal_ujian' => now()->setHour(10)->setMinute(0),
        ]);

        $ujianBesok = Ujian::factory()->create([
            'nama_ujian' => 'Ujian Besok',
            'tipe_ujian' => 'offline_kelas',
            'status' => 'aktif',
            'tanggal_ujian' => now()->addDay(),
        ]);

        $response = $this->get(route('peserta.ujian.offline.portal'));

        $response->assertSuccessful();
        $response->assertSee('Ujian Hari Ini');
        $response->assertDontSee('Ujian Besok');
    });
});
