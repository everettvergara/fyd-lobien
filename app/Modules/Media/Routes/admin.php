<?php

use App\Modules\Media\Controllers\MediaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('media', [MediaController::class, 'index'])->name('media.index');
    Route::get('media/picker', [MediaController::class, 'picker'])->name('media.picker');
    Route::post('media', [MediaController::class, 'store'])->name('media.store');
    Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
});
