<?php

namespace App\Modules\PropertyListings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Models\ListingConfiguration;
use App\Modules\PropertyListings\Services\ListingDemoSeedService;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class ListingConfigurationController extends Controller
{
    public function __construct(
        protected ListingDemoSeedService $demoSeed,
    ) {}

    public function index(): View
    {
        $this->authorize('manage', ListingConfiguration::class);

        $demoCount = Listing::query()->where('code', 'like', 'DEMO-%')->count();

        return view('propertylistings::configuration.index', [
            'demoCount' => $demoCount,
            'gdAvailable' => function_exists('imagecreatetruecolor') && function_exists('imagejpeg'),
        ]);
    }

    public function seedSamples(): RedirectResponse
    {
        $this->authorize('manage', ListingConfiguration::class);

        try {
            $result = $this->demoSeed->seed();
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('admin.listings.configuration.index')
                ->with('error', 'Sample seed failed: '.$e->getMessage());
        }

        ActivityLogger::log('property-listings', 'sample_listings_seeded', null, $result);

        $message = sprintf(
            'Sample listings seeded: %d listing(s) processed.',
            $result['seeded'] ?? 0,
        );

        if (! empty($result['image_fallback'])) {
            $message .= ' Demo asset images used a fallback because PHP GD is not enabled.';
        }

        return redirect()
            ->route('admin.listings.configuration.index')
            ->with('success', $message);
    }
}
