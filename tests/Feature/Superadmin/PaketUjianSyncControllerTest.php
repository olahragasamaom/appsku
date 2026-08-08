<?php

use App\Models\Paket;
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

describe('Paket Ujian Sync Controller', function () {
    it('syncs online exams to a package', function () {
        $paket = Paket::factory()->create();
        $ujianA = Ujian::factory()->online()->create();
        $ujianB = Ujian::factory()->online()->create();

        $response = $this->put(route('superadmin.paket.ujian.sync', $paket), [
            'ujian_id' => [$ujianA->id, $ujianB->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        expect($paket->ujians()->count())->toBe(2);
        expect($paket->ujians()->pluck('panritta_ujian.id')->all())->toBe([$ujianA->id, $ujianB->id]);
    });

    it('rejects an offline exam', function () {
        $paket = Paket::factory()->create();
        $offline = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);

        $response = $this->put(route('superadmin.paket.ujian.sync', $paket), [
            'ujian_id' => [$offline->id],
        ]);

        $response->assertSessionHasErrors('ujian_id');

        expect($paket->ujians()->count())->toBe(0);
    });

    it('clears all exams when passing an empty array', function () {
        $paket = Paket::factory()->create();
        $ujian = Ujian::factory()->online()->create();
        $paket->ujians()->attach($ujian->id);

        $response = $this->put(route('superadmin.paket.ujian.sync', $paket), [
            'ujian_id' => [],
        ]);

        $response->assertRedirect();

        expect($paket->ujians()->count())->toBe(0);
    });

    it('validates the ujian_id exists', function () {
        $paket = Paket::factory()->create();

        $response = $this->put(route('superadmin.paket.ujian.sync', $paket), [
            'ujian_id' => [999],
        ]);

        $response->assertSessionHasErrors('ujian_id.0');
    });
});
