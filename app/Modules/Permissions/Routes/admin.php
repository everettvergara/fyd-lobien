<?php

use App\Modules\Permissions\Controllers\PermissionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
});
