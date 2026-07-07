<?php

namespace App\Modules\PropertyListings\Jobs;

use App\Modules\PropertyListings\Services\PropertyListingPageGenerationProgressService;
use App\Modules\PropertyListings\Services\PropertyListingPageGenerationService;
use App\Modules\PropertyListings\Services\PropertyListingPublicService;
use App\Services\ActivityLogger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class GeneratePropertyListingPagesJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $batchId,
    ) {}

    public function handle(
        PropertyListingPageGenerationService $generationService,
        PropertyListingPublicService $publicService,
        PropertyListingPageGenerationProgressService $progressService,
    ): void {
        $eligible = $publicService->eligibleListings();
        $citySlugs = $publicService->distinctCitySlugs($eligible);
        $total = count($citySlugs) + $eligible->count();

        $progressService->markRunning($this->batchId, $total);

        try {
            $stats = $generationService->syncAll(function (int $processed, int $total) use ($progressService) {
                $progressService->tick($this->batchId, $processed, $total);
            });

            $progressService->markCompleted($this->batchId, $stats, $total);

            ActivityLogger::log('property-listings', 'public_pages_generated', null, [
                'batch_id' => $this->batchId,
                ...$stats,
            ]);
        } catch (Throwable $e) {
            report($e);
            $progressService->markFailed($this->batchId, $e->getMessage());
        }
    }
}
