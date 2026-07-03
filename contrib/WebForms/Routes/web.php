<?php

use App\Modules\WebForms\Controllers\PublicWebformController;
use App\Modules\WebForms\Controllers\PublicWebformPageController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/webforms')->group(function () {
    Route::get('{slug}', [PublicWebformController::class, 'show'])->name('api.webforms.show');
    Route::post('{slug}/submit', [PublicWebformController::class, 'submit'])->name('api.webforms.submit');
});

Route::get('/forms/{slug}', [PublicWebformPageController::class, 'show'])
    ->name('webforms.show')
    ->where('slug', '[a-z0-9\-]+');
