<?php

namespace App\Modules\PropertyListings\Services;

use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Support\ListingLookupGroups;
use App\Modules\PropertyListings\Support\ListingLookupRegistry;

class ListingCompareService
{
    public function __construct(
        protected ListingLookupRegistry $registry,
    ) {}

    /**
     * @param  array<int, int|string>  $ids
     * @return array{
     *     listings: array<int, array<string, mixed>>,
     *     rows: array<int, array{key: string, label: string, values: array<int, mixed>}>
     * }
     */
    public function matrix(array $ids): array
    {
        $normalizedIds = collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $listings = Listing::query()
            ->whereIn('id', $normalizedIds)
            ->with(['spec', 'buildingService', 'otherInfo', 'units'])
            ->get()
            ->sortBy(fn (Listing $listing) => array_search($listing->id, $normalizedIds, true))
            ->values();

        $listingSummaries = $listings->map(fn (Listing $listing) => [
            'id' => $listing->id,
            'code' => $listing->code,
            'name' => $listing->name,
        ])->all();

        $rows = collect($this->rowDefinitions())
            ->map(function (array $definition) use ($listings) {
                return [
                    'key' => $definition['key'],
                    'label' => $definition['label'],
                    'values' => $listings->map(fn (Listing $listing) => $definition['value']($listing, $this))->all(),
                ];
            })
            ->values()
            ->all();

        return [
            'listings' => $listingSummaries,
            'rows' => $rows,
        ];
    }

    /**
     * @return array<int, array{key: string, label: string, value: \Closure(Listing, self): mixed}>
     */
    protected function rowDefinitions(): array
    {
        return [
            [
                'key' => 'city',
                'label' => 'City',
                'value' => fn (Listing $listing) => $listing->city ?: '—',
            ],
            [
                'key' => 'province',
                'label' => 'Province',
                'value' => fn (Listing $listing) => $listing->province ?: '—',
            ],
            [
                'key' => 'completion_status',
                'label' => 'Completion',
                'value' => fn (Listing $listing, self $service) => $service->registry->label(
                    ListingLookupGroups::COMPLETION_STATUS,
                    $listing->completion_status,
                ),
            ],
            [
                'key' => 'grade',
                'label' => 'Grade',
                'value' => fn (Listing $listing, self $service) => $service->registry->label(
                    ListingLookupGroups::GRADE,
                    $listing->spec?->grade,
                ),
            ],
            [
                'key' => 'developer',
                'label' => 'Developer',
                'value' => fn (Listing $listing) => $listing->spec?->developer ?: '—',
            ],
            [
                'key' => 'office_rental_rate',
                'label' => 'Office Rental Rate',
                'value' => fn (Listing $listing) => $this->formatDecimal($listing->office_rental_rate),
            ],
            [
                'key' => 'total_area_size',
                'label' => 'Total Area',
                'value' => fn (Listing $listing) => $this->formatDecimal($listing->total_area_size),
            ],
            [
                'key' => 'unit_market_size',
                'label' => 'Unit Market Size',
                'value' => fn (Listing $listing) => $this->formatDecimal($listing->unit_market_size),
            ],
            [
                'key' => 'retail_market_rate',
                'label' => 'Retail Market Rate',
                'value' => fn (Listing $listing) => $this->formatDecimal($listing->retail_market_rate),
            ],
            [
                'key' => 'gross_leasable_area',
                'label' => 'GLA',
                'value' => fn (Listing $listing) => $this->formatDecimal($listing->spec?->gross_leasable_area),
            ],
            [
                'key' => 'net_usable_area',
                'label' => 'Net Usable Area',
                'value' => fn (Listing $listing) => $this->formatDecimal($listing->netUsableArea()),
            ],
            [
                'key' => 'units_count',
                'label' => 'Units',
                'value' => fn (Listing $listing) => (string) $listing->units->count(),
            ],
            [
                'key' => 'vacant_units',
                'label' => 'Vacant Units',
                'value' => fn (Listing $listing) => (string) $listing->units
                    ->where('availability', 'vacant')
                    ->count(),
            ],
            [
                'key' => 'for_lease_units',
                'label' => 'For Lease',
                'value' => fn (Listing $listing) => (string) $listing->units
                    ->where('for_lease', true)
                    ->count(),
            ],
            [
                'key' => 'for_sale_units',
                'label' => 'For Sale',
                'value' => fn (Listing $listing) => (string) $listing->units
                    ->where('for_sale', true)
                    ->count(),
            ],
            [
                'key' => 'peza_accreditation',
                'label' => 'PEZA Accreditation',
                'value' => fn (Listing $listing, self $service) => $service->registry->label(
                    ListingLookupGroups::PEZA_ACCREDITATION,
                    $listing->otherInfo?->peza_accreditation,
                ),
            ],
            [
                'key' => 'operating_hours',
                'label' => 'Operating Hours',
                'value' => fn (Listing $listing) => $listing->buildingService?->operating_hours ?: '—',
            ],
            [
                'key' => 'ac_system',
                'label' => 'AC System',
                'value' => fn (Listing $listing) => $listing->buildingService?->ac_system ?: '—',
            ],
        ];
    }

    protected function formatDecimal(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return number_format((float) $value, 2, '.', '');
    }
}
