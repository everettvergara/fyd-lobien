<?php

namespace App\Modules\PropertyListings;

use App\Framework\PublicBlock;
use App\Modules\PropertyListings\Blocks\PropertyListingDetailBlockResolver;
use App\Modules\PropertyListings\Blocks\PropertyListingsCitiesBlockResolver;
use App\Modules\PropertyListings\Blocks\PropertyListingsCityBlockResolver;
use App\Modules\PropertyListings\Blocks\PropertySearchBannerBlockResolver;
use App\Modules\PropertyListings\Blocks\PropertySearchResultsBlockResolver;
use App\Modules\PropertyListings\Support\PropertyBannerImageOptionsProvider;
use App\Modules\PropertyListings\Database\Seeders\ListingLookupSeeder;
use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Models\ListingConfiguration;
use App\Modules\PropertyListings\Models\ListingLookup;
use App\Modules\PropertyListings\Policies\ListingConfigurationPolicy;
use App\Modules\PropertyListings\Policies\ListingLookupPolicy;
use App\Modules\PropertyListings\Policies\ListingPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'PropertyListings';
    }

    public function isInstallable(): bool
    {
        return true;
    }

    public function policies(): array
    {
        return [
            Listing::class => ListingPolicy::class,
            ListingLookup::class => ListingLookupPolicy::class,
            ListingConfiguration::class => ListingConfigurationPolicy::class,
        ];
    }

    public function permissions(): array
    {
        return [
            $this->permissionEntry('listings', 'view', 'View Listings'),
            $this->permissionEntry('listings', 'create', 'Create Listings'),
            $this->permissionEntry('listings', 'edit', 'Edit Listings'),
            $this->permissionEntry('listings', 'delete', 'Delete Listings'),
            $this->permissionEntry('listings', 'export', 'Export Listings'),
            $this->permissionEntry('listings', 'import', 'Import Listings'),
            $this->permissionEntry('listings.assets', 'batch', 'Batch Upload Listing Assets'),
            $this->permissionEntry('listings.lookups', 'view', 'View Listing Dropdown Values'),
            $this->permissionEntry('listings.lookups', 'create', 'Create Listing Dropdown Values'),
            $this->permissionEntry('listings.lookups', 'edit', 'Edit Listing Dropdown Values'),
            $this->permissionEntry('listings.lookups', 'delete', 'Delete Listing Dropdown Values'),
            $this->permissionEntry('listings.configuration', 'manage', 'Manage Property Listings Configuration'),
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem('Listings', 'admin.listings.index', 'listings.view', 'bi-buildings', sort: 10),
            $this->menuItem('Property Uploaders', 'admin.property-uploaders.index', 'listings.import', 'bi-cloud-arrow-up', sort: 20),
            $this->menuItem('Dropdown Values', 'admin.listing-lookups.index', 'listings.lookups.view', 'bi-menu-button-wide', sort: 30),
            $this->menuItem('Configuration', 'admin.listings.configuration.index', 'listings.configuration.manage', 'bi-sliders', sort: 40),
        ];
    }

    public function seeders(): array
    {
        return [ListingLookupSeeder::class];
    }

    public function uninstall(): void
    {
        if (Schema::hasTable('menu_items')) {
            app(\App\Modules\PropertyListings\Services\PropertyListingMenuService::class)->removeFooterMenu();
        }

        Schema::dropIfExists('listing_assets');
        Schema::dropIfExists('listing_fees');
        Schema::dropIfExists('listing_remarks');
        Schema::dropIfExists('listing_units');
        Schema::dropIfExists('listing_other_infos');
        Schema::dropIfExists('listing_building_services');
        Schema::dropIfExists('listing_specs');
        Schema::dropIfExists('listings');
        Schema::dropIfExists('listing_lookups');

        if (Schema::hasTable('migrations')) {
            DB::table('migrations')
                ->whereIn('migration', [
                    '2026_07_04_600001_create_property_listing_tables',
                    '2026_07_05_600002_add_published_to_public_to_listings',
                    '2026_07_06_600003_ensure_density_ratio_is_string',
                    '2026_07_06_600004_allow_nullable_fee_type_on_listing_fees',
                    '2026_07_06_600005_allow_nullable_import_conversion_fields',
                    '2026_07_06_600006_change_lift_power_and_efficiency_fields_to_strings',
                    '2026_07_06_600007_allow_nullable_listing_location',
                    '2026_07_07_600008_add_slug_to_listings',
                    '2026_07_07_600009_add_summary_and_description_to_listings',
                ])
                ->delete();
        }
    }

    public function publicBlocks(): array
    {
        return [
            PublicBlock::make('property-listing-detail')
                ->label('Property Listing Detail')
                ->icon('bi-building')
                ->module($this->name())
                ->resolver(PropertyListingDetailBlockResolver::class)
                ->component('PropertyListingDetailBlock'),
            PublicBlock::make('property-listings-city')
                ->label('Property Listings (City)')
                ->icon('bi-buildings')
                ->module($this->name())
                ->resolver(PropertyListingsCityBlockResolver::class)
                ->component('PropertyListingsCityBlock')
                ->configSchema([
                    [
                        'key' => 'per_page',
                        'label' => 'Listings per page',
                        'type' => 'number',
                        'default' => 9,
                        'min' => 1,
                        'max' => 48,
                    ],
                ]),
            PublicBlock::make('property-listings-cities')
                ->label('Property Cities')
                ->icon('bi-geo-alt')
                ->module($this->name())
                ->resolver(PropertyListingsCitiesBlockResolver::class)
                ->component('PropertyListingsCitiesBlock')
                ->configSchema([
                    [
                        'key' => 'per_page',
                        'label' => 'Cities per page',
                        'type' => 'number',
                        'default' => 9,
                        'min' => 1,
                        'max' => 48,
                    ],
                ]),
            PublicBlock::make('property-search-banner')
                ->label('Property Search Banner')
                ->icon('bi-search')
                ->module($this->name())
                ->resolver(PropertySearchBannerBlockResolver::class)
                ->component('PropertySearchBannerBlock')
                ->configSchema([
                    [
                        'key' => 'heading',
                        'label' => 'Heading',
                        'type' => 'text',
                        'default' => 'Find your property',
                    ],
                    [
                        'key' => 'background_image_id',
                        'label' => 'Background Image',
                        'type' => 'select',
                        'optionsProvider' => PropertyBannerImageOptionsProvider::class,
                        'help' => 'Full-width background image from the media library.',
                    ],
                ]),
            PublicBlock::make('property-search-results')
                ->label('Property Search Results')
                ->icon('bi-card-list')
                ->module($this->name())
                ->resolver(PropertySearchResultsBlockResolver::class)
                ->component('PropertySearchResultsBlock')
                ->configSchema([
                    [
                        'key' => 'per_page',
                        'label' => 'Results per page',
                        'type' => 'number',
                        'default' => 9,
                        'min' => 1,
                        'max' => 48,
                    ],
                ]),
        ];
    }
}
