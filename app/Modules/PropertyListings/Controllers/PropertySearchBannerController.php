<?php

namespace App\Modules\PropertyListings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PropertyListings\Models\PropertySearchBanner;
use App\Modules\PropertyListings\Requests\StorePropertySearchBannerRequest;
use App\Modules\PropertyListings\Requests\UpdatePropertySearchBannerRequest;
use App\Modules\PropertyListings\Services\PropertySearchBannerAdminListService;
use App\Services\ActivityLogger;
use App\Services\Media\MediaUsageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropertySearchBannerController extends Controller
{
    public function __construct(
        protected PropertySearchBannerAdminListService $list,
        protected MediaUsageService $usage,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', PropertySearchBanner::class);

        return view('propertylistings::property-search-banners.index', [
            'list' => $this->list->result($request),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', PropertySearchBanner::class);

        return view('propertylistings::property-search-banners.create');
    }

    public function store(StorePropertySearchBannerRequest $request): RedirectResponse
    {
        $banner = PropertySearchBanner::create($request->validated());
        $this->syncMediaUsage($banner);

        ActivityLogger::log('property_search_banners', 'created', $banner);

        return redirect()
            ->route('admin.property-search-banners.index')
            ->with('success', 'Search banner created.');
    }

    public function edit(PropertySearchBanner $propertySearchBanner): View
    {
        $this->authorize('update', $propertySearchBanner);

        $propertySearchBanner->load('backgroundImage');

        return view('propertylistings::property-search-banners.edit', [
            'banner' => $propertySearchBanner,
        ]);
    }

    public function update(UpdatePropertySearchBannerRequest $request, PropertySearchBanner $propertySearchBanner): RedirectResponse
    {
        $propertySearchBanner->update($request->validated());
        $this->syncMediaUsage($propertySearchBanner->refresh());

        ActivityLogger::log('property_search_banners', 'updated', $propertySearchBanner);

        return redirect()
            ->route('admin.property-search-banners.index')
            ->with('success', 'Search banner updated.');
    }

    public function destroy(PropertySearchBanner $propertySearchBanner): RedirectResponse
    {
        $this->authorize('delete', $propertySearchBanner);

        ActivityLogger::log('property_search_banners', 'deleted', $propertySearchBanner);
        $this->usage->removeModel($propertySearchBanner);
        $propertySearchBanner->delete();

        return redirect()
            ->route('admin.property-search-banners.index')
            ->with('success', 'Search banner deleted.');
    }

    protected function syncMediaUsage(PropertySearchBanner $banner): void
    {
        $this->usage->syncModel($banner, 'property-listings', [
            'background_image_id' => 'Property Search Banner Background',
        ]);
    }
}
