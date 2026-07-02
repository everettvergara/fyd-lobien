<?php

use App\Modules\Banners\Controllers\BannerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('banners', [BannerController::class, 'index'])->name('banners.index');
    Route::get('banners/create', [BannerController::class, 'create'])->name('banners.create');
    Route::post('banners', [BannerController::class, 'store'])->name('banners.store');
    Route::post('banners/bulk', [BannerController::class, 'bulk'])->name('banners.bulk');
    Route::get('banners/{banner}/edit', [BannerController::class, 'edit'])->name('banners.edit');
    Route::get('banners/{banner}/preview', [BannerController::class, 'preview'])->name('banners.preview');
    Route::put('banners/{banner}', [BannerController::class, 'update'])->name('banners.update');
    Route::delete('banners/{banner}', [BannerController::class, 'destroy'])->name('banners.destroy');
    Route::post('banners/{banner}/publish', [BannerController::class, 'publish'])->name('banners.publish');
    Route::post('banners/{banner}/unpublish', [BannerController::class, 'unpublish'])->name('banners.unpublish');
    Route::post('banners/{banner}/archive', [BannerController::class, 'archive'])->name('banners.archive');
    Route::post('banners/{banner}/duplicate', [BannerController::class, 'duplicate'])->name('banners.duplicate');
});
