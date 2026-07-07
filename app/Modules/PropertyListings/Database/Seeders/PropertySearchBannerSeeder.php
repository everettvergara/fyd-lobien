<?php

namespace App\Modules\PropertyListings\Database\Seeders;

use App\Modules\PropertyListings\Models\PropertySearchBanner;
use Illuminate\Database\Seeder;

class PropertySearchBannerSeeder extends Seeder
{
    public function run(): void
    {
        PropertySearchBanner::updateOrCreate(
            ['key' => 'default'],
            [
                'name' => 'Default Property Search',
                'heading' => 'Find your property',
                'is_active' => true,
            ],
        );
    }
}
