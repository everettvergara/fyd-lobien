<?php

namespace App\Modules\PropertyListings\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PropertyListingPageGenerationProgressService
{
    protected const CACHE_PREFIX = 'property-listings:page-gen:';

    protected const TTL_SECONDS = 3600;

    public function createBatch(): string
    {
        $batchId = (string) Str::uuid();
        $this->put($batchId, [
            'status' => 'queued',
            'total' => 0,
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'removed' => 0,
            'errors' => [],
            'message' => 'Queued…',
        ]);

        return $batchId;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function put(string $batchId, array $payload): void
    {
        Cache::put(self::CACHE_PREFIX.$batchId, $payload, self::TTL_SECONDS);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function get(string $batchId): ?array
    {
        $payload = Cache::get(self::CACHE_PREFIX.$batchId);

        return is_array($payload) ? $payload : null;
    }

    public function markRunning(string $batchId, int $total): void
    {
        $current = $this->get($batchId) ?? [];
        $this->put($batchId, [
            ...$current,
            'status' => 'running',
            'total' => $total,
            'processed' => 0,
            'message' => 'Generating public pages…',
        ]);
    }

    public function tick(string $batchId, int $processed, int $total): void
    {
        $current = $this->get($batchId) ?? [];
        $this->put($batchId, [
            ...$current,
            'status' => 'running',
            'processed' => $processed,
            'total' => $total,
            'message' => "Processing {$processed} / {$total} pages…",
        ]);
    }

    /**
     * @param  array{created: int, updated: int, removed: int, errors: array<int, string>}  $stats
     */
    public function markCompleted(string $batchId, array $stats, int $total): void
    {
        $current = $this->get($batchId) ?? [];
        $this->put($batchId, [
            ...$current,
            'status' => 'completed',
            'total' => $total,
            'processed' => $total,
            'created' => $stats['created'],
            'updated' => $stats['updated'],
            'removed' => $stats['removed'],
            'errors' => $stats['errors'],
            'message' => sprintf(
                'Completed: %d created, %d updated, %d removed.',
                $stats['created'],
                $stats['updated'],
                $stats['removed'],
            ),
        ]);
    }

    public function markFailed(string $batchId, string $message): void
    {
        $current = $this->get($batchId) ?? [];
        $this->put($batchId, [
            ...$current,
            'status' => 'failed',
            'message' => $message,
        ]);
    }
}
