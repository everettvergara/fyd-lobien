<?php

namespace App\Modules\PropertyListings\Controllers\Concerns;

use App\Modules\Address\Models\Province;
use App\Modules\PropertyListings\Support\ListingLookupGroups;
use App\Modules\PropertyListings\Support\ListingLookupRegistry;
use Illuminate\Support\Collection;

trait ProvidesListingFormData
{
    /**
     * @return array<string, array<string, string>>
     */
    protected function lookupOptions(ListingLookupRegistry $registry): array
    {
        return collect(ListingLookupGroups::keys())
            ->mapWithKeys(fn (string $group) => [$group => $registry->options($group)])
            ->all();
    }

    protected function provinces(): Collection
    {
        if (! class_exists(Province::class)) {
            return collect();
        }

        return Province::query()
            ->active()
            ->with(['cities' => fn ($query) => $query->active()->orderBy('name')])
            ->orderBy('name')
            ->get();
    }
}
