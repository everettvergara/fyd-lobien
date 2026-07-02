<?php

use App\Modules\Cache\Controllers\CacheSettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('cache', [CacheSettingsController::class, 'index'])->name('cache.index');
    Route::put('cache', [CacheSettingsController::class, 'update'])->name('cache.update');
    Route::post('cache/clear', [CacheSettingsController::class, 'clear'])->name('cache.clear');
});
