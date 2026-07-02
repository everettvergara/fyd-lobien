<?php

use App\Modules\Content\Controllers\ContentController;
use App\Modules\Content\Controllers\ContentTypeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('content-types', [ContentTypeController::class, 'index'])->name('content-types.index');
    Route::get('content-types/create', [ContentTypeController::class, 'create'])->name('content-types.create');
    Route::post('content-types', [ContentTypeController::class, 'store'])->name('content-types.store');
    Route::get('content-types/{content_type}/edit', [ContentTypeController::class, 'edit'])->name('content-types.edit');
    Route::put('content-types/{content_type}', [ContentTypeController::class, 'update'])->name('content-types.update');
    Route::delete('content-types/{content_type}', [ContentTypeController::class, 'destroy'])->name('content-types.destroy');

    Route::get('content', [ContentController::class, 'index'])->name('content.index');
    Route::get('content/create', [ContentController::class, 'create'])->name('content.create');
    Route::post('content', [ContentController::class, 'store'])->name('content.store');
    Route::post('content/bulk', [ContentController::class, 'bulk'])->name('content.bulk');
    Route::get('content/{content}', [ContentController::class, 'show'])->name('content.show');
    Route::get('content/{content}/edit', [ContentController::class, 'edit'])->name('content.edit');
    Route::put('content/{content}', [ContentController::class, 'update'])->name('content.update');
    Route::delete('content/{content}', [ContentController::class, 'destroy'])->name('content.destroy');
    Route::post('content/{content}/publish', [ContentController::class, 'publish'])->name('content.publish');
    Route::post('content/{content}/archive', [ContentController::class, 'archive'])->name('content.archive');
    Route::post('content/{content}/duplicate', [ContentController::class, 'duplicate'])->name('content.duplicate');
    Route::get('content/{content}/preview', [ContentController::class, 'preview'])->name('content.preview');
});
