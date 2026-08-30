<?php

use App\Models\Paket;
use App\Models\PesertaLangganan;
use App\Models\Ujian;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->peserta = User::factory()->create([
        'password' => Hash::make('password'),
        'is_peserta' => true,
        'is_active' => true,
    ]);
});

test('ujian online tanpa tanggal muncul di dashboard kapan saja', function () {
    $paket = Paket::factory()->create(['is_active' => true]);
    $ujian = Ujian::factory()->create([
        'status' => 'aktif',
        'tipe_ujian' => 'online_paket',
        'tanggal_ujian' => null,
        'batas_keterlambatan' => null,
    ]);
    $paket->ujians()->attach($ujian);

    PesertaLangganan::create([
        'user_id' => $this->peserta->id,
        'paket_id' => $paket->id,
        'status' => 'active',
        'mulai_pada' => now(),
        'berakhir_pada' => now()->addDays(30),
    ]);

    $response = $this->actingAs($this->peserta, 'web')->get(route('peserta.dashboard'));

    $response->assertStatus(200);
    $response->assertSee($ujian->nama_ujian);
});

test('ujian online dengan tanggal di masa lalu dan batas keterlambatan di masa depan tetap muncul', function () {
    $paket = Paket::factory()->create(['is_active' => true]);

    // Ujian tanggal 18 Agustus 2026, batas keterlambatan 2 September 2026
    $ujian = Ujian::factory()->create([
        'status' => 'aktif',
        'tipe_ujian' => 'online_paket',
        'tanggal_ujian' => now()->parse('2026-08-18 08:00:00'),
        'batas_keterlambatan' => now()->parse('2026-09-02 23:59:59'),
    ]);
    $paket->ujians()->attach($ujian);

    PesertaLangganan::create([
        'user_id' => $this->peserta->id,
        'paket_id' => $paket->id,
        'status' => 'active',
        'mulai_pada' => now(),
        'berakhir_pada' => now()->addDays(30),
    ]);

    // Akses di tanggal 1 September 2026 (masih dalam batas keterlambatan)
    $this->travelTo(now()->parse('2026-09-01 10:00:00'));

    $response = $this->actingAs($this->peserta, 'web')->get(route('peserta.dashboard'));

    $response->assertStatus(200);
    $response->assertSee($ujian->nama_ujian);
});

test('ujian online tidak muncul sebelum tanggal ujian', function () {
    $paket = Paket::factory()->create(['is_active' => true]);

    // Ujian akan berlangsung besok
    $ujian = Ujian::factory()->create([
        'status' => 'aktif',
        'tipe_ujian' => 'online_paket',
        'tanggal_ujian' => now()->addDay(),
        'batas_keterlambatan' => now()->addDays(7),
    ]);
    $paket->ujians()->attach($ujian);

    PesertaLangganan::create([
        'user_id' => $this->peserta->id,
        'paket_id' => $paket->id,
        'status' => 'active',
        'mulai_pada' => now(),
        'berakhir_pada' => now()->addDays(30),
    ]);

    $response = $this->actingAs($this->peserta, 'web')->get(route('peserta.dashboard'));

    $response->assertStatus(200);
    $response->assertDontSee($ujian->nama_ujian);
});

test('ujian online tidak muncul setelah batas keterlambatan', function () {
    $paket = Paket::factory()->create(['is_active' => true]);

    // Ujian sudah lewat batas keterlambatan
    $ujian = Ujian::factory()->create([
        'status' => 'aktif',
        'tipe_ujian' => 'online_paket',
        'tanggal_ujian' => now()->subDays(10),
        'batas_keterlambatan' => now()->subDay(),
    ]);
    $paket->ujians()->attach($ujian);

    PesertaLangganan::create([
        'user_id' => $this->peserta->id,
        'paket_id' => $paket->id,
        'status' => 'active',
        'mulai_pada' => now(),
        'berakhir_pada' => now()->addDays(30),
    ]);

    $response = $this->actingAs($this->peserta, 'web')->get(route('peserta.dashboard'));

    $response->assertStatus(200);
    $response->assertDontSee($ujian->nama_ujian);
});

test('ujian offline muncul di portal dalam rentang tanggal hingga batas keterlambatan', function () {
    // Ujian tanggal 18 Agustus 2026, batas keterlambatan 2 September 2026
    $ujian = Ujian::factory()->create([
        'status' => 'aktif',
        'tipe_ujian' => 'offline_kelas',
        'tanggal_ujian' => now()->parse('2026-08-18 08:00:00'),
        'batas_keterlambatan' => now()->parse('2026-09-02 23:59:59'),
    ]);

    // Akses di tanggal 1 September 2026 (masih dalam batas keterlambatan)
    $this->travelTo(now()->parse('2026-09-01 10:00:00'));

    $response = $this->get(route('peserta.ujian.offline.portal'));

    $response->assertStatus(200);
    $response->assertSee($ujian->nama_ujian);
});

test('ujian offline tidak muncul setelah batas keterlambatan', function () {
    // Ujian sudah lewat batas keterlambatan
    $ujian = Ujian::factory()->create([
        'status' => 'aktif',
        'tipe_ujian' => 'offline_kelas',
        'tanggal_ujian' => now()->subDays(10),
        'batas_keterlambatan' => now()->subDay(),
    ]);

    $response = $this->get(route('peserta.ujian.offline.portal'));

    $response->assertStatus(200);
    $response->assertDontSee($ujian->nama_ujian);
});

test('ujian offline tidak muncul sebelum tanggal ujian', function () {
    // Ujian akan berlangsung besok
    $ujian = Ujian::factory()->create([
        'status' => 'aktif',
        'tipe_ujian' => 'offline_kelas',
        'tanggal_ujian' => now()->addDay(),
        'batas_keterlambatan' => now()->addDays(7),
    ]);

    $response = $this->get(route('peserta.ujian.offline.portal'));

    $response->assertStatus(200);
    $response->assertDontSee($ujian->nama_ujian);
});
