<?php

use App\Modules\Authentication\Controllers\ChangePasswordController;
use App\Modules\Authentication\Controllers\EmailVerificationController;
use App\Modules\Authentication\Controllers\ForgotPasswordController;
use App\Modules\Authentication\Controllers\LoginController;
use App\Modules\Authentication\Controllers\LogoutController;
use App\Modules\Authentication\Controllers\ProfileController;
use App\Modules\Authentication\Controllers\RegisterController;
use App\Modules\Authentication\Controllers\ResetPasswordController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
    Route::middleware('registration.enabled')->group(function () {
        Route::get('register', [RegisterController::class, 'create'])->name('register');
        Route::post('register', [RegisterController::class, 'store']);
    });
    Route::get('forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
});

Route::get('email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->name('verification.verify');

Route::middleware('auth')->group(function () {
    Route::get('email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    Route::post('logout', [LogoutController::class, 'destroy'])->name('logout');
});

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('change-password', [ChangePasswordController::class, 'show'])->name('password.change');
    Route::put('change-password', [ChangePasswordController::class, 'update'])->name('password.change.update');
});
