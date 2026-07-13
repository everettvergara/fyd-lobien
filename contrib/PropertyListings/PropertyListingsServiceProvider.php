<?php

namespace App\Modules\PropertyListings;

use App\Modules\PropertyListings\Services\PropertyListingPublicService;
use Illuminate\Support\ServiceProvider;

class PropertyListingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PropertyListingPublicService::class);
    }
}
