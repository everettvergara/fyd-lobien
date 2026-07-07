<?php

use App\Modules\Newsletter\Controllers\PublicNewsletterController;
use App\Modules\Newsletter\Controllers\PublicNewsletterUnsubscribeController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/newsletters')->group(function () {
    Route::get('{slug}', [PublicNewsletterController::class, 'show'])->name('api.newsletters.show');
    Route::post('{slug}/subscribe', [PublicNewsletterController::class, 'subscribe'])->name('api.newsletters.subscribe');
    Route::post('{slug}/unsubscribe', [PublicNewsletterController::class, 'unsubscribe'])->name('api.newsletters.unsubscribe');
});

Route::get('/newsletters/unsubscribe/{token}', PublicNewsletterUnsubscribeController::class)
    ->name('newsletters.unsubscribe')
    ->where('token', '[a-zA-Z0-9]+');
