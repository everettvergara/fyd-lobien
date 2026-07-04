<?php

namespace App\Modules\PropertyListings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Listing extends Model
{
    protected $fillable = [
        'code',
        'name',
        'province',
        'city',
        'brgy',
        'address',
        'office_rental_rate',
        'total_area_size',
        'unit_market_size',
        'retail_market_rate',
        'completion_status',
    ];

    protected function casts(): array
    {
        return [
            'office_rental_rate' => 'decimal:2',
            'total_area_size' => 'decimal:2',
            'unit_market_size' => 'decimal:2',
            'retail_market_rate' => 'decimal:2',
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

        return round((float) $spec->typical_retail_floor_area * ((float) $spec->floor_efficiency / 100), 2);
    }
}
