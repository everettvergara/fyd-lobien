<?php

namespace App\Modules\PropertyListings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Support\ListingLookupGroups;
use App\Modules\PropertyListings\Support\ListingLookupRegistry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListingCompareController extends Controller
{
    public function __construct(
        protected ListingLookupRegistry $lookups,
    ) {}

    public function compare(Request $request): View
    {
        $this->authorize('viewAny', Listing::class);

        $ids = $this->parseIds($request);

        $listings = Listing::query()
            ->whereIn('id', $ids)
            ->with(['spec', 'buildingService', 'otherInfo', 'units', 'fees', 'assets'])
            ->withCount(['units', 'fees', 'assets'])
            ->get()
            ->sortBy(fn (Listing $listing) => array_search($listing->id, $ids, true))
            ->values();

        $lookupOptions = collect(ListingLookupGroups::keys())
            ->mapWithKeys(fn (string $group) => [$group => $this->lookups->options($group)])
            ->all();

        return view('propertylistings::listings.compare', [
            'listings' => $listings,
            'lookups' => $lookupOptions,
        ]);
    }

    /**
     * @return array<int, int>
     */
    protected function parseIds(Request $request): array
    {
        $raw = $request->query('ids');

        if (is_string($raw)) {
            $parts = array_filter(array_map('trim', explode(',', $raw)));
        } elseif (is_array($raw)) {
            $parts = $raw;
        } else {
            return [];
        }

        return collect($parts)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->take(5)
            ->values()
            ->all();
    }
}
