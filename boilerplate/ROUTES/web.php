<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Settings\UserController;
use Illuminate\Support\Facades\Route;

// ============================================================================
// PUBLIC ROUTES (Guest)
// ============================================================================

Route::middleware('guest')->group(function () {
    // Login
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Register
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    // Forgot Password
    Route::get('forgot-password', [PasswordResetController::class, 'request'])
        ->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'send'])
        ->name('password.email');

    // Reset Password
    Route::get('reset-password/{token}', [PasswordResetController::class, 'reset'])
        ->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'update'])
        ->name('password.update');
});

// ============================================================================
// PROTECTED ROUTES (Auth Required)
// ============================================================================

Route::middleware('auth:sanctum')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Logout
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // ========================================================================
    // SETTINGS ROUTES
    // ========================================================================

    Route::prefix('settings')->name('settings.')->group(function () {
        // Users Management
        Route::resource('users', UserController::class);
    });
});

// ============================================================================
// REDIRECT / CATCHALL
// ============================================================================

// Redirect root to dashboard if authenticated, to login if not
Route::redirect('/', '/dashboard');
