<?php

namespace App\Modules\PropertyListings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PropertyListings\Controllers\Concerns\ProvidesListingFormData;
use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Requests\StoreListingRequest;
use App\Modules\PropertyListings\Requests\UpdateListingRequest;
use App\Modules\PropertyListings\Services\ListingAdminListService;
use App\Modules\PropertyListings\Services\ListingPersistenceService;
use App\Modules\PropertyListings\Support\ListingLookupRegistry;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListingController extends Controller
{
    use ProvidesListingFormData;

    public function __construct(
        protected ListingAdminListService $list,
        protected ListingPersistenceService $persistence,
        protected ListingLookupRegistry $lookups,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Listing::class);

        $viewMode = in_array($request->query('view'), ['table', 'thumbnails'], true)
            ? $request->query('view')
            : 'table';

        return view('propertylistings::listings.index', [
            'list' => $this->list->result($request),
            'viewMode' => $viewMode,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Listing::class);

        return view('propertylistings::listings.create', [
            'lookups' => $this->lookupOptions($this->lookups),
            'provinces' => $this->provinces(),
        ]);
    }

    public function store(StoreListingRequest $request): RedirectResponse
    {
        $listing = $this->persistence->create($request->validated());

        ActivityLogger::log('listings', 'created', $listing);

        return redirect()->route('admin.listings.edit', $listing)->with('success', 'Listing created.');
    }

    public function edit(Listing $listing): View
    {
        $this->authorize('update', $listing);

        $listing->load([
            'spec',
            'buildingService',
            'otherInfo',
            'units',
            'fees',
            'assets.media',
            'remarks.user',
            'remarks.unit',
        ]);

        return view('propertylistings::listings.edit', [
            'listing' => $listing,
            'lookups' => $this->lookupOptions($this->lookups),
            'provinces' => $this->provinces(),
        ]);
    }

    public function update(UpdateListingRequest $request, Listing $listing): RedirectResponse
    {
        $listing = $this->persistence->update($listing, $request->validated());

        ActivityLogger::log('listings', 'updated', $listing);

        return redirect()->route('admin.listings.edit', $listing)->with('success', 'Listing updated.');
    }

    public function destroy(Listing $listing): RedirectResponse
    {
        $this->authorize('delete', $listing);

        ActivityLogger::log('listings', 'deleted', $listing);
        $listing->delete();

        return redirect()->route('admin.listings.index')->with('success', 'Listing deleted.');
    }
}
