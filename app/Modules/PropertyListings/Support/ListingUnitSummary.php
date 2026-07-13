<?php

namespace App\Modules\PropertyListings\Support;

use App\Modules\PropertyListings\Models\Listing;

class ListingUnitSummary
{
    public function __construct(
        protected ListingLookupRegistry $registry,
    ) {}

    /**
     * @return array<int, string>
     */
    public function propertyTypeLabels(Listing $listing): array
    {
        return $listing->units
            ->pluck('property_type')
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->unique()
            ->sort()
            ->values()
            ->map(fn (string $value) => $this->registry->label(ListingLookupGroups::PROPERTY_TYPE, $value))
            ->all();
    }

    public function propertyTypeSummary(Listing $listing): string
    {
        $labels = $this->propertyTypeLabels($listing);

        return $labels === [] ? '—' : implode(', ', $labels);
    }

    public function availabilitySummary(Listing $listing): string
    {
        $counts = $listing->units
            ->filter(fn ($unit) => $unit->availability !== null && $unit->availability !== '')
            ->groupBy('availability')
            ->map->count()
            ->sortKeys();

        if ($counts->isEmpty()) {
            return '—';
        }

        return $counts
            ->map(fn (int $count, string $value) => sprintf(
                '%s (%d)',
                $this->registry->label(ListingLookupGroups::AVAILABILITY, $value),
                $count,
            ))
            ->implode(', ');
    }

    public function hasForLease(Listing $listing): bool
    {
        return $listing->units->contains(fn ($unit) => (bool) $unit->for_lease);
    }

    public function hasForSale(Listing $listing): bool
    {
        return $listing->units->contains(fn ($unit) => (bool) $unit->for_sale);
    }
}
