<?php

namespace App\Modules\PropertyListings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PropertyListings\Models\ListingLookup;
use App\Modules\PropertyListings\Requests\StoreListingLookupRequest;
use App\Modules\PropertyListings\Requests\UpdateListingLookupRequest;
use App\Modules\PropertyListings\Services\ListingLookupAdminService;
use App\Modules\PropertyListings\Services\ListingLookupPersistenceService;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ListingLookupController extends Controller
{
    public function __construct(
        protected ListingLookupAdminService $admin,
        protected ListingLookupPersistenceService $persistence,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', ListingLookup::class);

        $groups = $this->admin->hubStats();

        return view('propertylistings::listing-lookups.index', [
            'groups' => $groups,
            'groupCounts' => collect($groups)->pluck('active_count', 'group')->all(),
            'groupSlugs' => collect($groups)->pluck('slug', 'group')->all(),
        ]);
    }

    public function groupIndex(string $group): View
    {
        $this->authorize('viewAny', ListingLookup::class);

        $groupKey = $this->resolveGroupOrFail($group);

        $lookups = ListingLookup::query()
            ->where('group', $groupKey)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();

        return view('propertylistings::listing-lookups.group', [
            'group' => $groupKey,
            'groupLabel' => $this->admin->groupLabel($groupKey),
            'groupSlug' => $this->admin->groupSlug($groupKey),
            'lookups' => $lookups,
        ]);
    }

    public function create(string $group): View
    {
        $this->authorize('create', ListingLookup::class);

        $groupKey = $this->resolveGroupOrFail($group);

        return view('propertylistings::listing-lookups.create', [
            'group' => $groupKey,
            'groupLabel' => $this->admin->groupLabel($groupKey),
            'groupSlug' => $this->admin->groupSlug($groupKey),
        ]);
    }

    public function store(StoreListingLookupRequest $request, string $group): RedirectResponse
    {
        $groupKey = $this->resolveGroupOrFail($group);

        $lookup = $this->persistence->create($groupKey, $request->validated());

        ActivityLogger::log('listing_lookups', 'created', $lookup);

        return redirect()
            ->route('admin.listing-lookups.group', $this->admin->groupSlug($groupKey))
            ->with('success', 'Dropdown value created.');
    }

    public function edit(string $group, ListingLookup $listingLookup): View
    {
        $this->authorize('update', $listingLookup);

        $groupKey = $this->resolveGroupOrFail($group);

        abort_unless($listingLookup->group === $groupKey, 404);

        return view('propertylistings::listing-lookups.edit', [
            'lookup' => $listingLookup,
            'group' => $groupKey,
            'groupLabel' => $this->admin->groupLabel($groupKey),
            'groupSlug' => $this->admin->groupSlug($groupKey),
            'usageCount' => $this->persistence->usageCount($listingLookup),
        ]);
    }

    public function update(UpdateListingLookupRequest $request, string $group, ListingLookup $listingLookup): RedirectResponse
    {
        $groupKey = $this->resolveGroupOrFail($group);

        abort_unless($listingLookup->group === $groupKey, 404);

        $lookup = $this->persistence->update($listingLookup, $request->validated());

        ActivityLogger::log('listing_lookups', 'updated', $lookup);

        return redirect()
            ->route('admin.listing-lookups.group', $this->admin->groupSlug($groupKey))
            ->with('success', 'Dropdown value updated.');
    }

    public function destroy(string $group, ListingLookup $listingLookup): RedirectResponse
    {
        $this->authorize('delete', $listingLookup);

        $groupKey = $this->resolveGroupOrFail($group);

        abort_unless($listingLookup->group === $groupKey, 404);

        try {
            $this->persistence->delete($listingLookup);
        } catch (ValidationException $exception) {
            return redirect()
                ->route('admin.listing-lookups.group', $this->admin->groupSlug($groupKey))
                ->withErrors($exception->errors());
        }

        ActivityLogger::log('listing_lookups', 'deleted', $listingLookup);

        return redirect()
            ->route('admin.listing-lookups.group', $this->admin->groupSlug($groupKey))
            ->with('success', 'Dropdown value deleted.');
    }

    protected function resolveGroupOrFail(string $group): string
    {
        $groupKey = $this->admin->groupFromSlug($group);

        if ($groupKey === null) {
            throw new NotFoundHttpException('Unknown lookup group.');
        }

        return $groupKey;
    }
}
