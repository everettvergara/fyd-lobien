<?php

namespace App\Modules\PropertyListings\Services;

use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Support\ListingBrochureTypes;
use App\Modules\PropertyListings\Support\ListingCompareFormatter;
use App\Modules\PropertyListings\Support\ListingLookupGroups;
use App\Modules\PropertyListings\Support\ListingLookupRegistry;

class ListingBrochureService
{
    public function __construct(
        protected ListingLookupRegistry $registry,
    ) {}

    public function loadListing(Listing $listing): Listing
    {
        return $listing->load([
            'spec',
            'buildingService',
            'otherInfo',
            'units',
            'fees',
            'assets.media',
        ]);
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function lookupOptions(): array
    {
        return collect(ListingLookupGroups::keys())
            ->mapWithKeys(fn (string $group) => [$group => $this->registry->options($group)])
            ->all();
    }

    /**
     * @return array<int, array{layout: string, images: array<int, array{thumb: string, full: string, alt: string}>}>
     */
    public function imagePages(Listing $listing, string $brochureType): array
    {
        $assetType = match ($brochureType) {
            ListingBrochureTypes::INTERIOR => 'interior',
            ListingBrochureTypes::PROPERTY_PHOTOS => 'building',
            ListingBrochureTypes::FLOOR_PLAN => 'floor-plan',
            default => '',
        };

        $images = $assetType !== '' ? $listing->assetImages($assetType) : [];

        if ($images === []) {
            return [['layout' => 'empty', 'images' => []]];
        }

        return match ($brochureType) {
            ListingBrochureTypes::INTERIOR => $this->interiorPages($images),
            ListingBrochureTypes::PROPERTY_PHOTOS => $this->buildingPages($images),
            ListingBrochureTypes::FLOOR_PLAN => $this->floorPlanPages($images),
            default => $this->singleImagePages($images),
        };
    }

    /**
     * @param  array<int, array{thumb: string, full: string, alt: string}>  $images
     * @return array<int, array{layout: string, images: array<int, array{thumb: string, full: string, alt: string}>}>
     */
    protected function interiorPages(array $images): array
    {
        $count = count($images);

        if ($count === 1) {
            return [['layout' => 'hero', 'images' => $images]];
        }

        if ($count === 2) {
            return [['layout' => 'pair', 'images' => $images]];
        }

        if ($count === 3) {
            return [['layout' => 'triple', 'images' => $images]];
        }

        $pages = [];

        foreach (array_chunk($images, 4) as $chunk) {
            $pages[] = [
                'layout' => count($chunk) <= 2 ? 'pair' : 'grid-2x2',
                'images' => $chunk,
            ];
        }

        return $pages;
    }

    /**
     * @param  array<int, array{thumb: string, full: string, alt: string}>  $images
     * @return array<int, array{layout: string, images: array<int, array{thumb: string, full: string, alt: string}>}>
     */
    protected function buildingPages(array $images): array
    {
        $count = count($images);

        if ($count === 1) {
            return [['layout' => 'cinematic', 'images' => $images]];
        }

        if ($count === 2) {
            return [['layout' => 'stack', 'images' => $images]];
        }

        if ($count === 3) {
            return [
                ['layout' => 'hero', 'images' => [$images[0]]],
                ['layout' => 'pair', 'images' => [$images[1], $images[2]]],
            ];
        }

        return $this->singleImagePages($images, 'hero');
    }

    /**
     * @param  array<int, array{thumb: string, full: string, alt: string}>  $images
     * @return array<int, array{layout: string, images: array<int, array{thumb: string, full: string, alt: string}>}>
     */
    protected function floorPlanPages(array $images): array
    {
        return $this->singleImagePages($images, 'plan');
    }

    /**
     * @param  array<int, array{thumb: string, full: string, alt: string}>  $images
     * @return array<int, array{layout: string, images: array<int, array{thumb: string, full: string, alt: string}>}>
     */
    protected function singleImagePages(array $images, string $layout = 'hero'): array
    {
        return array_map(
            fn (array $image) => ['layout' => $layout, 'images' => [$image]],
            $images,
        );
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    public function propertyInfoRows(Listing $listing): array
    {
        $spec = $listing->spec;
        $building = $listing->buildingService;
        $other = $listing->otherInfo;

        $rows = [
            ['label' => 'Developer', 'value' => $this->text($spec?->developer)],
            ['label' => 'Building Grade', 'value' => $this->lookup(ListingLookupGroups::GRADE, $spec?->grade)],
            ['label' => 'Completion Date', 'value' => $this->text($spec?->completion_year)],
            ['label' => 'No. of Storey', 'value' => $this->text($spec?->no_of_floors)],
            ['label' => 'No. of Basement', 'value' => $this->text($spec?->no_of_basement)],
            ['label' => 'Gross Leasable Area – Office (m²)', 'value' => ListingCompareFormatter::area($spec?->gross_leasable_area)],
            ['label' => 'Typical Floor Area – Office (m²)', 'value' => ListingCompareFormatter::area($spec?->typical_floor_area)],
            ['label' => 'Floor Efficiency', 'value' => $this->text($spec?->floor_efficiency)],
            ['label' => 'Floor to Ceiling Height (m)', 'value' => $this->text($spec?->floor_to_ceiling_height)],
            ['label' => 'Allotted Parking Slots', 'value' => $this->text($spec?->parking_allocation)],
            ['label' => 'AC System', 'value' => $this->text($building?->ac_system)],
            ['label' => 'No. of Passenger Lifts', 'value' => $this->text($building?->no_of_lifts_passenger)],
            ['label' => 'No. of Service Lifts', 'value' => $this->text($building?->no_of_lifts_service)],
            ['label' => 'Telco Providers', 'value' => $this->text($building?->telco)],
            ['label' => 'Density Ratio', 'value' => $this->text($spec?->density_ratio)],
            ['label' => 'Operating Hours', 'value' => $this->text($building?->operating_hours)],
            ['label' => 'Backup Power', 'value' => $this->text($building?->backup_power)],
            ['label' => 'PEZA Accredited', 'value' => $this->lookup(ListingLookupGroups::PEZA_ACCREDITATION, $other?->peza_accreditation)],
            ['label' => 'Sustainability', 'value' => $this->text($other?->sustainability)],
        ];

        return $rows;
    }

    public function lookupLabel(string $group, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return $this->registry->label($group, (string) $value) ?: (string) $value;
    }

    protected function lookup(string $group, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        $label = $this->registry->label($group, (string) $value);

        return $label !== '' ? $label : (string) $value;
    }

    protected function text(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return (string) $value;
    }
}
