<?php

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\TelegramAuthController;
use Illuminate\Support\Facades\Route;

// Guest routes - Telegram login for regular users
Route::middleware('guest')->group(function () {
    // Main login - Telegram only
    Route::get('login', [TelegramAuthController::class, 'create'])
        ->name('login');

    Route::get('auth/telegram/callback', [TelegramAuthController::class, 'callback'])
        ->name('auth.telegram.callback');

    // Admin login - email/password (secret URL)
    Route::get('admin/login', [AdminAuthController::class, 'create'])
        ->name('admin.login');

    Route::post('admin/login', [AdminAuthController::class, 'store']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // Admin logout (redirects to admin login)
    Route::post('admin/logout', [AdminAuthController::class, 'destroy'])
        ->name('admin.logout');
});
