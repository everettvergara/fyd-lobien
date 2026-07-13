<?php

namespace App\Modules\PropertyListings\Database\Seeders;

use App\Modules\PropertyListings\Services\ListingDemoSeedService;
use Illuminate\Database\Seeder;

class ListingDemoSeeder extends Seeder
{
    public function run(): void
    {
        app(ListingDemoSeedService::class)->seed();
    }
}
