<?php

use App\Modules\WebForms\Controllers\WebformBuilderController;
use App\Modules\WebForms\Controllers\WebformController;
use App\Modules\WebForms\Controllers\WebformSubmissionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::resource('webforms', WebformController::class)->except(['show']);
    Route::get('webforms/{webform}/builder', [WebformBuilderController::class, 'edit'])->name('webforms.builder');
    Route::put('webforms/{webform}/builder', [WebformBuilderController::class, 'update'])->name('webforms.builder.update');

    Route::get('webform-submissions', [WebformSubmissionController::class, 'index'])->name('webform-submissions.index');
    Route::post('webform-submissions/bulk-delete', [WebformSubmissionController::class, 'bulkDestroy'])->name('webform-submissions.bulk-delete');
    Route::get('webform-submissions/{webformSubmission}', [WebformSubmissionController::class, 'show'])->name('webform-submissions.show');
    Route::delete('webform-submissions/{webformSubmission}', [WebformSubmissionController::class, 'destroy'])->name('webform-submissions.destroy');
});
