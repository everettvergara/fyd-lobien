<?php

use App\Modules\Careers\Controllers\CareerApplicationController;
use App\Modules\Careers\Controllers\CareerJobController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::resource('career-jobs', CareerJobController::class)->except(['show']);

    Route::get('career-applications', [CareerApplicationController::class, 'index'])->name('career-applications.index');
    Route::post('career-applications/bulk-delete', [CareerApplicationController::class, 'bulkDestroy'])->name('career-applications.bulk-destroy');
    Route::get('career-applications/{careerApplication}', [CareerApplicationController::class, 'show'])->name('career-applications.show');
    Route::get('career-applications/{careerApplication}/resume', [CareerApplicationController::class, 'downloadResume'])->name('career-applications.download-resume');
    Route::delete('career-applications/{careerApplication}', [CareerApplicationController::class, 'destroy'])->name('career-applications.destroy');
});
