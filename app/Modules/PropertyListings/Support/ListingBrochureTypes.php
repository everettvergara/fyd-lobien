<?php

namespace App\Modules\PropertyListings\Support;

use App\Modules\PropertyListings\Models\Listing;

class ListingBrochureTypes
{
    public const INTERIOR = 'interior';

    public const PROPERTY_PHOTOS = 'property-photos';

    public const FLOOR_PLAN = 'floor-plan';

    public const FLOORS_UNITS = 'floors-units';

    public const PROPERTY_INFORMATION = 'property-information';

    public const ALL = 'all';

    /**
     * @return array<string, array{label: string, icon: string, asset_type: string|null}>
     */
    public static function definitions(): array
    {
        return [
            self::INTERIOR => [
                'label' => 'Interior',
                'icon' => 'bi-lamp',
                'asset_type' => 'interior',
            ],
            self::PROPERTY_PHOTOS => [
                'label' => 'Property Photos',
                'icon' => 'bi-building',
                'asset_type' => 'building',
            ],
            self::FLOOR_PLAN => [
                'label' => 'Floor Plan',
                'icon' => 'bi-grid-3x3',
                'asset_type' => 'floor-plan',
            ],
            self::FLOORS_UNITS => [
                'label' => 'Floors / Units',
                'icon' => 'bi-table',
                'asset_type' => null,
            ],
            self::PROPERTY_INFORMATION => [
                'label' => 'Property Information',
                'icon' => 'bi-info-circle',
                'asset_type' => null,
            ],
            self::ALL => [
                'label' => 'Print All',
                'icon' => 'bi-files',
                'asset_type' => null,
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function keys(): array
    {
        return array_keys(self::definitions());
    }

    public static function isValid(string $type): bool
    {
        return array_key_exists($type, self::definitions());
    }

    public static function label(string $type): string
    {
        return self::definitions()[$type]['label'] ?? $type;
    }

    public static function icon(string $type): string
    {
        return self::definitions()[$type]['icon'] ?? 'bi-file-earmark';
    }

    public static function url(Listing $listing, string $type): string
    {
        return route('admin.listings.brochures.show', [$listing, $type]);
    }

    public static function hexagonFrameUrl(): string
    {
        return asset('modules/property-listings/brochure-hexagon-frame.png');
    }

    /**
     * @return array<int, string>
     */
    public static function contentTypes(): array
    {
        return [
            self::INTERIOR,
            self::PROPERTY_PHOTOS,
            self::FLOOR_PLAN,
            self::FLOORS_UNITS,
            self::PROPERTY_INFORMATION,
        ];
    }
}
