<?php

use App\Modules\Media\Controllers\MediaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('media', [MediaController::class, 'index'])->name('media.index');
    Route::get('media/picker', [MediaController::class, 'picker'])->name('media.picker');
    Route::post('media/preference', [MediaController::class, 'preference'])->name('media.preference');
    Route::post('media', [MediaController::class, 'store'])->name('media.store');
    Route::post('media/bulk', [MediaController::class, 'bulk'])->name('media.bulk');
    Route::post('media/folders', [MediaController::class, 'storeFolder'])->name('media.folders.store');
    Route::delete('media/folders/{folder}', [MediaController::class, 'destroyFolder'])->name('media.folders.destroy');
    Route::get('media/{media}/preview', [MediaController::class, 'preview'])->name('media.preview');
    Route::get('media/{media}/download', [MediaController::class, 'download'])->name('media.download');
    Route::patch('media/{media}', [MediaController::class, 'update'])->name('media.update');
    Route::post('media/{media}/duplicate', [MediaController::class, 'duplicate'])->name('media.duplicate');
    Route::post('media/{media}/replace', [MediaController::class, 'replace'])->name('media.replace');
    Route::post('media/{media}/archive', [MediaController::class, 'archive'])->name('media.archive');
    Route::post('media/{media}/restore', [MediaController::class, 'restore'])->name('media.restore');
    Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
});
