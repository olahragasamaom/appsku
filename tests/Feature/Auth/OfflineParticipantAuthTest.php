<?php

use App\Http\Middleware\OfflineParticipantAuth;
use App\Models\OfflineParticipantSession;
use App\Models\PesertaOffline;
use App\Models\Ujian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function runMiddleware(Request $request): \Symfony\Component\HttpFoundation\Response
{
    $middleware = app(OfflineParticipantAuth::class);

    return $middleware->handle($request, fn () => new Response('ok'));
}

function makeSessionRequest(array $session = [], ?Ujian $ujian = null): Request
{
    $request = Request::create('/test');
    $request->setLaravelSession(app('session')->driver());
    $request->session()->put($session);

    if ($ujian) {
        $route = new Route(['GET'], 'ujian/{ujian}/kerjakan', []);
        $route->bind($request);
        $route->setParameter('ujian', $ujian);
        $request->setRouteResolver(fn () => $route);
    }

    return $request;
}

/**
 * Helper: Create valid offline session with peserta, attempt, and participant session.
 */
function makeValidOfflineSession(Ujian $ujian, string $status = 'sedang_ujian'): array
{
    $peserta = PesertaOffline::factory()->create([
        'ujian_id' => $ujian->id,
        'is_active' => true,
    ]);

    $attempt = $ujian->peserta()->create([
        'user_id' => null,
        'status' => $status,
        'waktu_mulai' => now(),
        'waktu_selesai' => $status === 'selesai' ? now() : null,
    ]);

    $peserta->update(['ujian_peserta_id' => $attempt->id]);

    $sessionToken = Str::random(40);
    OfflineParticipantSession::create([
        'peserta_offline_id' => $peserta->id,
        'ujian_id' => $ujian->id,
        'session_token' => $sessionToken,
        'status' => 'sedang_ujian',
        'login_at' => now(),
        'last_activity_at' => now(),
    ]);

    return [
        'peserta' => $peserta,
        'attempt' => $attempt,
        'sessionToken' => $sessionToken,
    ];
}

describe('OfflineParticipantAuth middleware', function () {
    it('aborts 403 when offline_peserta_id session key is missing', function () {
        $request = makeSessionRequest([]);

        expect(fn () => runMiddleware($request))->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
    });

    it('aborts 403 when offline_ujian_id does not match route ujian', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas', 'status' => 'aktif']);
        $other = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas', 'status' => 'aktif']);

        $data = makeValidOfflineSession($other);

        $request = makeSessionRequest([
            'offline_peserta_id' => $data['peserta']->id,
            'offline_session_token' => $data['sessionToken'],
            'offline_ujian_id' => $other->id,
            'offline_attempt_id' => $data['attempt']->id,
        ], $ujian);

        expect(fn () => runMiddleware($request))->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
    });

    it('passes through when session is valid and attempt is active', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas', 'status' => 'aktif']);

        $data = makeValidOfflineSession($ujian);

        $request = makeSessionRequest([
            'offline_peserta_id' => $data['peserta']->id,
            'offline_session_token' => $data['sessionToken'],
            'offline_ujian_id' => $ujian->id,
            'offline_attempt_id' => $data['attempt']->id,
        ], $ujian);

        $response = runMiddleware($request);

        expect($response->getContent())->toBe('ok');
    });

    it('redirects to hasil when attempt is selesai and tampilkan_hasil is true', function () {
        $ujian = Ujian::factory()->create([
            'tipe_ujian' => 'offline_kelas',
            'status' => 'aktif',
            'tampilkan_hasil' => true,
        ]);

        $data = makeValidOfflineSession($ujian, 'selesai');

        $request = makeSessionRequest([
            'offline_peserta_id' => $data['peserta']->id,
            'offline_session_token' => $data['sessionToken'],
            'offline_ujian_id' => $ujian->id,
            'offline_attempt_id' => $data['attempt']->id,
        ], $ujian);

        $response = runMiddleware($request);

        expect($response->getStatusCode())->toBe(302);
    });

    it('aborts 403 when attempt is selesai and tampilkan_hasil is false', function () {
        $ujian = Ujian::factory()->create([
            'tipe_ujian' => 'offline_kelas',
            'status' => 'aktif',
            'tampilkan_hasil' => false,
        ]);

        $data = makeValidOfflineSession($ujian, 'selesai');

        $request = makeSessionRequest([
            'offline_peserta_id' => $data['peserta']->id,
            'offline_session_token' => $data['sessionToken'],
            'offline_ujian_id' => $ujian->id,
            'offline_attempt_id' => $data['attempt']->id,
        ], $ujian);

        expect(fn () => runMiddleware($request))->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
    });
});
