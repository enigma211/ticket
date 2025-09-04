<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    // Cooldown-based login throttle: allow 1 attempt per X minutes (per IP)
    $loginCooldown = max(1, (int) (settings('cooldown_login_minutes') ?? 10));
    // Allow up to 5 attempts within the cooldown window (was 1 attempt, too strict)
    $loginThrottle = "throttle:5,{$loginCooldown}";
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware($loginThrottle)->name('login');

    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    $passwordCooldown = max(1, (int) (settings('cooldown_password_minutes') ?? 10));
    $passwordThrottle = "throttle:1,{$passwordCooldown}";
    Volt::route('forgot-password', 'pages.auth.forgot-password')->middleware($passwordThrottle)->name('password.request');
    Volt::route('reset-password/{token}', 'pages.auth.reset-password')->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'pages.auth.confirm-password')->name('password.confirm');
});
