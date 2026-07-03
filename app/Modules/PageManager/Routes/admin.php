<?php

use App\Modules\PageManager\Controllers\PageController;
use App\Modules\PageManager\Controllers\PageMasterController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('page-master', [PageMasterController::class, 'edit'])->name('page-master.edit');
    Route::put('page-master', [PageMasterController::class, 'update'])->name('page-master.update');

    Route::get('pages', [PageController::class, 'index'])->name('pages.index');
    Route::get('pages/create', [PageController::class, 'create'])->name('pages.create');
    Route::post('pages', [PageController::class, 'store'])->name('pages.store');
    Route::get('pages/{page}/edit', [PageController::class, 'edit'])->name('pages.edit');
    Route::put('pages/{page}', [PageController::class, 'update'])->name('pages.update');
    Route::delete('pages/{page}', [PageController::class, 'destroy'])->name('pages.destroy');
    Route::post('pages/{page}/publish', [PageController::class, 'publish'])->name('pages.publish');
});
