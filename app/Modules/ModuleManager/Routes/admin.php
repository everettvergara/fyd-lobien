<?php

use App\Modules\ModuleManager\Controllers\ModuleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('modules', [ModuleController::class, 'index'])->name('modules.index');
    Route::post('modules/{name}/install', [ModuleController::class, 'install'])->name('modules.install');
    Route::post('modules/{name}/disable', [ModuleController::class, 'disable'])->name('modules.disable');
    Route::post('modules/{name}/enable', [ModuleController::class, 'enable'])->name('modules.enable');
    Route::post('modules/{name}/uninstall', [ModuleController::class, 'uninstall'])->name('modules.uninstall');
});
