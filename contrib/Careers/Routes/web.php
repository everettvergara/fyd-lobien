<?php

use App\Modules\Careers\Controllers\PublicCareerController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/careers')->group(function () {
    Route::get('jobs', [PublicCareerController::class, 'listJobs'])->name('api.careers.jobs.index');
    Route::get('jobs/{slug}', [PublicCareerController::class, 'showJob'])->name('api.careers.jobs.show');
    Route::post('jobs/{slug}/apply', [PublicCareerController::class, 'apply'])->name('api.careers.jobs.apply');
});

Route::get('/careers/{slug}', [PublicCareerController::class, 'showPage'])
    ->name('careers.show')
    ->where('slug', '[a-z0-9\-]+');
