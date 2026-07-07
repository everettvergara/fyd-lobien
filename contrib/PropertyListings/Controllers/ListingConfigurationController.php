<?php

namespace App\Modules\PropertyListings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PropertyListings\Jobs\GeneratePropertyListingPagesJob;
use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Models\ListingConfiguration;
use App\Modules\PropertyListings\Services\ListingDemoSeedService;
use App\Modules\PropertyListings\Services\PropertyListingPageGenerationProgressService;
use App\Modules\PropertyListings\Services\PropertyListingPageGenerationService;
use App\Modules\PropertyListings\Services\PropertyListingPublicService;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class ListingConfigurationController extends Controller
{
    public function __construct(
        protected ListingDemoSeedService $demoSeed,
        protected PropertyListingPublicService $publicService,
        protected PropertyListingPageGenerationService $pageGeneration,
        protected PropertyListingPageGenerationProgressService $pageGenerationProgress,
    ) {}

    public function index(): View
    {
        $this->authorize('manage', ListingConfiguration::class);

        $demoCount = Listing::query()->where('code', 'like', 'DEMO-%')->count();
        $eligibleCount = $this->publicService->eligibleListings()->count();
        $cityCount = count($this->publicService->distinctCitySlugs());
        $existingPropertyPages = $this->pageGeneration->countExistingPropertyPages();

        return view('propertylistings::configuration.index', [
            'demoCount' => $demoCount,
            'gdAvailable' => function_exists('imagecreatetruecolor') && function_exists('imagejpeg'),
            'eligibleCount' => $eligibleCount,
            'cityCount' => $cityCount,
            'existingPropertyPages' => $existingPropertyPages,
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

    public function generatePages(Request $request): JsonResponse
    {
        $this->authorize('manage', ListingConfiguration::class);

        $batchId = $this->pageGenerationProgress->createBatch();
        GeneratePropertyListingPagesJob::dispatch($batchId);

        return response()->json([
            'batch_id' => $batchId,
        ]);
    }

    public function clearPages(): RedirectResponse
    {
        $this->authorize('manage', ListingConfiguration::class);

        try {
            $result = $this->pageGeneration->clearAll();
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('admin.listings.configuration.index')
                ->with('error', 'Clear public website failed: '.$e->getMessage());
        }

        ActivityLogger::log('property-listings', 'public_website_cleared', null, $result);

        return redirect()
            ->route('admin.listings.configuration.index')
            ->with('success', sprintf(
                'Public website cleared: %d page(s) and %d menu item(s) removed.',
                $result['pages_removed'],
                $result['menu_items_removed'],
            ));
    }

    public function generatePagesStatus(Request $request): JsonResponse
    {
        $this->authorize('manage', ListingConfiguration::class);

        $batchId = (string) $request->query('batch_id', '');
        $progress = $batchId !== '' ? $this->pageGenerationProgress->get($batchId) : null;

        if ($progress === null) {
            return response()->json([
                'status' => 'unknown',
                'message' => 'No generation job found.',
            ], 404);
        }

        return response()->json($progress);
    }
}
