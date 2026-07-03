<?php

use App\Modules\Themes\Controllers\ThemeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('themes', [ThemeController::class, 'index'])->name('themes.index');
    Route::put('themes', [ThemeController::class, 'update'])->name('themes.update');
    Route::post('themes/{slug}/install', [ThemeController::class, 'install'])->name('themes.install');
});
