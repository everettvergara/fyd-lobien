<?php

namespace App\Modules\PropertyListings\Models;

use App\Modules\PropertyListings\Support\ListingPathHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Listing extends Model
{
    protected $fillable = [
        'code',
        'name',
        'summary',
        'description',
        'slug',
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
        'public_page_path',
    ];

    protected function casts(): array
    {
        return [
            'office_rental_rate' => 'decimal:2',
            'total_area_size' => 'decimal:2',
            'unit_market_size' => 'decimal:2',
            'retail_market_rate' => 'decimal:2',
            'published_to_public' => 'boolean',
        ];
    }

    public function spec(): HasOne
    {
        return $this->hasOne(ListingSpec::class);
    }

    public function buildingService(): HasOne
    {
        return $this->hasOne(ListingBuildingService::class);
    }

    public function otherInfo(): HasOne
    {
        return $this->hasOne(ListingOtherInfo::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(ListingUnit::class)->orderBy('sort_order')->orderBy('id');
    }

    public function fees(): HasMany
    {
        return $this->hasMany(ListingFee::class)->orderBy('sort_order')->orderBy('id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(ListingAsset::class)->orderBy('sort_order')->orderBy('id');
    }

    public function remarks(): HasMany
    {
        return $this->hasMany(ListingRemark::class)->orderByDesc('remarked_at')->orderByDesc('id');
    }

    public function netUsableArea(): ?float
    {
        $spec = $this->spec;

        if ($spec === null || $spec->typical_retail_floor_area === null || $spec->floor_efficiency === null) {
            return null;
        }

        if (! is_numeric($spec->floor_efficiency)) {
            return null;
        }

        return round((float) $spec->typical_retail_floor_area * ((float) $spec->floor_efficiency / 100), 2);
    }

    /**
     * @return array<int, array{thumb: string, full: string, alt: string}>
     */
    public function assetImages(string $assetType): array
    {
        return $this->assets
            ->where('asset_type', $assetType)
            ->sortBy([
                ['sort_order', 'asc'],
                ['id', 'asc'],
            ])
            ->map(function (ListingAsset $asset) {
                $media = $asset->media;

                if ($media === null) {
                    return null;
                }

                $full = $media->url();
                $thumb = $media->variantUrl('thumbnail') ?? $full;

                return [
                    'thumb' => $thumb,
                    'full' => $full,
                    'alt' => $media->alt_text ?: $media->displayName(),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function buildingImageUrls(): array
    {
        return collect($this->assetImages('building'))
            ->pluck('thumb')
            ->all();
    }

    public function citySlug(): ?string
    {
        return ListingPathHelper::citySlug($this->city);
    }

    public function publicPath(): ?string
    {
        return ListingPathHelper::listingPath($this);
    }

    public function isPublicPageEligible(): bool
    {
        if (! $this->published_to_public) {
            return false;
        }

        return $this->citySlug() !== null && trim((string) ($this->slug ?? '')) !== '';
    }
}
