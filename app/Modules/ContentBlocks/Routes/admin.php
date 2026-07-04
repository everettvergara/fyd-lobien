<?php

use App\Modules\ContentBlocks\Controllers\ContentBlockController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('content-blocks', [ContentBlockController::class, 'index'])->name('content-blocks.index');
    Route::get('content-blocks/create', [ContentBlockController::class, 'create'])->name('content-blocks.create');
    Route::post('content-blocks', [ContentBlockController::class, 'store'])->name('content-blocks.store');
    Route::post('content-blocks/bulk', [ContentBlockController::class, 'bulk'])->name('content-blocks.bulk');
    Route::post('content-blocks/preview', [ContentBlockController::class, 'preview'])->name('content-blocks.preview');
    Route::post('content-blocks/{contentBlock}/preview', [ContentBlockController::class, 'preview'])->name('content-blocks.preview-existing');
    Route::get('content-blocks/{contentBlock}/edit', [ContentBlockController::class, 'edit'])->name('content-blocks.edit');
    Route::put('content-blocks/{contentBlock}', [ContentBlockController::class, 'update'])->name('content-blocks.update');
    Route::delete('content-blocks/{contentBlock}', [ContentBlockController::class, 'destroy'])->name('content-blocks.destroy');
});
