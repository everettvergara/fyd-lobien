<?php

use App\Modules\Newsletter\Controllers\NewsletterListController;
use App\Modules\Newsletter\Controllers\NewsletterSendController;
use App\Modules\Newsletter\Controllers\NewsletterSubscriberController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::resource('newsletter-lists', NewsletterListController::class)->except(['show']);

    Route::get('newsletter-subscribers/export', [NewsletterSubscriberController::class, 'export'])
        ->name('newsletter-subscribers.export');
    Route::post('newsletter-subscribers/bulk', [NewsletterSubscriberController::class, 'bulkDestroy'])
        ->name('newsletter-subscribers.bulk');
    Route::resource('newsletter-subscribers', NewsletterSubscriberController::class)->except(['show']);

    Route::get('newsletters/compose', [NewsletterSendController::class, 'compose'])->name('newsletters.compose');
    Route::post('newsletters/send', [NewsletterSendController::class, 'send'])->name('newsletters.send');
    Route::get('newsletter-sends', [NewsletterSendController::class, 'index'])->name('newsletter-sends.index');
});
