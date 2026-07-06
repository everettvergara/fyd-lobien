<?php

namespace App\Modules\PropertyListings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Services\ListingBrochureService;
use App\Modules\PropertyListings\Support\ListingBrochureTypes;
use App\Services\NavigationService;
use Illuminate\View\View;

class ListingBrochureController extends Controller
{
    public function __construct(
        protected ListingBrochureService $brochures,
        protected NavigationService $navigation,
    ) {}

    public function index(Listing $listing): View
    {
        $this->authorize('view', $listing);

        $listing = $this->brochures->loadListing($listing);

        return view('propertylistings::brochures.index', [
            'listing' => $listing,
            'types' => ListingBrochureTypes::definitions(),
            'siteInfo' => $this->navigation->siteInfo(),
        ]);
    }

    public function show(Listing $listing, string $type): View
    {
        $this->authorize('view', $listing);

        if (! ListingBrochureTypes::isValid($type)) {
            abort(404);
        }

        $listing = $this->brochures->loadListing($listing);
        $lookups = $this->brochures->lookupOptions();

        $view = match ($type) {
            ListingBrochureTypes::INTERIOR => 'propertylistings::brochures.interior',
            ListingBrochureTypes::PROPERTY_PHOTOS => 'propertylistings::brochures.property-photos',
            ListingBrochureTypes::FLOOR_PLAN => 'propertylistings::brochures.floor-plan',
            ListingBrochureTypes::FLOORS_UNITS => 'propertylistings::brochures.floors-units',
            ListingBrochureTypes::PROPERTY_INFORMATION => 'propertylistings::brochures.property-information',
            ListingBrochureTypes::ALL => 'propertylistings::brochures.all',
            default => abort(404),
        };

        $data = [
            'listing' => $listing,
            'lookups' => $lookups,
            'brochureType' => $type,
            'brochureLabel' => ListingBrochureTypes::label($type),
            'siteInfo' => $this->navigation->siteInfo(),
        ];

        if (in_array($type, [ListingBrochureTypes::INTERIOR, ListingBrochureTypes::PROPERTY_PHOTOS, ListingBrochureTypes::FLOOR_PLAN], true)) {
            $data['pages'] = $this->brochures->imagePages($listing, $type);
        }

        if ($type === ListingBrochureTypes::PROPERTY_INFORMATION) {
            $data['propertyInfoRows'] = $this->brochures->propertyInfoRows($listing);
            $data['buildingImages'] = $listing->assetImages('building');
            $data['mapImages'] = $listing->assetImages('map');
        }

        if ($type === ListingBrochureTypes::ALL) {
            $data['sections'] = collect(ListingBrochureTypes::contentTypes())
                ->map(fn (string $sectionType) => [
                    'type' => $sectionType,
                    'label' => ListingBrochureTypes::label($sectionType),
                    'pages' => in_array($sectionType, [
                        ListingBrochureTypes::INTERIOR,
                        ListingBrochureTypes::PROPERTY_PHOTOS,
                        ListingBrochureTypes::FLOOR_PLAN,
                    ], true)
                        ? $this->brochures->imagePages($listing, $sectionType)
                        : null,
                    'propertyInfoRows' => $sectionType === ListingBrochureTypes::PROPERTY_INFORMATION
                        ? $this->brochures->propertyInfoRows($listing)
                        : null,
                    'buildingImages' => $sectionType === ListingBrochureTypes::PROPERTY_INFORMATION
                        ? $listing->assetImages('building')
                        : null,
                    'mapImages' => $sectionType === ListingBrochureTypes::PROPERTY_INFORMATION
                        ? $listing->assetImages('map')
                        : null,
                ])
                ->all();
        }

        return view($view, $data);
    }
}
