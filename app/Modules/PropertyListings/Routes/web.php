<?php

use App\Modules\PropertyListings\Controllers\PublicPropertyListingController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/property-listings')->group(function () {
    Route::get('cities/{citySlug}', [PublicPropertyListingController::class, 'cityListings'])
        ->name('api.property-listings.cities.listings')
        ->where('citySlug', '[a-z0-9\-]+');
    Route::get('cities/{citySlug}/listings/{slug}', [PublicPropertyListingController::class, 'show'])
        ->name('api.property-listings.listings.show')
        ->where(['citySlug' => '[a-z0-9\-]+', 'slug' => '[a-z0-9\-]+']);
});
