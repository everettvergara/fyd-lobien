<?php

use App\Modules\PropertyListings\Controllers\ListingAssetBatchController;
use App\Modules\PropertyListings\Controllers\ListingBrochureController;
use App\Modules\PropertyListings\Controllers\ListingCompareController;
use App\Modules\PropertyListings\Controllers\ListingConfigurationController;
use App\Modules\PropertyListings\Controllers\ListingController;
use App\Modules\PropertyListings\Controllers\ListingExportController;
use App\Modules\PropertyListings\Controllers\ListingImportController;
use App\Modules\PropertyListings\Controllers\ListingLookupController;
use App\Modules\PropertyListings\Controllers\ListingRemarkController;
use App\Modules\PropertyListings\Controllers\PropertySearchBannerController;
use App\Modules\PropertyListings\Controllers\PropertyUploaderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('listings/compare', [ListingCompareController::class, 'compare'])->name('listings.compare');

    Route::get('listings/{listing}/brochures', [ListingBrochureController::class, 'index'])->name('listings.brochures.index');
    Route::get('listings/{listing}/brochures/{type}', [ListingBrochureController::class, 'show'])
        ->whereIn('type', ['interior', 'property-photos', 'floor-plan', 'floors-units', 'property-information', 'all'])
        ->name('listings.brochures.show');

    Route::get('property-uploaders', [PropertyUploaderController::class, 'index'])->name('property-uploaders.index');
    Route::get('property-uploaders/assets', [ListingAssetBatchController::class, 'batchForm'])->name('property-uploaders.assets');
    Route::post('property-uploaders/assets/stage/start', [ListingAssetBatchController::class, 'batchStageStart'])->name('property-uploaders.assets.stage.start');
    Route::post('property-uploaders/assets/stage/file', [ListingAssetBatchController::class, 'batchStageFile'])->name('property-uploaders.assets.stage.file');
    Route::post('property-uploaders/assets/stage/validate', [ListingAssetBatchController::class, 'batchStageValidate'])->name('property-uploaders.assets.stage.validate');
    Route::post('property-uploaders/assets/stage/preview', [ListingAssetBatchController::class, 'batchStagePreview'])->name('property-uploaders.assets.stage.preview');
    Route::post('property-uploaders/assets/preview', [ListingAssetBatchController::class, 'batchPreview'])->name('property-uploaders.assets.preview');
    Route::post('property-uploaders/assets/commit/progress', [ListingAssetBatchController::class, 'batchCommitProgress'])->name('property-uploaders.assets.commit.progress');
    Route::post('property-uploaders/assets/commit', [ListingAssetBatchController::class, 'batchCommit'])->name('property-uploaders.assets.commit');
    Route::get('property-uploaders/{type}/export', [ListingExportController::class, 'export'])
        ->whereIn('type', ['header', 'units', 'fees'])
        ->name('property-uploaders.export');
    Route::get('property-uploaders/{type}/import', [ListingImportController::class, 'importForm'])
        ->whereIn('type', ['header', 'units', 'fees'])
        ->name('property-uploaders.import');
    Route::post('property-uploaders/{type}/import/preview', [ListingImportController::class, 'importPreview'])
        ->whereIn('type', ['header', 'units', 'fees'])
        ->name('property-uploaders.import.preview');
    Route::post('property-uploaders/{type}/import/commit', [ListingImportController::class, 'importCommit'])
        ->whereIn('type', ['header', 'units', 'fees'])
        ->name('property-uploaders.import.commit');
    Route::get('property-uploaders/{type}/template', [ListingImportController::class, 'template'])
        ->whereIn('type', ['header', 'units', 'fees'])
        ->name('property-uploaders.template');

    Route::get('listings/export', [ListingExportController::class, 'export'])->defaults('type', 'header')->name('listings.export');
    Route::get('listings/import', [ListingImportController::class, 'importForm'])->defaults('type', 'header')->name('listings.import');
    Route::post('listings/import/preview', [ListingImportController::class, 'importPreview'])->defaults('type', 'header')->name('listings.import.preview');
    Route::post('listings/import/commit', [ListingImportController::class, 'importCommit'])->defaults('type', 'header')->name('listings.import.commit');
    Route::get('listings/import/template', [ListingImportController::class, 'template'])->defaults('type', 'header')->name('listings.import.template');
    Route::get('listings/assets/batch', [ListingAssetBatchController::class, 'batchForm'])->name('listings.assets.batch');
    Route::post('listings/assets/batch/preview', [ListingAssetBatchController::class, 'batchPreview'])->name('listings.assets.batch.preview');
    Route::post('listings/assets/batch/commit', [ListingAssetBatchController::class, 'batchCommit'])->name('listings.assets.batch.commit');

    Route::post('listings/{listing}/assets/batch/upload', [ListingAssetBatchController::class, 'listingBatchUpload'])->name('listings.assets.batch.listing.upload');
    Route::post('listings/{listing}/assets/batch/preview', [ListingAssetBatchController::class, 'listingBatchPreview'])->name('listings.assets.batch.listing.preview');
    Route::post('listings/{listing}/assets/batch/commit', [ListingAssetBatchController::class, 'listingBatchCommit'])->name('listings.assets.batch.listing.commit');

    Route::get('listings/configuration', [ListingConfigurationController::class, 'index'])->name('listings.configuration.index');
    Route::post('listings/configuration/seed-samples', [ListingConfigurationController::class, 'seedSamples'])->name('listings.configuration.seed-samples');
    Route::post('listings/configuration/generate-pages', [ListingConfigurationController::class, 'generatePages'])->name('listings.configuration.generate-pages');
    Route::get('listings/configuration/generate-pages/status', [ListingConfigurationController::class, 'generatePagesStatus'])->name('listings.configuration.generate-pages.status');
    Route::post('listings/configuration/clear-pages', [ListingConfigurationController::class, 'clearPages'])->name('listings.configuration.clear-pages');

    Route::get('listings/{listing}/remarks', [ListingRemarkController::class, 'index'])->name('listings.remarks.index');
    Route::post('listings/{listing}/remarks', [ListingRemarkController::class, 'store'])->name('listings.remarks.store');
    Route::delete('listings/{listing}/remarks/{remark}', [ListingRemarkController::class, 'destroy'])->name('listings.remarks.destroy');

    Route::patch('listings/{listing}/published', [ListingController::class, 'updatePublished'])->name('listings.published');
    Route::post('listings/publish-all', [ListingController::class, 'publishAll'])->name('listings.publish-all');
    Route::post('listings/unpublish-all', [ListingController::class, 'unpublishAll'])->name('listings.unpublish-all');

    Route::resource('listings', ListingController::class)->except(['show']);

    Route::resource('property-search-banners', PropertySearchBannerController::class)->except(['show']);

    Route::get('listing-lookups', [ListingLookupController::class, 'index'])->name('listing-lookups.index');
    Route::get('listing-lookups/{group}', [ListingLookupController::class, 'groupIndex'])->name('listing-lookups.group');
    Route::get('listing-lookups/{group}/create', [ListingLookupController::class, 'create'])->name('listing-lookups.create');
    Route::post('listing-lookups/{group}', [ListingLookupController::class, 'store'])->name('listing-lookups.store');
    Route::get('listing-lookups/{group}/{listing_lookup}/edit', [ListingLookupController::class, 'edit'])->name('listing-lookups.edit');
    Route::put('listing-lookups/{group}/{listing_lookup}', [ListingLookupController::class, 'update'])->name('listing-lookups.update');
    Route::delete('listing-lookups/{group}/{listing_lookup}', [ListingLookupController::class, 'destroy'])->name('listing-lookups.destroy');
});
