<?php

namespace App\Modules\PropertyListings\Services;

use App\Modules\Address\Models\City;
use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Models\ListingAsset;
use App\Modules\PropertyListings\Models\ListingFee;
use App\Modules\PropertyListings\Models\ListingLookup;
use App\Modules\PropertyListings\Models\ListingRemark;
use App\Modules\PropertyListings\Models\ListingUnit;
use App\Modules\PropertyListings\Support\ListingLookupGroups;
use App\Modules\PropertyListings\Support\ListingLookupRegistry;
use App\Modules\PropertyListings\Support\ListingPathHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PropertyListingPublicService
{
    /** @var Collection<int, Listing>|null */
    protected ?Collection $eligibleListingsCache = null;

    /** @var Collection<string, City>|null */
    protected ?Collection $citiesBySlugCache = null;

    public function __construct(
        protected ListingLookupRegistry $lookups,
    ) {}

    /**
     * @return array<int, string>
     */
    public function detailRelations(): array
    {
        return [
            'spec',
            'buildingService',
            'otherInfo',
            'units',
            'fees',
            'assets.media',
            'remarks.user',
        ];
    }

    /**
     * Relations needed to build listing card DTOs.
     *
     * @return array<int, string>
     */
    public function listRelations(): array
    {
        return ['spec', 'units', 'assets.media'];
    }

    public function publishedQuery(): Builder
    {
        return Listing::query()
            ->where('published_to_public', true)
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->whereNotNull('slug')
            ->where('slug', '!=', '');
    }

    public function findPublishedByCityAndSlug(string $citySlug, string $listingSlug): ?Listing
    {
        return $this->publishedQuery()
            ->with($this->detailRelations())
            ->where('slug', $listingSlug)
            ->get()
            ->first(fn (Listing $listing) => $listing->citySlug() === $citySlug);
    }

    /**
     * @return Collection<int, Listing>
     */
    public function randomPublishedForCity(string $citySlug, int $limit = 5, ?int $excludeListingId = null): Collection
    {
        return $this->publishedQuery()
            ->with($this->listRelations())
            ->when($excludeListingId !== null, fn (Builder $query) => $query->where('id', '!=', $excludeListingId))
            ->get()
            ->filter(fn (Listing $listing) => $listing->citySlug() === $citySlug)
            ->shuffle()
            ->take($limit)
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    public function toListItemDto(Listing $listing): array
    {
        return [
            'id' => $listing->id,
            'code' => $listing->code,
            'name' => $listing->name,
            'slug' => $listing->slug,
            'summary' => $listing->summary,
            'city' => $listing->city,
            'city_slug' => $listing->citySlug(),
            'province' => $listing->province,
            'address' => $listing->address,
            'developer' => $listing->spec?->developer,
            'completion_status' => $listing->completion_status,
            'office_rental_rate' => $listing->office_rental_rate,
            'total_area_size' => $listing->total_area_size,
            'for_lease' => $listing->units->contains(fn (ListingUnit $unit) => (bool) $unit->for_lease),
            'for_sale' => $listing->units->contains(fn (ListingUnit $unit) => (bool) $unit->for_sale),
            'building_image_urls' => $listing->buildingImageUrls(),
            'url' => $listing->publicPath() ? url($listing->publicPath()) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDetailDto(Listing $listing): array
    {
        $listing->loadMissing($this->detailRelations());

        return [
            ...$this->toListItemDto($listing),
            'description' => $listing->description,
            'brgy' => $listing->brgy,
            'address' => $listing->address,
            'unit_market_size' => $listing->unit_market_size,
            'retail_market_rate' => $listing->retail_market_rate,
            'published_to_public' => (bool) $listing->published_to_public,
            'net_usable_area' => $listing->netUsableArea(),
            'spec' => $this->specDto($listing),
            'building_service' => $this->buildingServiceDto($listing),
            'other_info' => $this->otherInfoDto($listing),
            'units' => $listing->units->map(fn (ListingUnit $unit) => $this->unitDto($unit))->values()->all(),
            'fees' => $listing->fees->map(fn (ListingFee $fee) => $this->feeDto($fee))->values()->all(),
            'assets' => $this->assetsDto($listing),
            'remarks' => $listing->remarks->map(fn (ListingRemark $remark) => $this->remarkDto($remark))->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function specDto(Listing $listing): ?array
    {
        $spec = $listing->spec;
        if ($spec === null) {
            return null;
        }

        return [
            'developer' => $spec->developer,
            'grade' => $spec->grade,
            'completion_year' => $spec->completion_year,
            'completion_qtr' => $spec->completion_qtr,
            'no_of_floors' => $spec->no_of_floors,
            'no_of_basement' => $spec->no_of_basement,
            'density_ratio' => $spec->density_ratio,
            'parking_allocation' => $spec->parking_allocation,
            'floor_to_ceiling_height' => $spec->floor_to_ceiling_height,
            'gross_leasable_area' => $spec->gross_leasable_area,
            'typical_floor_area' => $spec->typical_floor_area,
            'typical_retail_floor_area' => $spec->typical_retail_floor_area,
            'floor_efficiency' => $spec->floor_efficiency,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function buildingServiceDto(Listing $listing): ?array
    {
        $building = $listing->buildingService;
        if ($building === null) {
            return null;
        }

        return [
            'operating_hours' => $building->operating_hours,
            'ac_system' => $building->ac_system,
            'no_of_lifts_passenger' => $building->no_of_lifts_passenger,
            'no_of_lifts_service' => $building->no_of_lifts_service,
            'telco' => $building->telco,
            'backup_power' => $building->backup_power,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function otherInfoDto(Listing $listing): ?array
    {
        $other = $listing->otherInfo;
        if ($other === null) {
            return null;
        }

        return [
            'peza_accreditation' => $other->peza_accreditation,
            'sustainability' => $other->sustainability,
            'other_info_visible' => (bool) $other->other_info_visible,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function unitDto(ListingUnit $unit): array
    {
        return [
            'id' => $unit->id,
            'floor' => $unit->floor,
            'unit' => $unit->unit,
            'area_size' => $unit->area_size,
            'rental' => $unit->rental,
            'handover_condition' => $unit->handover_condition,
            'availability' => $unit->availability,
            'bedrooms' => $unit->bedrooms,
            'selling_price' => $unit->selling_price,
            'property_type' => $unit->property_type,
            'for_lease' => (bool) $unit->for_lease,
            'for_sale' => (bool) $unit->for_sale,
            'last_remarks' => $unit->last_remarks,
            'sort_order' => $unit->sort_order,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function feeDto(ListingFee $fee): array
    {
        return [
            'id' => $fee->id,
            'fee_type' => $fee->fee_type,
            'fee' => $fee->fee,
            'sort_order' => $fee->sort_order,
        ];
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    protected function assetsDto(Listing $listing): array
    {
        $grouped = [];

        foreach ($listing->assets as $asset) {
            $type = (string) $asset->asset_type;
            $grouped[$type] ??= [];
            $dto = $this->assetDto($asset);
            if ($dto !== null) {
                $grouped[$type][] = $dto;
            }
        }

        return $grouped;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function assetDto(ListingAsset $asset): ?array
    {
        $media = $asset->media;
        if ($media === null) {
            return null;
        }

        $full = $media->url();
        $thumb = $media->variantUrl('thumbnail') ?? $full;

        return [
            'id' => $asset->id,
            'asset_type' => $asset->asset_type,
            'sort_order' => $asset->sort_order,
            'thumb' => $thumb,
            'full' => $full,
            'alt' => $media->alt_text ?: $media->displayName(),
            'mime_type' => $media->mime_type,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function remarkDto(ListingRemark $remark): array
    {
        return [
            'id' => $remark->id,
            'comment' => $remark->comment,
            'remarked_at' => $remark->remarked_at?->toIso8601String(),
            'user_name' => $remark->user?->name,
        ];
    }

    public function cityLabelForSlug(string $citySlug, ?Collection $eligibleListings = null): string
    {
        $listings = $eligibleListings ?? $this->eligibleListings();

        $match = $listings->first(fn (Listing $listing) => $listing->citySlug() === $citySlug);

        return $match?->city ?? ucwords(str_replace('-', ' ', $citySlug));
    }

    public function eligibleListings(): Collection
    {
        return $this->eligibleListingsCache ??= $this->publishedQuery()->get();
    }

    /**
     * @return Collection<string, int>
     */
    protected function listingCountsByCitySlug(Collection $eligible): Collection
    {
        return $eligible
            ->map(fn (Listing $listing) => $listing->citySlug())
            ->filter()
            ->countBy();
    }

    /**
     * @return Collection<string, City>
     */
    protected function citiesByProvinceAndSlug(): Collection
    {
        if ($this->citiesBySlugCache !== null) {
            return $this->citiesBySlugCache;
        }

        if (! class_exists(City::class)) {
            return $this->citiesBySlugCache = collect();
        }

        return $this->citiesBySlugCache = City::query()
            ->with(['image', 'province'])
            ->get()
            ->keyBy(fn (City $city) => $this->cityCacheKey(
                $city->province?->name,
                Str::slug($city->name),
            ));
    }

    protected function cityCacheKey(?string $provinceName, string $citySlug): string
    {
        $provinceSlug = $provinceName !== null && trim($provinceName) !== ''
            ? Str::slug($provinceName)
            : '_';

        return $provinceSlug.'/'.$citySlug;
    }

    /**
     * @return array<int, string>
     */
    public function distinctCitySlugs(?Collection $eligibleListings = null): array
    {
        $listings = $eligibleListings ?? $this->eligibleListings();

        return $listings
            ->map(fn (Listing $listing) => $listing->citySlug())
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Normalize public search filter input. Every filter defaults to "all".
     *
     * @param  array<string, mixed>  $input
     * @return array{city: string, property_type: string, intent: string, name: string}
     */
    public function normalizeSearchFilters(array $input): array
    {
        $normalize = function (mixed $value): string {
            $value = is_string($value) ? trim($value) : '';

            return $value === '' ? 'all' : $value;
        };

        return [
            'city' => $normalize($input['city'] ?? null),
            'property_type' => $normalize($input['property_type'] ?? null),
            'intent' => in_array($input['intent'] ?? null, ['lease', 'sale'], true) ? $input['intent'] : 'all',
            'name' => is_string($input['name'] ?? null) ? trim($input['name']) : '',
        ];
    }

    /**
     * Published listings matching the given public search filters.
     *
     * @param  array{city?: string, property_type?: string, intent?: string, name?: string}  $filters
     * @return Collection<int, Listing>
     */
    public function searchPublished(array $filters): Collection
    {
        $filters = $this->normalizeSearchFilters($filters);

        return $this->publishedQuery()
            ->with($this->listRelations())
            ->when($filters['name'] !== '', fn (Builder $query) => $query->where('name', 'like', '%'.$filters['name'].'%'))
            ->orderBy('name')
            ->get()
            ->filter(function (Listing $listing) use ($filters) {
                if ($filters['city'] !== 'all' && $listing->citySlug() !== $filters['city']) {
                    return false;
                }

                if ($filters['property_type'] !== 'all'
                    && ! $listing->units->contains(fn (ListingUnit $unit) => $unit->property_type === $filters['property_type'])) {
                    return false;
                }

                if ($filters['intent'] === 'lease'
                    && ! $listing->units->contains(fn (ListingUnit $unit) => (bool) $unit->for_lease)) {
                    return false;
                }

                if ($filters['intent'] === 'sale'
                    && ! $listing->units->contains(fn (ListingUnit $unit) => (bool) $unit->for_sale)) {
                    return false;
                }

                return true;
            })
            ->values();
    }

    /**
     * Options for the public property type filter dropdown.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function propertyTypeOptions(): array
    {
        return collect($this->lookups->options(ListingLookupGroups::PROPERTY_TYPE))
            ->map(fn (string $label, string $value) => ['value' => $value, 'label' => $label])
            ->values()
            ->all();
    }

    /**
     * Property type cards for the public property types block.
     *
     * @return array<int, array{value: string, label: string, summary: ?string, image_url: ?string, image_alt: string, search_url: string}>
     */
    public function propertyTypeCards(?int $limit = null): array
    {
        $query = ListingLookup::query()
            ->where('group', ListingLookupGroups::PROPERTY_TYPE)
            ->active()
            ->with('image')
            ->orderBy('sort_order')
            ->orderBy('label');

        if ($limit !== null) {
            $query->limit(max(1, $limit));
        }

        return $query->get()
            ->map(fn (ListingLookup $lookup) => [
                'value' => (string) $lookup->value,
                'label' => (string) $lookup->label,
                'summary' => $lookup->summary,
                'image_url' => $lookup->image?->url(),
                'image_alt' => $lookup->image?->alt_text ?? $lookup->label,
                'search_url' => url(ListingPathHelper::searchPath().'?property_type='.$lookup->value),
            ])
            ->values()
            ->all();
    }

    /**
     * Options for the public city filter dropdown (cities with published listings).
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function cityOptions(?Collection $eligibleListings = null): array
    {
        $eligible = $eligibleListings ?? $this->eligibleListings();

        return collect($this->distinctCitySlugs($eligible))
            ->map(fn (string $slug) => [
                'value' => $slug,
                'label' => $this->cityLabelForSlug($slug, $eligible),
            ])
            ->sortBy('label')
            ->values()
            ->all();
    }

    /**
     * Address module City record matching a listing city slug, if any.
     */
    public function cityModelForSlug(string $citySlug, ?string $provinceName = null): ?City
    {
        if (! class_exists(City::class)) {
            return null;
        }

        if ($provinceName === null || trim($provinceName) === '') {
            return null;
        }

        $city = $this->citiesByProvinceAndSlug()->get($this->cityCacheKey($provinceName, $citySlug));

        return $city instanceof City ? $city : null;
    }

    /**
     * Card DTO for a city with published listings, enriched with Address module data.
     *
     * @return array<string, mixed>
     */
    public function cityDto(string $citySlug, ?Collection $eligibleListings = null, ?Collection $listingCountsBySlug = null): array
    {
        $eligible = $eligibleListings ?? $this->eligibleListings();
        $cityListings = $eligible->filter(fn (Listing $listing) => $listing->citySlug() === $citySlug);
        $provinces = $cityListings->pluck('province')->filter()->unique()->values();
        $provinceForLookup = $provinces->count() === 1 ? $provinces->first() : null;
        $city = $this->cityModelForSlug($citySlug, $provinceForLookup);
        $counts = $listingCountsBySlug ?? $this->listingCountsByCitySlug($eligible);
        $count = (int) $counts->get($citySlug, 0);

        return [
            'slug' => $citySlug,
            'label' => $this->cityLabelForSlug($citySlug, $eligible),
            'summary' => $city?->summary,
            'description' => $city?->description,
            'image_url' => $city?->image?->url(),
            'image_alt' => $city?->image?->alt_text ?: ($city?->name ?? $citySlug),
            'listing_count' => $count,
            'url' => url(ListingPathHelper::cityPath($citySlug)),
        ];
    }

    /**
     * City card DTOs for every city that has published listings, sorted by label.
     *
     * @return array<int, array<string, mixed>>
     */
    public function citiesWithListings(?Collection $eligibleListings = null): array
    {
        $eligible = $eligibleListings ?? $this->eligibleListings();
        $counts = $this->listingCountsByCitySlug($eligible);

        return collect($this->distinctCitySlugs($eligible))
            ->map(fn (string $slug) => $this->cityDto($slug, $eligible, $counts))
            ->sortBy('label')
            ->values()
            ->all();
    }

    /**
     * Paginated city card DTOs — builds full DTOs only for the current page.
     *
     * @return array{items: array<int, array<string, mixed>>, pagination: array{current_page: int, last_page: int, per_page: int, total: int}}
     */
    public function paginatedCitiesWithListings(int $page, int $perPage, ?Collection $eligibleListings = null): array
    {
        $eligible = $eligibleListings ?? $this->eligibleListings();
        $counts = $this->listingCountsByCitySlug($eligible);

        $slugRows = collect($this->distinctCitySlugs($eligible))
            ->map(fn (string $slug) => [
                'slug' => $slug,
                'label' => $this->cityLabelForSlug($slug, $eligible),
            ])
            ->sortBy('label')
            ->values();

        $paginated = $this->paginateCollection($slugRows, $page, $perPage);

        return [
            'items' => collect($paginated['items'])
                ->map(fn (array $row) => $this->cityDto($row['slug'], $eligible, $counts))
                ->all(),
            'pagination' => $paginated['pagination'],
        ];
    }

    /**
     * Slice a collection into a page and return items plus pagination metadata.
     *
     * @param  Collection<int, mixed>  $items
     * @return array{items: array<int, mixed>, pagination: array{current_page: int, last_page: int, per_page: int, total: int}}
     */
    public function paginateCollection(Collection $items, int $page, int $perPage): array
    {
        $perPage = max(1, $perPage);
        $total = $items->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $lastPage);

        return [
            'items' => $items->forPage($page, $perPage)->values()->all(),
            'pagination' => [
                'current_page' => $page,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $total,
            ],
        ];
    }

    public function isListingDetailPath(string $pagePath): bool
    {
        $parsed = ListingPathHelper::parsePagePath($pagePath);

        return is_array($parsed) && isset($parsed['listing_slug']);
    }

    public function isCityPath(string $pagePath): bool
    {
        $parsed = ListingPathHelper::parsePagePath($pagePath);

        return is_array($parsed) && ! isset($parsed['listing_slug']);
    }
}
