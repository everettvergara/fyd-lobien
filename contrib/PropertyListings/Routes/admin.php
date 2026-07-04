<?php

use App\Modules\PropertyListings\Controllers\ListingAssetBatchController;
use App\Modules\PropertyListings\Controllers\ListingCompareController;
use App\Modules\PropertyListings\Controllers\ListingConfigurationController;
use App\Modules\PropertyListings\Controllers\ListingController;
use App\Modules\PropertyListings\Controllers\ListingExportController;
use App\Modules\PropertyListings\Controllers\ListingImportController;
use App\Modules\PropertyListings\Controllers\ListingLookupController;
use App\Modules\PropertyListings\Controllers\ListingRemarkController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('listings/compare', [ListingCompareController::class, 'compare'])->name('listings.compare');
    Route::get('listings/export', [ListingExportController::class, 'export'])->name('listings.export');

    Route::get('listings/import', [ListingImportController::class, 'importForm'])->name('listings.import');
    Route::post('listings/import/preview', [ListingImportController::class, 'importPreview'])->name('listings.import.preview');
    Route::post('listings/import/commit', [ListingImportController::class, 'importCommit'])->name('listings.import.commit');
    Route::get('listings/import/template', [ListingImportController::class, 'template'])->name('listings.import.template');

    Route::get('listings/assets/batch', [ListingAssetBatchController::class, 'batchForm'])->name('listings.assets.batch');
    Route::post('listings/assets/batch/preview', [ListingAssetBatchController::class, 'batchPreview'])->name('listings.assets.batch.preview');
    Route::post('listings/assets/batch/commit', [ListingAssetBatchController::class, 'batchCommit'])->name('listings.assets.batch.commit');

    Route::post('listings/{listing}/assets/batch/upload', [ListingAssetBatchController::class, 'listingBatchUpload'])->name('listings.assets.batch.listing.upload');
    Route::post('listings/{listing}/assets/batch/preview', [ListingAssetBatchController::class, 'listingBatchPreview'])->name('listings.assets.batch.listing.preview');
    Route::post('listings/{listing}/assets/batch/commit', [ListingAssetBatchController::class, 'listingBatchCommit'])->name('listings.assets.batch.listing.commit');

    Route::get('listings/configuration', [ListingConfigurationController::class, 'index'])->name('listings.configuration.index');
    Route::post('listings/configuration/seed-samples', [ListingConfigurationController::class, 'seedSamples'])->name('listings.configuration.seed-samples');

    Route::get('listings/{listing}/remarks', [ListingRemarkController::class, 'index'])->name('listings.remarks.index');
    Route::post('listings/{listing}/remarks', [ListingRemarkController::class, 'store'])->name('listings.remarks.store');

    Route::resource('listings', ListingController::class)->except(['show']);

    Route::get('listing-lookups', [ListingLookupController::class, 'index'])->name('listing-lookups.index');
    Route::get('listing-lookups/{group}', [ListingLookupController::class, 'groupIndex'])->name('listing-lookups.group');
    Route::get('listing-lookups/{group}/create', [ListingLookupController::class, 'create'])->name('listing-lookups.create');
    Route::post('listing-lookups/{group}', [ListingLookupController::class, 'store'])->name('listing-lookups.store');
    Route::get('listing-lookups/{group}/{listing_lookup}/edit', [ListingLookupController::class, 'edit'])->name('listing-lookups.edit');
    Route::put('listing-lookups/{group}/{listing_lookup}', [ListingLookupController::class, 'update'])->name('listing-lookups.update');
    Route::delete('listing-lookups/{group}/{listing_lookup}', [ListingLookupController::class, 'destroy'])->name('listing-lookups.destroy');
});
