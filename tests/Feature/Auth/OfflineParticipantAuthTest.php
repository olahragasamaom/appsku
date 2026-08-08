<?php

use App\Http\Middleware\OfflineParticipantAuth;
use App\Models\Ujian;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;

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

describe('OfflineParticipantAuth middleware', function () {
    it('aborts 403 when offline_peserta_id session key is missing', function () {
        $request = makeSessionRequest([]);

        expect(fn () => runMiddleware($request))->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
    });

    it('aborts 403 when offline_ujian_id does not match route ujian', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas', 'status' => 'aktif']);
        $other = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas', 'status' => 'aktif']);

        $request = makeSessionRequest([
            'offline_peserta_id' => 1,
            'offline_ujian_id' => $other->id,
        ], $ujian);

        expect(fn () => runMiddleware($request))->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
    });

    it('passes through when session is valid and attempt is active', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas', 'status' => 'aktif']);
        $user = User::factory()->create();
        $attempt = $ujian->peserta()->create([
            'user_id' => $user->id,
            'status' => 'sedang_ujian',
        ]);

        $request = makeSessionRequest([
            'offline_peserta_id' => 1,
            'offline_ujian_id' => $ujian->id,
            'offline_attempt_id' => $attempt->id,
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
        $user = User::factory()->create();
        $attempt = $ujian->peserta()->create([
            'user_id' => $user->id,
            'status' => 'selesai',
            'waktu_selesai' => now(),
        ]);

        $request = makeSessionRequest([
            'offline_peserta_id' => 1,
            'offline_ujian_id' => $ujian->id,
            'offline_attempt_id' => $attempt->id,
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
        $user = User::factory()->create();
        $attempt = $ujian->peserta()->create([
            'user_id' => $user->id,
            'status' => 'selesai',
            'waktu_selesai' => now(),
        ]);

        $request = makeSessionRequest([
            'offline_peserta_id' => 1,
            'offline_ujian_id' => $ujian->id,
            'offline_attempt_id' => $attempt->id,
        ], $ujian);

        expect(fn () => runMiddleware($request))->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
    });
});
