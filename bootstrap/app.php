<?php

use App\Http\Middleware\CheckBlockedIp;
use App\Http\Middleware\DetectAttack;
use App\Http\Middleware\EnsureExamAccess;
use App\Http\Middleware\EnsurePeserta;
use App\Http\Middleware\EnsureSuperadmin;
use App\Http\Middleware\EnsureUserIsEmployee;
use App\Http\Middleware\LogRateLimitHit;
use App\Http\Middleware\OfflineParticipantAuth;
use App\Http\Middleware\RedirectEmployeeToPortal;
use App\Http\Middleware\SetTenant;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant' => SetTenant::class,
            'superadmin' => EnsureSuperadmin::class,
            'peserta' => EnsurePeserta::class,
            'employee' => EnsureUserIsEmployee::class,
            'admin' => RedirectEmployeeToPortal::class,
            'offline.auth' => OfflineParticipantAuth::class,
            'exam.access' => EnsureExamAccess::class,
            // Spatie Permission middleware
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

        $middleware->appendToGroup('web', [
            DetectAttack::class,
            CheckBlockedIp::class,
            SetTenant::class,
            LogRateLimitHit::class,
        ]);

        $middleware->appendToGroup('api', [
            DetectAttack::class,
            CheckBlockedIp::class,
            LogRateLimitHit::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (UnauthorizedException $e) {
            abort(403, $e->getMessage());
        });
    })->create();
