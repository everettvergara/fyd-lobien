<?php

namespace App\Modules\PropertyListings;

use App\Modules\PropertyListings\Database\Seeders\ListingLookupSeeder;
use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Models\ListingConfiguration;
use App\Modules\PropertyListings\Models\ListingLookup;
use App\Modules\PropertyListings\Policies\ListingConfigurationPolicy;
use App\Modules\PropertyListings\Policies\ListingLookupPolicy;
use App\Modules\PropertyListings\Policies\ListingPolicy;

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
            $this->menuItem('Dropdown Values', 'admin.listing-lookups.index', 'listings.lookups.view', 'bi-menu-button-wide', sort: 20),
            $this->menuItem('Configuration', 'admin.listings.configuration.index', 'listings.configuration.manage', 'bi-sliders', sort: 30),
        ];
    }

    public function seeders(): array
    {
        return [ListingLookupSeeder::class];
    }

    public function publicBlocks(): array
    {
        return [];
    }
}
