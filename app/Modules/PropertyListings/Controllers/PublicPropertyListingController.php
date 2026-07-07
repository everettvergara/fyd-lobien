<?php

namespace App\Modules\PropertyListings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PropertyListings\Services\PropertyListingPublicService;
use Illuminate\Http\JsonResponse;

class PublicPropertyListingController extends Controller
{
    public function __construct(
        protected PropertyListingPublicService $publicService,
    ) {}

    public function cityListings(string $citySlug): JsonResponse
    {
        $listings = $this->publicService
            ->randomPublishedForCity($citySlug, 5)
            ->map(fn ($listing) => $this->publicService->toListItemDto($listing))
            ->values()
            ->all();

        return response()->json([
            'city_slug' => $citySlug,
            'city_label' => $this->publicService->cityLabelForSlug($citySlug),
            'listings' => $listings,
        ]);
    }

    public function show(string $citySlug, string $slug): JsonResponse
    {
        $listing = $this->publicService->findPublishedByCityAndSlug($citySlug, $slug);

        if ($listing === null) {
            return response()->json(['message' => 'Listing not found.'], 404);
        }

        return response()->json([
            'listing' => $this->publicService->toDetailDto($listing),
        ]);
    }
}
