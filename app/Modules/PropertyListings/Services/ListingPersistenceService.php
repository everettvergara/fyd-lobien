<?php

namespace App\Modules\PropertyListings\Services;

use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Models\ListingAsset;
use App\Modules\PropertyListings\Models\ListingFee;
use App\Modules\PropertyListings\Models\ListingUnit;
use App\Services\Media\MediaUsageService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ListingPersistenceService
{
    public function __construct(
        protected MediaUsageService $mediaUsage,
    ) {}

    public function create(array $data): Listing
    {
        $listing = DB::transaction(function () use ($data) {
            $listing = Listing::create($this->listingPayload($data));
            $this->syncRelations($listing, $data);

            return $listing;
        });

        $this->syncMediaUsage($listing->refresh());

        return $listing;
    }

    public function update(Listing $listing, array $data): Listing
    {
        DB::transaction(function () use ($listing, $data) {
            $listing->update($this->listingPayload($data));
            $this->syncRelations($listing, $data);
        });

        $this->syncMediaUsage($listing->refresh());

        return $listing;
    }

    /**
     * @return array<string, mixed>
     */
    protected function listingPayload(array $data): array
    {
        return Arr::only($data, [
            'code',
            'name',
            'summary',
            'description',
            'province',
            'city',
            'brgy',
            'address',
            'office_rental_rate',
            'total_area_size',
            'unit_market_size',
            'retail_market_rate',
            'completion_status',
            'published_to_public',
        ]);
    }

    protected function syncRelations(Listing $listing, array $data): void
    {
        if (array_key_exists('spec', $data)) {
            $listing->spec()->updateOrCreate(
                ['listing_id' => $listing->id],
                Arr::except((array) $data['spec'], ['id', 'listing_id']),
            );
        }

        if (array_key_exists('building_service', $data)) {
            $listing->buildingService()->updateOrCreate(
                ['listing_id' => $listing->id],
                Arr::except((array) $data['building_service'], ['id', 'listing_id']),
            );
        }

        if (array_key_exists('other_info', $data)) {
            $listing->otherInfo()->updateOrCreate(
                ['listing_id' => $listing->id],
                Arr::except((array) $data['other_info'], ['id', 'listing_id']),
            );
        }

        if (array_key_exists('units', $data)) {
            $this->syncUnits($listing, (array) $data['units']);
        }

        if (array_key_exists('fees', $data)) {
            $this->syncFees($listing, (array) $data['fees']);
        }

        if (array_key_exists('assets', $data)) {
            $this->syncAssets($listing, (array) $data['assets']);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    protected function syncUnits(Listing $listing, array $rows): void
    {
        $keptIds = [];

        foreach (array_values($rows) as $index => $row) {
            $payload = Arr::except($row, ['id']);
            $payload['sort_order'] = (int) ($payload['sort_order'] ?? $index);

            if (! empty($row['id'])) {
                $unit = ListingUnit::query()
                    ->where('listing_id', $listing->id)
                    ->whereKey($row['id'])
                    ->first();

                if ($unit !== null) {
                    $unit->update($payload);
                    $keptIds[] = $unit->id;

                    continue;
                }
            }

            $unit = $listing->units()->create($payload);
            $keptIds[] = $unit->id;
        }

        $listing->units()->whereNotIn('id', $keptIds)->delete();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    protected function syncFees(Listing $listing, array $rows): void
    {
        $keptIds = [];

        foreach (array_values($rows) as $index => $row) {
            $payload = Arr::except($row, ['id']);
            $payload['sort_order'] = (int) ($payload['sort_order'] ?? $index);

            if (! empty($row['id'])) {
                $fee = ListingFee::query()
                    ->where('listing_id', $listing->id)
                    ->whereKey($row['id'])
                    ->first();

                if ($fee !== null) {
                    $fee->update($payload);
                    $keptIds[] = $fee->id;

                    continue;
                }
            }

            $fee = $listing->fees()->create($payload);
            $keptIds[] = $fee->id;
        }

        $listing->fees()->whereNotIn('id', $keptIds)->delete();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    protected function syncAssets(Listing $listing, array $rows): void
    {
        $keptIds = [];

        foreach (array_values($rows) as $index => $row) {
            $payload = Arr::only($row, ['asset_type', 'media_id']);
            $payload['sort_order'] = (int) ($row['sort_order'] ?? $index);

            if (empty($payload['asset_type']) || empty($payload['media_id'])) {
                continue;
            }

            if (! empty($row['id'])) {
                $asset = ListingAsset::query()
                    ->where('listing_id', $listing->id)
                    ->whereKey($row['id'])
                    ->first();

                if ($asset !== null) {
                    $asset->update($payload);
                    $keptIds[] = $asset->id;

                    continue;
                }
            }

            $asset = $listing->assets()->create($payload);
            $keptIds[] = $asset->id;
        }

        $listing->assets()->whereNotIn('id', $keptIds)->delete();
    }

    protected function syncMediaUsage(Listing $listing): void
    {
        $listing->loadMissing('assets');

        $this->mediaUsage->syncRelatedMedia(
            $listing,
            'property-listings',
            'asset_media',
            $listing->assets->pluck('media_id')->all(),
            'Listing Asset',
        );
    }
}
